<?php

namespace Tests\Unit;

use App\Models\InstallmentPlan;
use App\Services\Sale\InstallmentScheduleCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstallmentScheduleCalculatorTest extends TestCase
{
    #[Test]
    public function it_applies_flat_monthly_interest_without_down_payment(): void
    {
        $plan = new InstallmentPlan([
            'term_months' => 10,
            'down_payment_percent' => 0,
            'monthly_interest_percent' => 3,
            'max_financiable_amount' => 150000000,
            'down_payment_required_above' => 100000000,
            'min_down_payment_percent' => 10,
        ]);

        $result = app(InstallmentScheduleCalculator::class)->calculate($plan, 80000000);

        $this->assertSame(0.0, $result['effective_down_payment_percent']);
        $this->assertSame('0.0000', $result['plan_down_payment_amount']);
        $this->assertSame('0.0000', $result['mandatory_down_payment_amount']);
        $this->assertSame('0.0000', $result['down_payment_amount']);
        $this->assertSame('80000000.0000', $result['financed_amount']);
        $this->assertCount(10, $result['schedule']);
        $this->assertSame('2400000.0000', $result['schedule'][0]['interest_amount']);
        $this->assertSame('24000000.0000', $result['total_interest']);
    }

    #[Test]
    public function it_forces_minimum_down_payment_above_threshold(): void
    {
        $plan = new InstallmentPlan([
            'term_months' => 10,
            'down_payment_percent' => 0,
            'monthly_interest_percent' => 3,
            'max_financiable_amount' => 150000000,
            'down_payment_required_above' => 100000000,
            'min_down_payment_percent' => 10,
        ]);

        $result = app(InstallmentScheduleCalculator::class)->calculate($plan, 120000000);

        $this->assertSame(10.0, $result['effective_down_payment_percent']);
        $this->assertSame('12000000.0000', $result['plan_down_payment_amount']);
        $this->assertSame('12000000.0000', $result['down_payment_amount']);
        $this->assertSame('108000000.0000', $result['financed_amount']);
        $this->assertSame(0, $result['schedule'][0]['sequence']);
        $this->assertCount(11, $result['schedule']);
    }

    #[Test]
    public function it_adds_mandatory_cash_down_payment_to_plan_down_payment(): void
    {
        $plan = new InstallmentPlan([
            'term_months' => 10,
            'down_payment_percent' => 10,
            'monthly_interest_percent' => 0,
            'max_financiable_amount' => 150000000,
            'down_payment_required_above' => null,
            'min_down_payment_percent' => 0,
        ]);

        $result = app(InstallmentScheduleCalculator::class)->calculate($plan, 1000000, 500000);

        $this->assertSame('100000.0000', $result['plan_down_payment_amount']);
        $this->assertSame('500000.0000', $result['mandatory_down_payment_amount']);
        $this->assertSame('600000.0000', $result['down_payment_amount']);
        $this->assertSame('900000.0000', $result['financed_amount']);
        $this->assertSame('1500000.0000', $result['total_payable']);
        $this->assertSame(0, $result['schedule'][0]['sequence']);
        $this->assertSame('600000.0000', $result['schedule'][0]['total_amount']);
    }

    #[Test]
    public function it_rejects_when_financed_base_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $plan = new InstallmentPlan([
            'term_months' => 10,
            'down_payment_percent' => 0,
            'monthly_interest_percent' => 2,
            'max_financiable_amount' => 150000000,
        ]);

        app(InstallmentScheduleCalculator::class)->calculate($plan, 0, 500000);
    }

    #[Test]
    public function it_rejects_when_financed_exceeds_max(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $plan = new InstallmentPlan([
            'term_months' => 10,
            'down_payment_percent' => 0,
            'monthly_interest_percent' => 2,
            'max_financiable_amount' => 150000000,
            'down_payment_required_above' => null,
            'min_down_payment_percent' => 0,
        ]);

        app(InstallmentScheduleCalculator::class)->calculate($plan, 160000000);
    }
}
