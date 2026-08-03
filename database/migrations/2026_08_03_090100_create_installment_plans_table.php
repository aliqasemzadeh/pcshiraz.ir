<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('term_months');
            $table->decimal('down_payment_percent', 5, 2)->default(0);
            $table->decimal('monthly_interest_percent', 5, 4)->default(0);
            $table->decimal('max_financiable_amount', 18, 4)->nullable();
            $table->decimal('down_payment_required_above', 18, 4)->nullable();
            $table->decimal('min_down_payment_percent', 5, 2)->default(0);
            $table->decimal('min_order_amount', 18, 4)->nullable();
            $table->smallInteger('priority')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('organization_installment_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('installment_plan_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->smallInteger('priority')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'installment_plan_id'], 'org_installment_plan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_installment_plan');
        Schema::dropIfExists('installment_plans');
    }
};
