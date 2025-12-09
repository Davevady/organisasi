<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Member::create([
                'member_code' => 'MBR-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => 'Member ' . $i,
                'email' => 'member' . $i . '@test.com',
                'phone' => '0812345678' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'address' => 'Jl. Test No. ' . $i . ', Jakarta',
                'status' => $i <= 8 ? 'active' : 'inactive',
                'join_date' => now()->subMonths(rand(1, 24)),
                'notes' => 'Member testing data ' . $i,
            ]);
        }
    }
}
