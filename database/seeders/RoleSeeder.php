<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access to all features'
        ]);

        Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Can manage inventory and payments'
        ]);

        Role::create([
            'name' => 'Treasurer',
            'slug' => 'treasurer',
            'description' => 'Can manage cash transactions'
        ]);

        Role::create([
            'name' => 'Warehouse',
            'slug' => 'warehouse',
            'description' => 'Can manage inventory only'
        ]);

        Role::create([
            'name' => 'User',
            'slug' => 'user',
            'description' => 'New account'
        ]);
    }
}
