<?php

namespace Tests\Feature;

use App\Models\InstallmentPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstallmentPlanMaskedAmountTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_stores_masked_money_fields_as_plain_amounts(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('sale.installment-plan.create')
            ->set('form.title', 'پلن ده ماهه')
            ->set('form.term_months', 10)
            ->set('form.down_payment_percent', '10')
            ->set('form.monthly_interest_percent', '2')
            ->set('form.max_financiable_amount', '50,000,000')
            ->set('form.down_payment_required_above', '20,000,000')
            ->set('form.min_order_amount', '5,000,000')
            ->set('form.max_order_amount', '80,000,000')
            ->call('save')
            ->assertHasNoErrors();

        $plan = InstallmentPlan::query()->first();

        $this->assertNotNull($plan);
        $this->assertSame('50000000.0000', (string) $plan->max_financiable_amount);
        $this->assertSame('20000000.0000', (string) $plan->down_payment_required_above);
        $this->assertSame('5000000.0000', (string) $plan->min_order_amount);
        $this->assertSame('80000000.0000', (string) $plan->max_order_amount);
    }
}
