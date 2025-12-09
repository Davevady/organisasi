<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('transaction_code')->unique();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['in', 'out', 'adjustment']); // Masuk, Keluar, Penyesuaian
            $table->decimal('quantity', 15, 2);
            $table->decimal('price_per_unit', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->decimal('stock_before', 15, 2); // Stock sebelum transaksi
            $table->decimal('stock_after', 15, 2); // Stock setelah transaksi
            $table->date('transaction_date');
            $table->string('reference_number')->nullable(); // Nomor PO, Invoice, dll
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
