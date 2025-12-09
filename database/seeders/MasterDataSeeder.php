<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Category, Unit};

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Inventory Categories
        $electronics = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'type' => 'inventory',
            'description' => 'Peralatan elektronik'
        ]);

        Category::create([
            'name' => 'Laptop',
            'slug' => 'laptop',
            'type' => 'inventory',
            'parent_id' => $electronics->id
        ]);

        Category::create([
            'name' => 'Furniture',
            'slug' => 'furniture',
            'type' => 'inventory',
            'description' => 'Perabotan kantor'
        ]);

        Category::create([
            'name' => 'ATK',
            'slug' => 'atk',
            'type' => 'inventory',
            'description' => 'Alat tulis kantor'
        ]);

        // Cash Categories
        Category::create([
            'name' => 'Iuran Member',
            'slug' => 'iuran-member',
            'type' => 'cash',
            'description' => 'Pemasukan dari iuran anggota'
        ]);

        Category::create([
            'name' => 'Donasi',
            'slug' => 'donasi',
            'type' => 'cash',
            'description' => 'Donasi dari pihak luar'
        ]);

        Category::create([
            'name' => 'Operasional',
            'slug' => 'operasional',
            'type' => 'cash',
            'description' => 'Biaya operasional'
        ]);

        Category::create([
            'name' => 'Pembelian Aset',
            'slug' => 'pembelian-aset',
            'type' => 'cash',
            'description' => 'Pembelian aset/inventaris'
        ]);

        // Units
        Unit::create(['name' => 'Unit', 'symbol' => 'pcs', 'description' => 'Pieces/Unit']);
        Unit::create(['name' => 'Box', 'symbol' => 'box', 'description' => 'Box']);
        Unit::create(['name' => 'Lusin', 'symbol' => 'dzn', 'description' => 'Dozen']);
        Unit::create(['name' => 'Set', 'symbol' => 'set', 'description' => 'Set']);
        Unit::create(['name' => 'Kilogram', 'symbol' => 'kg', 'description' => 'Kilogram']);
        Unit::create(['name' => 'Liter', 'symbol' => 'L', 'description' => 'Liter']);
    }
}
