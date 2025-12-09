<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Category, InventoryItem, InventoryTransaction, Unit, User};

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::where('type', 'inventory')->get();
        $units = Unit::all();
        $admin = User::where('email', 'admin@test.com')->first();
        $warehouse = User::where('email', 'warehouse@test.com')->first();

        $inventoryItems = [
            ['code' => 'BRG-001', 'name' => 'Laptop Dell XPS 13', 'category' => 'Laptop', 'unit' => 'pcs', 'stock' => 10, 'min_stock' => 5, 'purchase' => 15000000, 'selling' => 17000000, 'location' => 'Gudang A'],
            ['code' => 'BRG-002', 'name' => 'Monitor LG 24 inch', 'category' => 'Elektronik', 'unit' => 'pcs', 'stock' => 15, 'min_stock' => 8, 'purchase' => 2500000, 'selling' => 3000000, 'location' => 'Gudang A'],
            ['code' => 'BRG-003', 'name' => 'Mouse Logitech Wireless', 'category' => 'Elektronik', 'unit' => 'pcs', 'stock' => 25, 'min_stock' => 10, 'purchase' => 150000, 'selling' => 200000, 'location' => 'Gudang B'],
            ['code' => 'BRG-004', 'name' => 'Keyboard Mechanical', 'category' => 'Elektronik', 'unit' => 'pcs', 'stock' => 20, 'min_stock' => 10, 'purchase' => 500000, 'selling' => 650000, 'location' => 'Gudang B'],
            ['code' => 'BRG-005', 'name' => 'Meja Kantor', 'category' => 'Furniture', 'unit' => 'pcs', 'stock' => 8, 'min_stock' => 5, 'purchase' => 1500000, 'selling' => 2000000, 'location' => 'Gudang C'],
            ['code' => 'BRG-006', 'name' => 'Kursi Gaming', 'category' => 'Furniture', 'unit' => 'pcs', 'stock' => 12, 'min_stock' => 8, 'purchase' => 2000000, 'selling' => 2500000, 'location' => 'Gudang C'],
            ['code' => 'BRG-007', 'name' => 'Lemari Arsip', 'category' => 'Furniture', 'unit' => 'pcs', 'stock' => 5, 'min_stock' => 3, 'purchase' => 3000000, 'selling' => 3500000, 'location' => 'Gudang C'],
            ['code' => 'BRG-008', 'name' => 'Pulpen Pilot', 'category' => 'ATK', 'unit' => 'box', 'stock' => 50, 'min_stock' => 20, 'purchase' => 50000, 'selling' => 75000, 'location' => 'Ruang ATK'],
            ['code' => 'BRG-009', 'name' => 'Kertas A4 Paper One', 'category' => 'ATK', 'unit' => 'box', 'stock' => 100, 'min_stock' => 50, 'purchase' => 45000, 'selling' => 60000, 'location' => 'Ruang ATK'],
            ['code' => 'BRG-010', 'name' => 'Spidol Whiteboard', 'category' => 'ATK', 'unit' => 'box', 'stock' => 30, 'min_stock' => 15, 'purchase' => 35000, 'selling' => 50000, 'location' => 'Ruang ATK'],
            ['code' => 'BRG-011', 'name' => 'Printer HP LaserJet', 'category' => 'Elektronik', 'unit' => 'pcs', 'stock' => 4, 'min_stock' => 5, 'purchase' => 3500000, 'selling' => 4000000, 'location' => 'Gudang A'],
            ['code' => 'BRG-012', 'name' => 'Scanner Canon', 'category' => 'Elektronik', 'unit' => 'pcs', 'stock' => 3, 'min_stock' => 5, 'purchase' => 2500000, 'selling' => 3000000, 'location' => 'Gudang A'],
            ['code' => 'BRG-013', 'name' => 'Headset Sony', 'category' => 'Elektronik', 'unit' => 'pcs', 'stock' => 18, 'min_stock' => 10, 'purchase' => 300000, 'selling' => 400000, 'location' => 'Gudang B'],
            ['code' => 'BRG-014', 'name' => 'Stapler Besar', 'category' => 'ATK', 'unit' => 'pcs', 'stock' => 0, 'min_stock' => 5, 'purchase' => 75000, 'selling' => 100000, 'location' => 'Ruang ATK'],
            ['code' => 'BRG-015', 'name' => 'Penghapus Whiteboard', 'category' => 'ATK', 'unit' => 'pcs', 'stock' => 15, 'min_stock' => 10, 'purchase' => 25000, 'selling' => 35000, 'location' => 'Ruang ATK'],
        ];

        $createdItems = [];
        foreach ($inventoryItems as $item) {
            $category = Category::where('slug', strtolower($item['category']))->first()
                        ?? Category::where('type', 'inventory')->first();
            $unit = Unit::where('symbol', $item['unit'])->first() ?? Unit::first();

            $createdItems[] = InventoryItem::create([
                'code' => $item['code'],
                'name' => $item['name'],
                'description' => 'Deskripsi untuk ' . $item['name'],
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'current_stock' => $item['stock'],
                'minimum_stock' => $item['min_stock'],
                'purchase_price' => $item['purchase'],
                'selling_price' => $item['selling'],
                'location' => $item['location'],
                'status' => $item['stock'] > 0 ? 'available' : 'unavailable',
            ]);
        }

        // Create Inventory Transactions
        foreach ($createdItems as $index => $item) {
            // Transaction IN (restocking)
            $quantity = rand(5, 20);
            $stockBefore = max(0, $item->current_stock - $quantity);

            InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type' => 'in',
                'quantity' => $quantity,
                'price_per_unit' => $item->purchase_price,
                'total_price' => $quantity * $item->purchase_price,
                'stock_before' => $stockBefore,
                'stock_after' => $item->current_stock,
                'transaction_date' => now()->subDays(rand(1, 30)),
                'transaction_code' => 'INV-IN-' . now()->format('Ymd') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'reference_number' => 'PO-2025-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'notes' => 'Pembelian dari vendor',
                'recorded_by' => $admin->id,
            ]);

            // Some items have OUT transactions
            if ($index % 3 === 0 && $item->current_stock > 5) {
                $outQty = rand(2, 5);
                $stockBeforeOut = $item->current_stock + $outQty;

                InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => $outQty,
                    'price_per_unit' => $item->selling_price,
                    'total_price' => $outQty * $item->selling_price,
                    'stock_before' => $stockBeforeOut,
                    'stock_after' => $item->current_stock,
                    'transaction_date' => now()->subDays(rand(1, 15)),
                    'transaction_code' => 'INV-OUT-' . now()->format('Ymd') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'reference_number' => 'SO-2025-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'notes' => 'Penjualan kepada member',
                    'recorded_by' => $warehouse->id,
                ]);
            }
        }
    }
}
