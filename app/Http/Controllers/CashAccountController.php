<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CashAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = CashAccount::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%')
                    ->orWhere('account_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        return Inertia::render('Cash/Accounts/Index', [
            'cashAccounts' => $query
                ->latest()
                ->paginate(10)
                ->withQueryString(),

            'filters' => $request->only([
                'search',
                'type',
                'status',
            ]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Cash/Accounts/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:cash_accounts,code'],
            'type' => ['required', Rule::in(['bank', 'cash'])],
            'account_number' => ['nullable', 'string', 'max:100'],
            'balance' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        CashAccount::create([
            ...$validated,
            'balance' => $validated['balance'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('cash-account.index')
            ->with('success', 'Cash account berhasil ditambahkan.');
    }

    public function edit(string $uuid)
    {
        $cashAccount = CashAccount::where('uuid', $uuid)->firstOrFail();

        return Inertia::render('Cash/Accounts/Edit', [
            'cashAccount' => $cashAccount,
        ]);
    }

    public function update(Request $request, string $uuid)
    {
        $cashAccount = CashAccount::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cash_accounts')->ignore($cashAccount->id),
            ],
            'type' => ['required', Rule::in(['bank', 'cash'])],
            'account_number' => ['nullable', 'string', 'max:100'],
            'balance' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $cashAccount->update($validated);

        return redirect()
            ->route('cash-account.index')
            ->with('success', 'Cash account berhasil diperbarui.');
    }

    public function destroy(string $uuid)
    {
        $cashAccount = CashAccount::where('uuid', $uuid)->firstOrFail();

        $cashAccount->delete();

        return redirect()
            ->route('cash-account.index')
            ->with('success', 'Cash account berhasil dihapus.');
    }
}