<?php

namespace App\Services\Sale;

use App\Models\InstallmentPlan;
use App\Models\Organization;
use Illuminate\Support\Collection;

class InstallmentPlanMatcher
{
    public function __construct(
        protected InstallmentScheduleCalculator $calculator,
    ) {}

    /**
     * @return Collection<int, array{plan: InstallmentPlan, preview: array<string, mixed>}>
     */
    public function eligiblePlans(
        Organization $organization,
        string|float $financedBase,
        string|float $mandatoryDownPayment = 0,
    ): Collection {
        return $this->candidatePlans($organization)
            ->filter(fn (array $row) => $this->calculator->isEligible(
                $row['plan'],
                $financedBase,
                $mandatoryDownPayment,
            ))
            ->map(function (array $row) use ($financedBase, $mandatoryDownPayment) {
                return [
                    'plan' => $row['plan'],
                    'preview' => $this->calculator->calculate(
                        $row['plan'],
                        $financedBase,
                        $mandatoryDownPayment,
                    ),
                    'priority' => $row['priority'],
                ];
            })
            ->sortBy([
                ['priority', 'desc'],
                fn (array $row) => (float) $row['plan']->monthly_interest_percent,
                fn (array $row) => (int) $row['plan']->term_months,
            ])
            ->values()
            ->map(fn (array $row) => [
                'plan' => $row['plan'],
                'preview' => $row['preview'],
            ]);
    }

    /**
     * @return Collection<int, array{plan: InstallmentPlan, priority: int}>
     */
    protected function candidatePlans(Organization $organization): Collection
    {
        $orgSpecific = InstallmentPlan::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->get()
            ->filter(fn (InstallmentPlan $plan) => $plan->isCurrentlyValid())
            ->map(fn (InstallmentPlan $plan) => [
                'plan' => $plan,
                'priority' => (int) $plan->priority,
            ]);

        $assigned = $organization->assignedInstallmentPlans()
            ->wherePivot('is_active', true)
            ->where('installment_plans.is_active', true)
            ->get()
            ->filter(fn (InstallmentPlan $plan) => $plan->isCurrentlyValid())
            ->map(fn (InstallmentPlan $plan) => [
                'plan' => $plan,
                'priority' => (int) ($plan->pivot->priority ?? $plan->priority),
            ]);

        return $orgSpecific
            ->concat($assigned)
            ->unique(fn (array $row) => $row['plan']->id)
            ->values();
    }
}
