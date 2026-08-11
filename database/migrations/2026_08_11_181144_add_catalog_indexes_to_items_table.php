<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->index(['category_id', 'is_active', 'is_purchasable'], 'items_category_active_purchasable_idx');
            $table->index(['category_id', 'brand_id', 'is_active'], 'items_category_brand_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_category_active_purchasable_idx');
            $table->dropIndex('items_category_brand_active_idx');
        });
    }
};
