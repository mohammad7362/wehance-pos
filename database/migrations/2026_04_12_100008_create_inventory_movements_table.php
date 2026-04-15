<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['purchase', 'sale', 'return', 'adjustment', 'transfer_in', 'transfer_out', 'initial']);
            $table->decimal('quantity', 12, 3);  // positive = in, negative = out
            $table->decimal('quantity_before', 12, 3)->default(0);
            $table->decimal('quantity_after', 12, 3)->default(0);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reference_type')->nullable(); // App\Models\Sale, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
