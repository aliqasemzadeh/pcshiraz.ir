<?php

namespace Database\Seeders;

use App\Models\InstallmentPlan;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class InstallmentPlanSeeder extends Seeder
{
    /**
     * Amount-tiered installment plans: 3 order ranges × 3 terms.
     */
    public function run(): void
    {
        $tiers = [
            [
                'label' => 'تا ۱۰۰ میلیون',
                'min_order_amount' => '0',
                'max_order_amount' => '100000000',
                'down_payment_percent' => '10',
                'priority_base' => 30,
            ],
            [
                'label' => '۱۰۰ تا ۲۰۰ میلیون',
                'min_order_amount' => '100000000.0001',
                'max_order_amount' => '200000000',
                'down_payment_percent' => '20',
                'priority_base' => 20,
            ],
            [
                'label' => '۲۰۰ تا ۳۰۰ میلیون',
                'min_order_amount' => '200000000.0001',
                'max_order_amount' => '300000000',
                'down_payment_percent' => '30',
                'priority_base' => 10,
            ],
        ];

        $terms = [
            ['term_months' => 2, 'monthly_interest_percent' => '2', 'priority_offset' => 3],
            ['term_months' => 6, 'monthly_interest_percent' => '2.5', 'priority_offset' => 2],
            ['term_months' => 18, 'monthly_interest_percent' => '3', 'priority_offset' => 1],
        ];

        $sync = [];

        foreach ($tiers as $tier) {
            foreach ($terms as $term) {
                $title = sprintf('%s — %d ماه', $tier['label'], $term['term_months']);
                $priority = $tier['priority_base'] + $term['priority_offset'];

                $plan = InstallmentPlan::query()->updateOrCreate(
                    [
                        'title' => $title,
                        'organization_id' => null,
                    ],
                    [
                        'term_months' => $term['term_months'],
                        'down_payment_percent' => $tier['down_payment_percent'],
                        'monthly_interest_percent' => $term['monthly_interest_percent'],
                        'max_financiable_amount' => null,
                        'down_payment_required_above' => null,
                        'min_down_payment_percent' => 0,
                        'min_order_amount' => $tier['min_order_amount'],
                        'max_order_amount' => $tier['max_order_amount'],
                        'priority' => $priority,
                        'is_active' => true,
                        'starts_at' => null,
                        'ends_at' => null,
                    ],
                );

                $sync[$plan->id] = [
                    'is_default' => false,
                    'is_active' => true,
                    'priority' => $priority,
                ];
            }
        }

        Organization::query()
            ->where('is_active', true)
            ->each(function (Organization $organization) use ($sync): void {
                $organization->assignedInstallmentPlans()->syncWithoutDetaching($sync);
            });
    }
}
