<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('unit_id')->constrained()->onDelete('restrict');
            $table->decimal('current_stock', 15, 2)->default(0);
            $table->decimal('minimum_stock', 15, 2)->default(0); // Alert threshold
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->string('location')->nullable(); // Lokasi penyimpanan
            $table->enum('status', ['available', 'unavailable', 'discontinued'])->default('available');
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
