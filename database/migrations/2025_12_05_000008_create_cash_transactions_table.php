<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('transaction_code')->unique();
            $table->foreignId('cash_account_id')->constrained()->onDelete('restrict');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['in', 'out']); // Kas masuk atau keluar
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('description');
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('attachment')->nullable(); // File bukti transaksi
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
