<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CashTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = CashTransaction::with(['cashAccount', 'category']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('cash_account')) {
            $query->where('cash_account_id', $request->cash_account);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $cashAccounts = CashAccount::active()->get(['id', 'name'])->makeVisible(['id']);
        $categories = Category::active()->cash()->get(['id', 'name'])->makeVisible(['id']);

        return Inertia::render('Cash/Transactions/Index', [
            'transactions' => $query
                ->latest('transaction_date')
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),

            'cashAccounts' => $cashAccounts,
            'categories' => $categories,

            'filters' => $request->only([
                'search',
                'type',
                'cash_account',
                'category',
            ]),
        ]);
    }

    public function summary()
    {
        $totalIn = CashTransaction::in()->sum('amount');
        $totalOut = CashTransaction::out()->sum('amount');
        $balance = $totalIn - $totalOut;
        $transactionCount = CashTransaction::count();

        $todayIn = CashTransaction::in()->whereDate('transaction_date', today())->sum('amount');
        $todayOut = CashTransaction::out()->whereDate('transaction_date', today())->sum('amount');

        return Inertia::render('Cash/Transactions/Summary', [
            'summary' => [
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'balance' => (float) $balance,
                'transaction_count' => $transactionCount,
                'today_in' => (float) $todayIn,
                'today_out' => (float) $todayOut,
            ]
        ]);
    }

    public function create()
    {
        $accounts = CashAccount::active()->get(['id', 'name', 'code'])->makeVisible(['id']);
        $categories = Category::active()->cash()->get(['id', 'name', 'type'])->makeVisible(['id']);
        
        if ($categories->isEmpty()) {
            $categories = Category::all(['id', 'name'])->makeVisible(['id']);
        }

        return Inertia::render('Cash/Transactions/Create', [
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', Rule::in(['in', 'out'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $count = CashTransaction::withTrashed()->whereDate('created_at', today())->count() + 1;
            $transactionCode = 'TRX-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $transaction = CashTransaction::create([
                ...$validated,
                'transaction_code' => $transactionCode,
                'recorded_by' => $request->user()->id,
            ]);

            $account = CashAccount::find($validated['cash_account_id']);
            if ($validated['type'] === 'in') {
                $account->increment('balance', $validated['amount']);
            } else {
                $account->decrement('balance', $validated['amount']);
            }
        });

        return redirect()
            ->route('cash-transaction.index')
            ->with('success', 'Transaksi kas berhasil dicatat.');
    }

    public function show(string $uuid)
    {
        $transaction = CashTransaction::with(['cashAccount', 'category', 'recorder'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return Inertia::render('Cash/Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    public function edit(string $id)
    {
        abort(404, 'Transaksi kas tidak dapat diubah setelah dicatat.');
    }

    public function update(Request $request, string $id)
    {
        abort(404, 'Transaksi kas tidak dapat diubah setelah dicatat.');
    }

    public function destroy(string $uuid)
    {
        $transaction = CashTransaction::where('uuid', $uuid)->firstOrFail();

        DB::transaction(function () use ($transaction) {
            $account = CashAccount::find($transaction->cash_account_id);
            if ($transaction->type === 'in') {
                $account->decrement('balance', $transaction->amount);
            } else {
                $account->increment('balance', $transaction->amount);
            }

            $transaction->delete();
        });

        return redirect()
            ->route('cash-transaction.index')
            ->with('success', 'Transaksi kas berhasil dihapus.');
    }

    public function trashed(Request $request)
    {
        $query = CashTransaction::onlyTrashed()->with(['cashAccount', 'category']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $request->search . '%');
            });
        }

        return Inertia::render('Cash/Transactions/Trashed', [
            'transactions' => $query
                ->latest('deleted_at')
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function restore(string $uuid)
    {
        $transaction = CashTransaction::onlyTrashed()->where('uuid', $uuid)->firstOrFail();

        DB::transaction(function () use ($transaction) {
            $account = CashAccount::find($transaction->cash_account_id);
            if ($transaction->type === 'in') {
                $account->increment('balance', $transaction->amount);
            } else {
                $account->decrement('balance', $transaction->amount);
            }

            $transaction->restore();
        });

        return redirect()
            ->route('cash-transaction.trashed')
            ->with('success', 'Transaksi kas berhasil dipulihkan.');
    }

    public function forceDelete(string $uuid)
    {
        $transaction = CashTransaction::onlyTrashed()->where('uuid', $uuid)->firstOrFail();

        $transaction->forceDelete();

        return redirect()
            ->route('cash-transaction.trashed')
            ->with('success', 'Transaksi kas berhasil dihapus secara permanen.');
    }
}