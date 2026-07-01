<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->string('item_type')->default('product')->index();

            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->boolean('is_main')->default(false);

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->string('color_name')->nullable();

            $table->integer('weight')->nullable();
            $table->integer('length')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('slug')->unique();
            $table->text('meta_description')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['domain_id', 'category_id', 'is_main'], 'items_union_main_idx');
            $table->index(['domain_id', 'category_id', 'group_id'], 'items_union_no_group_idx');
            $table->index(['domain_id', 'category_id', 'title'], 'items_search_by_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
