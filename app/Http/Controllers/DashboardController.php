<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\InventoryItem;
use App\Models\Member;
use App\Models\MemberPayment;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $startDate = request(
            'start_date',
            now()->startOfMonth()->toDateString()
        );

        $endDate = request(
            'end_date',
            now()->toDateString()
        );

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // ==========================
        // Summary Cards
        // ==========================

        $totalMembers = Member::count();

        $activeMembers = Member::where('status', 'active')->count();

        $cashBalance = CashAccount::sum('balance');

        $inventoryItems = InventoryItem::where('status', 'available')
            ->count();

        // ==========================
        // Financial Summary
        // ==========================

        $monthlyIncome = CashTransaction::where('type', 'in')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $monthlyExpense = CashTransaction::where('type', 'out')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // ==========================
        // Member Payment Summary
        // ==========================

        $paymentSummary = [
            'paid' => MemberPayment::where('status', 'paid')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->count(),

            'unpaid' => MemberPayment::where('status', 'unpaid')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->count(),

            'partial' => MemberPayment::where('status', 'partial')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->count(),

            'late' => MemberPayment::where('status', 'late')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->count(),
        ];

        // ==========================
        // Inventory Alert
        // ==========================

        $lowStockItems = InventoryItem::with(['unit'])
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        // ==========================
        // Recent Transactions
        // ==========================

        $recentTransactions = CashTransaction::with([
            'cashAccount',
            'category'
        ])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderByDesc('transaction_date')
            ->limit(5)
            ->get();

        // ==========================
        // Recent Payments
        // ==========================

        $recentPayments = MemberPayment::with('member')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();

        // ==========================
        // Recent Members
        // ==========================

        $recentMembers = Member::orderByDesc('join_date')
            ->limit(5)
            ->get();

        $cashFlow = [];

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {

            $income = CashTransaction::whereDate(
                'transaction_date',
                $currentDate
            )
                ->where('type', 'in')
                ->sum('amount');

            $expense = CashTransaction::whereDate(
                'transaction_date',
                $currentDate
            )
                ->where('type', 'out')
                ->sum('amount');

            $cashFlow[] = [
                'date' => $currentDate->toDateString(),
                'income' => (float) $income,
                'expense' => (float) $expense,
            ];

            $currentDate->addDay();
        }

        return Inertia::render('Dashboard', [

            'cashFlow' => $cashFlow,

            'summary' => [
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'cash_balance' => (float) $cashBalance,
                'inventory_items' => $inventoryItems,
                'monthly_income' => (float) $monthlyIncome,
                'monthly_expense' => (float) $monthlyExpense,
                'net_balance' => (float) ($monthlyIncome - $monthlyExpense),
            ],

            'paymentSummary' => $paymentSummary,

            'lowStockItems' => $lowStockItems->map(fn($item) => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'current_stock' => (float) $item->current_stock,
                'minimum_stock' => (float) $item->minimum_stock,
                'unit' => [
                    'name' => $item->unit?->name,
                    'symbol' => $item->unit?->symbol,
                ],
            ]),

            'recentTransactions' => $recentTransactions->map(fn($trx) => [
                'id' => $trx->id,
                'transaction_code' => $trx->transaction_code,
                'transaction_date' => Carbon::parse($trx->transaction_date)->format('d M Y'),
                'description' => $trx->description,
                'amount' => (float) $trx->amount,
                'type' => $trx->type,
                'category' => [
                    'name' => $trx->category?->name,
                ],
                'cash_account' => [
                    'name' => $trx->cashAccount?->name,
                ],
            ]),

            'recentPayments' => $recentPayments->map(fn($payment) => [
                'id' => $payment->id,
                'payment_code' => $payment->payment_code,
                'amount' => (float) $payment->amount,
                'payment_date' => Carbon::parse($payment->payment_date)->format('d M Y'),
                'status' => $payment->status,
                'member' => [
                    'name' => $payment->member?->name,
                    'member_code' => $payment->member?->member_code,
                ],
            ]),

            'recentMembers' => $recentMembers->map(fn($member) => [
                'id' => $member->id,
                'member_code' => $member->member_code,
                'name' => $member->name,
                'status' => $member->status,
                'join_date' => Carbon::parse($member->join_date)->format('d M Y'),
            ]),

        ]);
    }
}
