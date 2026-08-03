<?php

namespace App\Livewire\Forms;

use App\Models\InstallmentPlan;
use Livewire\Form;

class InstallmentPlanForm extends Form
{
    public ?InstallmentPlan $installmentPlan = null;

    public string $title = '';

    public ?int $organization_id = null;

    public int $term_months = 10;

    public string $down_payment_percent = '0';

    public string $monthly_interest_percent = '0';

    public ?string $max_financiable_amount = null;

    public ?string $down_payment_required_above = null;

    public string $min_down_payment_percent = '0';

    public ?string $min_order_amount = null;

    public int $priority = 0;

    public bool $is_active = true;

    public function setInstallmentPlan(InstallmentPlan $plan): void
    {
        $this->installmentPlan = $plan;
        $this->title = $plan->title;
        $this->organization_id = $plan->organization_id;
        $this->term_months = $plan->term_months;
        $this->down_payment_percent = (string) $plan->down_payment_percent;
        $this->monthly_interest_percent = (string) $plan->monthly_interest_percent;
        $this->max_financiable_amount = $plan->max_financiable_amount !== null ? (string) $plan->max_financiable_amount : null;
        $this->down_payment_required_above = $plan->down_payment_required_above !== null ? (string) $plan->down_payment_required_above : null;
        $this->min_down_payment_percent = (string) $plan->min_down_payment_percent;
        $this->min_order_amount = $plan->min_order_amount !== null ? (string) $plan->min_order_amount : null;
        $this->priority = $plan->priority;
        $this->is_active = $plan->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'term_months' => ['required', 'integer', 'min:1', 'max:120'],
            'down_payment_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'monthly_interest_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_financiable_amount' => ['nullable', 'numeric', 'min:0'],
            'down_payment_required_above' => ['nullable', 'numeric', 'min:0'],
            'min_down_payment_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['required', 'integer'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'title' => __('general.title'),
            'organization_id' => __('general.organization_entity'),
            'term_months' => __('general.term_months'),
            'down_payment_percent' => __('general.down_payment_percent'),
            'monthly_interest_percent' => __('general.monthly_interest_percent'),
            'max_financiable_amount' => __('general.max_financiable_amount'),
            'down_payment_required_above' => __('general.down_payment_required_above'),
            'min_down_payment_percent' => __('general.min_down_payment_percent'),
            'min_order_amount' => __('general.min_order_amount'),
            'priority' => __('general.priority'),
            'is_active' => __('general.active'),
        ];
    }

    public function store(): InstallmentPlan
    {
        $this->validate();

        return InstallmentPlan::query()->create($this->payload());
    }

    public function update(InstallmentPlan $plan): InstallmentPlan
    {
        $this->installmentPlan = $plan;
        $this->validate();

        $plan->update($this->payload());

        return $plan->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return [
            'title' => $this->title,
            'organization_id' => $this->organization_id ?: null,
            'term_months' => $this->term_months,
            'down_payment_percent' => $this->down_payment_percent,
            'monthly_interest_percent' => $this->monthly_interest_percent,
            'max_financiable_amount' => $this->blankToNull($this->max_financiable_amount),
            'down_payment_required_above' => $this->blankToNull($this->down_payment_required_above),
            'min_down_payment_percent' => $this->min_down_payment_percent,
            'min_order_amount' => $this->blankToNull($this->min_order_amount),
            'priority' => $this->priority,
            'is_active' => $this->is_active,
        ];
    }

    protected function blankToNull(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return $value;
    }
}
