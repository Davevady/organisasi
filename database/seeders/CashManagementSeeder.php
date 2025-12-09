<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{CashAccount, CashTransaction, Category, User};

class CashManagementSeeder extends Seeder
{
    public function run(): void
    {
        $treasurer = User::where('email', 'treasurer@test.com')->first();

        // Create Cash Accounts with initial balance
        $kasUtama = CashAccount::create([
            'name' => 'Kas Utama',
            'code' => 'KAS-001',
            'type' => 'cash',
            'balance' => 10000000,
            'description' => 'Kas untuk operasional harian'
        ]);

        $bankBCA = CashAccount::create([
            'name' => 'Bank BCA',
            'code' => 'BNK-BCA-001',
            'type' => 'bank',
            'account_number' => '1234567890',
            'balance' => 50000000,
            'description' => 'Rekening utama organisasi'
        ]);

        $cashCategories = Category::where('type', 'cash')->get();

        // Income transactions
        for ($i = 1; $i <= 15; $i++) {
            $account = $i % 2 === 0 ? $kasUtama : $bankBCA;
            $category = $cashCategories->where('slug', $i % 3 === 0 ? 'donasi' : 'iuran-member')->first();

            CashTransaction::create([
                'cash_account_id' => $account->id,
                'category_id' => $category->id,
                'type' => 'in',
                'amount' => rand(100000, 1000000),
                'transaction_date' => now()->subDays(rand(1, 60)),
                'transaction_code' => 'CSH-IN-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'reference_number' => 'REF-IN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'description' => 'Pemasukan ' . $category->name,
                'recorded_by' => $treasurer->id,
            ]);
        }

        // Expense transactions
        for ($i = 1; $i <= 15; $i++) {
            $account = $i % 2 === 0 ? $bankBCA : $kasUtama;
            $category = $cashCategories->where('slug', $i % 3 === 0 ? 'pembelian-aset' : 'operasional')->first();

            CashTransaction::create([
                'cash_account_id' => $account->id,
                'category_id' => $category->id,
                'type' => 'out',
                'amount' => rand(50000, 500000),
                'transaction_date' => now()->subDays(rand(1, 60)),
                'transaction_code' => 'CSH-OUT-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'reference_number' => 'REF-OUT-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'description' => 'Pengeluaran ' . $category->name,
                'recorded_by' => $treasurer->id,
            ]);
        }
    }
}
