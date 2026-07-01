<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('price_type')->default('cash')->index();
            $table->decimal('price', 18, 4);
            $table->decimal('sale_price', 18, 4);
            $table->json('meta')->nullable();
            $table->integer('sales_cap')->nullable();
            $table->integer('total_sales_count')->default(0);
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->index(['item_id', 'price_type', 'is_active', 'sale_price'], 'item_prices_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_prices');
    }
};
