<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->decimal('max_order_amount', 18, 4)->nullable()->after('min_order_amount');
        });
    }

    public function down(): void
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->dropColumn('max_order_amount');
        });
    }
};
