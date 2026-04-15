<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('secondary_currency', 10)->nullable()->after('currency_symbol');
            $table->string('secondary_currency_symbol', 10)->nullable()->after('secondary_currency');
            $table->decimal('exchange_rate', 15, 4)->nullable()->after('secondary_currency_symbol');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['secondary_currency', 'secondary_currency_symbol', 'exchange_rate']);
        });
    }
};