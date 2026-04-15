<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->nullable()->unique(); // coupon code
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->decimal('min_purchase', 12, 2)->nullable();
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->integer('usage_limit')->nullable(); // null = unlimited
            $table->integer('usage_per_customer')->default(1);
            $table->integer('used_count')->default(0);
            $table->enum('applies_to', ['all', 'category', 'product'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
            $table->morphs('applicable'); // category or product
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_items');
        Schema::dropIfExists('discounts');
    }
};
