<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Role};

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567890',
            'status' => 'active',
        ]);
        $superAdmin->roles()->attach(Role::where('slug', 'super-admin')->first());

        // Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567891',
            'status' => 'active',
        ]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());

        // Treasurer
        $treasurer = User::create([
            'name' => 'Treasurer User',
            'email' => 'treasurer@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567892',
            'status' => 'active',
        ]);
        $treasurer->roles()->attach(Role::where('slug', 'treasurer')->first());

        // Warehouse Staff
        $warehouse = User::create([
            'name' => 'Warehouse Staff',
            'email' => 'warehouse@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567893',
            'status' => 'active',
        ]);
        $warehouse->roles()->attach(Role::where('slug', 'warehouse')->first());

        // Regular User
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'phone' => '081234567894',
            'status' => 'active',
        ]);
        $regularUser->roles()->attach(Role::where('slug', 'user')->first());
    }
}
