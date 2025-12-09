<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{CashAccount, CashTransaction, Category, Member, MemberPayment, User};

class MemberPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::all();
        $treasurer = User::where('email', 'treasurer@test.com')->first();
        $kasUtama = CashAccount::where('code', 'KAS-001')->first();
        $bankBCA = CashAccount::where('code', 'BNK-BCA-001')->first();
        $iuranCategory = Category::where('slug', 'iuran-member')->first();

        $periods = ['2024-09', '2024-10', '2024-11', '2024-12'];

        foreach ($members as $member) {
            foreach ($periods as $index => $period) {
                $isPaid = $index < 3 || rand(0, 1); // Last month some unpaid
                $paymentDate = $isPaid ? now()->subMonths(3 - $index)->addDays(rand(1, 15)) : null;

                $payment = MemberPayment::create([
                    'member_id' => $member->id,
                    'period' => $period,
                    'amount' => 100000,
                    'due_date' => now()->parse($period . '-01')->addMonth()->subDay(),
                    'payment_date' => $isPaid ? $paymentDate : now()->parse($period . '-01'),
                    'status' => $isPaid ? 'paid' : 'unpaid',
                    'payment_method' => $isPaid ? (rand(0, 1) ? 'cash' : 'transfer') : 'cash',
                    'payment_code' => $isPaid ? 'PAY-' . strtoupper(str_replace('-', '', $period)) . '-' . $member->member_code : 'PAY-UNPAID-' . strtoupper(str_replace('-', '', $period)) . '-' . $member->member_code,
                    'notes' => $isPaid ? 'Pembayaran iuran bulan ' . $period : 'Belum dibayar',
                    'recorded_by' => $treasurer->id,
                ]);

                // Create cash transaction for paid payments
                if ($isPaid) {
                    $account = $payment->payment_method === 'cash' ? $kasUtama : $bankBCA;

                    $cashTransaction = CashTransaction::create([
                        'cash_account_id' => $account->id,
                        'category_id' => $iuranCategory->id,
                        'type' => 'in',
                        'amount' => $payment->amount,
                        'transaction_date' => $paymentDate,
                        'transaction_code' => 'CSH-PAY-' . $payment->payment_code,
                        'reference_number' => $payment->payment_code,
                        'description' => 'Iuran member ' . $member->name . ' periode ' . $period,
                        'recorded_by' => $treasurer->id,
                    ]);

                    $payment->update(['cash_transaction_id' => $cashTransaction->id]);
                }
            }
        }
    }
}
