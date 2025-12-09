<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{DB, Validator};
use App\Models\{CashAccount, CashTransaction};
use App\Traits\ApiPaginationTrait;
use Pest\Support\Str;

class CashTransactionController extends BaseApiController
{
    use ApiPaginationTrait;

    public function index(Request $request): JsonResponse
    {
        $query = CashTransaction::with(['cashAccount', 'category', 'recorder']);

        // Filter by account
        if ($request->has('cash_account_id')) {
            $query->where('cash_account_id', $request->cash_account_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $query->latest('transaction_date');

        return $this->paginatedResponse($query, $request, 'Cash transactions retrieved successfully');
    }

    private function generateReferenceNumber(int $length = 6): string
    {
        $randomString = strtoupper(Str::random($length));
        return 'CASH-' . now()->format('Ymd') . '-' . $randomString;
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cash_account_id' => 'required|exists:cash_accounts,uuid',
            'category_id' => 'required|exists:categories,uuid',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        try {
            DB::beginTransaction();

            // Resolve UUIDs to internal IDs
            $cashAccount = CashAccount::where('uuid', $request->cash_account_id)->first();
            if (!$cashAccount) {
                return $this->resourceNotFound('cash account', $request->cash_account_id, 'cash_account_id');
            }

            $category = \App\Models\Category::where('uuid', $request->category_id)->first();
            if (!$category) {
                return $this->resourceNotFound('category', $request->category_id, 'category_id');
            }

            // Check balance for 'out' transactions
            if ($request->type === 'out' && $cashAccount->balance < $request->amount) {
                return $this->errorResponse('Insufficient balance', 400, [
                    'available_balance' => $cashAccount->balance
                ]);
            }

            // Generate transaction code
            $transactionCode = 'CASH-' . strtoupper($request->type) . '-' . now()->format('Ymd') . '-' . str_pad(CashTransaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $referenceNumber = $this->generateReferenceNumber();

            // Create transaction
            $transaction = CashTransaction::create([
                'transaction_code' => $transactionCode,
                'cash_account_id' => $cashAccount->id,
                'category_id' => $category->id,
                'type' => $request->type,
                'amount' => $request->amount,
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'notes' => $request->notes,
                'reference_number' => $referenceNumber,
                'attachment' => $request->attachment,
                'recorded_by' => auth()->check() ? auth()->id() : 1,
            ]);

            // Update account balance
            if ($request->type === 'in') {
                $cashAccount->increment('balance', $request->amount);
            } else {
                $cashAccount->decrement('balance', $request->amount);
            }

            DB::commit();

            $transaction->load(['cashAccount', 'category', 'recorder']);

            return $this->successResponse($transaction, 'Transaction created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Transaction failed: ' . $e->getMessage(), 500);
        }
    }

    public function show(CashTransaction $cashTransaction): JsonResponse
    {
        $cashTransaction->load(['cashAccount', 'category', 'recorder']);
        return $this->successResponse($cashTransaction);
    }

    public function summary(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $totalIn = CashTransaction::in()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalOut = CashTransaction::out()
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $balance = $totalIn - $totalOut;

        $totalAccounts = CashAccount::active()->sum('balance');

        return $this->successResponse([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'balance' => $balance,
            'total_accounts_balance' => $totalAccounts,
        ]);
    }

    public function byCategory(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $type = $request->input('type', 'out'); // in or out

        $data = CashTransaction::with('category')
            ->where('type', $type)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get();

        return $this->successResponse($data);
    }
}
