<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('installment_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sale_type')->index();
            $table->string('status', 30)->index();
            $table->decimal('subtotal', 18, 4);
            $table->decimal('discount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4);

            $table->unsignedTinyInteger('plan_term_months')->nullable();
            $table->decimal('plan_down_payment_percent', 5, 2)->nullable();
            $table->decimal('plan_monthly_interest_percent', 5, 4)->nullable();
            $table->decimal('plan_max_financiable_amount', 18, 4)->nullable();
            $table->decimal('down_payment_amount', 18, 4)->default(0);
            $table->decimal('financed_amount', 18, 4)->default(0);
            $table->decimal('total_interest', 18, 4)->default(0);
            $table->decimal('total_payable', 18, 4)->default(0);
            $table->decimal('outstanding_balance', 18, 4)->default(0);

            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('item_price_id')->nullable()->constrained('item_prices')->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 18, 4);
            $table->decimal('line_total', 18, 4);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('order_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');
            $table->date('due_date');
            $table->decimal('principal_amount', 18, 4);
            $table->decimal('interest_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4);
            $table->decimal('paid_amount', 18, 4)->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'sequence']);
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_installments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
