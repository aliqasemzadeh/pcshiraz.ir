<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('cash_only_subtotal', 18, 4)->default(0)->after('total_amount');
            $table->decimal('installment_subtotal', 18, 4)->default(0)->after('cash_only_subtotal');
            $table->decimal('plan_down_payment_amount', 18, 4)->default(0)->after('plan_max_financiable_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('price_type')->nullable()->index()->after('item_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'cash_only_subtotal',
                'installment_subtotal',
                'plan_down_payment_amount',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('price_type');
        });
    }
};
