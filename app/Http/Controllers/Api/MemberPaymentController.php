<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{DB, Validator};
use App\Models\{CashTransaction, Member, MemberPayment};
use App\Traits\ApiPaginationTrait;
use Pest\Support\Str;

class MemberPaymentController extends BaseApiController
{
    use ApiPaginationTrait;

    public function index(Request $request): JsonResponse
    {
        $query = MemberPayment::with(['member', 'recorder']);

        // Filter by member
        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        // Filter by period
        if ($request->has('period')) {
            $query->where('period', $request->period);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        $query->latest('payment_date');

        return $this->paginatedResponse($query, $request, 'Member payments retrieved successfully');
    }

    private function generateReferenceNumber(int $length = 6): string
    {
        $randomString = strtoupper(Str::random($length));
        return 'PAY-' . now()->format('Ymd') . '-' . $randomString;
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,uuid',
            'period' => 'required|string', // Format: YYYY-MM
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:paid,unpaid,partial,late',
            'payment_method' => 'required|in:cash,transfer,other',
            'notes' => 'nullable|string',
            'cash_account_id' => 'required_if:status,paid|exists:cash_accounts,uuid',
            'category_id' => 'required_if:status,paid|exists:categories,uuid',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            DB::beginTransaction();

            // Resolve UUIDs to internal IDs
            $member = Member::where('uuid', $request->member_id)->first();
            if (!$member) {
                return $this->resourceNotFound('member', $request->member_id, 'member_id');
            }

            $cashAccount = null;
            if ($request->has('cash_account_id') && $request->cash_account_id) {
                $cashAccount = \App\Models\CashAccount::where('uuid', $request->cash_account_id)->first();
                if (!$cashAccount) {
                    return $this->resourceNotFound('cash account', $request->cash_account_id, 'cash_account_id');
                }
            }

            $category = null;
            if ($request->has('category_id') && $request->category_id) {
                $category = \App\Models\Category::where('uuid', $request->category_id)->first();
                if (!$category) {
                    return $this->resourceNotFound('category', $request->category_id, 'category_id');
                }
            }

            // Check if payment for this period already exists
            $existingPayment = MemberPayment::where('member_id', $member->id)
                ->where('period', $request->period)
                ->first();

            if ($existingPayment) {
                return $this->errorResponse('Payment for this period already exists', 400);
            }

            // Generate payment code
            $paymentCode = 'PAY-' . now()->format('Ymd') . '-' . str_pad(MemberPayment::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $cashTransactionId = null;

            $referenceNumber = $this->generateReferenceNumber();

            // If paid, create cash transaction
            if ($request->status === 'paid' && $cashAccount && $category) {
                $transactionCode = 'CASH-IN-' . now()->format('Ymd') . '-' . str_pad(CashTransaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

                $cashTransaction = CashTransaction::create([
                    'transaction_code' => $transactionCode,
                    'cash_account_id' => $cashAccount->id,
                    'category_id' => $category->id,
                    'type' => 'in',
                    'amount' => $request->amount,
                    'transaction_date' => $request->payment_date,
                    'description' => "Pembayaran iuran {$request->period} - " . $member->name,
                    'notes' => $request->notes,
                    'reference_number' => $referenceNumber,
                    'recorded_by' => auth()->check() ? auth()->id() : 1,
                ]);

                $cashTransactionId = $cashTransaction->id;

                // Update cash account balance
                $cashAccount->increment('balance', $request->amount);
            }

            $request->merge([
                'due_date' => $request->due_date ?: null
            ]);

            // Create member payment
            $payment = MemberPayment::create([
                'payment_code' => $paymentCode,
                'member_id' => $member->id,
                'cash_transaction_id' => $cashTransactionId,
                'period' => $request->period,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'recorded_by' => auth()->check() ? auth()->id() : 1,
            ]);

            DB::commit();

            $payment->load(['member', 'recorder', 'cashTransaction']);

            return $this->successResponse($payment, 'Payment recorded successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Payment failed: ' . $e->getMessage(), 500);
        }
    }

    public function show(MemberPayment $memberPayment): JsonResponse
    {
        $memberPayment->load(['member', 'recorder', 'cashTransaction']);
        return $this->successResponse($memberPayment);
    }

    public function update(Request $request, MemberPayment $memberPayment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:paid,unpaid,partial,late',
            'payment_method' => 'required|in:cash,transfer,other',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $memberPayment->update($request->all());

        return $this->successResponse($memberPayment, 'Payment updated successfully');
    }

    public function unpaidByPeriod(Request $request, string $period): JsonResponse
    {
        $unpaidMembers = Member::whereDoesntHave('payments', function ($query) use ($period) {
            $query->where('period', $period)
                  ->where('status', 'paid');
        })->active()->get();

        return $this->successResponse($unpaidMembers);
    }

    public function summary(Request $request): JsonResponse
    {
        $period = $request->input('period', now()->format('Y-m'));

        $totalMembers = Member::active()->count();
        $paidCount = MemberPayment::period($period)->paid()->count();
        $unpaidCount = $totalMembers - $paidCount;
        $totalAmount = MemberPayment::period($period)->paid()->sum('amount');

        return $this->successResponse([
            'period' => $period,
            'total_members' => $totalMembers,
            'paid_count' => $paidCount,
            'unpaid_count' => $unpaidCount,
            'total_amount' => $totalAmount,
            'payment_rate' => $totalMembers > 0 ? round(($paidCount / $totalMembers) * 100, 2) : 0,
        ]);
    }
}
