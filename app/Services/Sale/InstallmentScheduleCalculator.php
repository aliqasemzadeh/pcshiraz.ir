<?php

namespace App\Services\Sale;

use App\Models\InstallmentPlan;
use Carbon\Carbon;
use InvalidArgumentException;

class InstallmentScheduleCalculator
{
    /**
     * @return array{
     *     effective_down_payment_percent: float,
     *     down_payment_amount: string,
     *     financed_amount: string,
     *     total_interest: string,
     *     total_payable: string,
     *     monthly_payment: string,
     *     schedule: list<array{
     *         sequence: int,
     *         due_date: Carbon,
     *         principal_amount: string,
     *         interest_amount: string,
     *         total_amount: string
     *     }>
     * }
     */
    public function calculate(InstallmentPlan $plan, string|float $orderTotal, ?Carbon $startDate = null): array
    {
        $total = $this->normalize($orderTotal);
        $startDate ??= now()->startOfDay();

        if (bccomp($total, '0', 4) <= 0) {
            throw new InvalidArgumentException('Order total must be greater than zero.');
        }

        if ($plan->min_order_amount !== null && bccomp($total, (string) $plan->min_order_amount, 4) < 0) {
            throw new InvalidArgumentException(__('general.order_below_plan_minimum'));
        }

        $effectiveDownPercent = (float) $plan->down_payment_percent;

        if (
            $plan->down_payment_required_above !== null
            && bccomp($total, (string) $plan->down_payment_required_above, 4) > 0
            && $effectiveDownPercent <= 0
        ) {
            $effectiveDownPercent = (float) $plan->min_down_payment_percent;
        }

        $downPayment = $this->percentOf($total, $effectiveDownPercent);
        $financed = bcsub($total, $downPayment, 4);

        if (
            $plan->max_financiable_amount !== null
            && bccomp($financed, (string) $plan->max_financiable_amount, 4) > 0
        ) {
            throw new InvalidArgumentException(__('general.financed_exceeds_plan_max'));
        }

        $termMonths = max(1, (int) $plan->term_months);
        $monthlyRate = bcdiv((string) $plan->monthly_interest_percent, '100', 8);
        $monthlyInterest = bcmul($financed, $monthlyRate, 4);
        $monthlyPrincipal = $termMonths > 0 ? bcdiv($financed, (string) $termMonths, 4) : '0';

        $schedule = [];

        if (bccomp($downPayment, '0', 4) > 0) {
            $schedule[] = [
                'sequence' => 0,
                'due_date' => $startDate->copy(),
                'principal_amount' => $downPayment,
                'interest_amount' => '0.0000',
                'total_amount' => $downPayment,
            ];
        }

        $allocatedPrincipal = '0.0000';

        for ($i = 1; $i <= $termMonths; $i++) {
            $isLast = $i === $termMonths;
            $principal = $isLast
                ? bcsub($financed, $allocatedPrincipal, 4)
                : $monthlyPrincipal;

            $allocatedPrincipal = bcadd($allocatedPrincipal, $principal, 4);
            $interest = $monthlyInterest;
            $rowTotal = bcadd($principal, $interest, 4);

            $schedule[] = [
                'sequence' => $i,
                'due_date' => $startDate->copy()->addMonthsNoOverflow($i),
                'principal_amount' => $principal,
                'interest_amount' => $interest,
                'total_amount' => $rowTotal,
            ];
        }

        $totalInterest = '0.0000';
        $totalPayable = '0.0000';

        foreach ($schedule as $row) {
            $totalInterest = bcadd($totalInterest, $row['interest_amount'], 4);
            $totalPayable = bcadd($totalPayable, $row['total_amount'], 4);
        }

        $monthlyPayment = bcadd($monthlyPrincipal, $monthlyInterest, 4);

        return [
            'effective_down_payment_percent' => $effectiveDownPercent,
            'down_payment_amount' => $downPayment,
            'financed_amount' => $financed,
            'total_interest' => $totalInterest,
            'total_payable' => $totalPayable,
            'monthly_payment' => $monthlyPayment,
            'schedule' => $schedule,
        ];
    }

    public function isEligible(InstallmentPlan $plan, string|float $orderTotal): bool
    {
        try {
            $this->calculate($plan, $orderTotal);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    protected function percentOf(string $amount, float $percent): string
    {
        if ($percent <= 0) {
            return '0.0000';
        }

        return bcmul($amount, bcdiv((string) $percent, '100', 8), 4);
    }

    protected function normalize(string|float $amount): string
    {
        return number_format((float) $amount, 4, '.', '');
    }
}
