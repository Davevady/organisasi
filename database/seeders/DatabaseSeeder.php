<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder dijalankan berurutan sesuai dependency
        $this->call([
            // 1. Master data yang tidak bergantung pada tabel lain
            RoleSeeder::class,
            MasterDataSeeder::class, // Categories & Units

            // 2. Users (bergantung pada Roles)
            UserSeeder::class,

            // 3. Members (independen)
            MemberSeeder::class,

            // 4. Inventory (bergantung pada Categories, Units, Users)
            InventorySeeder::class,

            // 5. Cash Management (bergantung pada Categories, Users)
            CashManagementSeeder::class,

            // 6. Member Payments (bergantung pada Members, CashAccounts, Categories, Users)
            MemberPaymentSeeder::class,
        ]);
    }
}
