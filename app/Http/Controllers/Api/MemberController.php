<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Validator;
use App\Models\Member;
use App\Traits\ApiPaginationTrait;
use Pest\Support\Str;

class MemberController extends BaseApiController
{
    use ApiPaginationTrait;

    public function index(Request $request): JsonResponse
    {
        $query = Member::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('member_code', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return $this->paginatedResponse($query, $request, 'Members retrieved successfully');
    }

    private function generateMemberCode(int $length = 6): string
    {
        $random = strtoupper(Str::random($length));
        return 'MBR-' . now()->format('Ymd') . '-' . $random;
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'join_date' => 'required|date',
            'exit_date' => 'nullable|date|after_or_equal:join_date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $member = Member::create([
            'member_code' => $this->generateMemberCode(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => $request->status,
            'join_date' => $request->join_date,
            'exit_date' => $request->exit_date,
            'notes' => $request->notes,
        ]);

        return $this->successResponse($member, 'Member created successfully', 201);
    }

    public function show(Member $member): JsonResponse
    {
        $member->load(['payments' => function ($query) {
            $query->latest('payment_date')->limit(12);
        }]);

        return $this->successResponse($member);
    }

    public function update(Request $request, Member $member): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:members,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
            'join_date' => 'required|date',
            'exit_date' => 'nullable|date|after_or_equal:join_date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $member->update($request->all());

        return $this->successResponse($member, 'Member updated successfully');
    }

    public function destroy(Member $member): JsonResponse
    {
        $member->delete();

        return $this->successResponse(null, 'Member deleted successfully');
    }

    public function active(Request $request): JsonResponse
    {
        $query = Member::active();
        return $this->paginatedResponse($query, $request, 'Active members retrieved successfully');
    }

    public function paymentHistory(Member $member, Request $request): JsonResponse
    {
        $query = $member->payments()
            ->with('recorder')
            ->latest('payment_date');

        return $this->paginatedResponse($query, $request, 'Payment history retrieved successfully');
    }
}
