<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['cash', 'card', 'store_credit']);
            $table->decimal('amount', 12, 2);
            $table->decimal('tendered', 12, 2)->default(0); // for cash
            $table->decimal('change_amount', 12, 2)->default(0); // for cash
            $table->string('stripe_payment_intent')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
