<?php

namespace App\Http\Controllers\Api;

use App\Models\CashAccount;
use App\Traits\ApiPaginationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashAccountController extends BaseApiController
{
    use ApiPaginationTrait;

    public function index(Request $request): JsonResponse
    {
        $query = CashAccount::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        return $this->paginatedResponse($query, $request, 'Cash accounts retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:cash_accounts,code',
            'type' => 'required|in:bank,cash',
            'account_number' => 'nullable|string|max:255',
            'balance' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $account = CashAccount::create($request->all());

        return $this->successResponse($account, 'Cash account created successfully', 201);
    }

    public function show(CashAccount $cashAccount): JsonResponse
    {
        return $this->successResponse($cashAccount);
    }

    public function update(Request $request, CashAccount $cashAccount): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:cash_accounts,code,' . $cashAccount->id,
            'type' => 'required|in:bank,cash',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $cashAccount->update($request->except('balance')); // Balance tidak diubah langsung

        return $this->successResponse($cashAccount, 'Cash account updated successfully');
    }

    public function destroy(CashAccount $cashAccount): JsonResponse
    {
        $cashAccount->delete();

        return $this->successResponse(null, 'Cash account deleted successfully');
    }
}
