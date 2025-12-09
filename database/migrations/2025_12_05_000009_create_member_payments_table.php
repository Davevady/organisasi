<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('payment_code')->unique();
            $table->foreignId('member_id')->constrained()->onDelete('restrict');
            $table->foreignId('cash_transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->string('period'); // Format: YYYY-MM untuk iuran bulanan
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['paid', 'unpaid', 'partial', 'late'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'transfer', 'other'])->default('cash');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('period');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_payments');
    }
};
