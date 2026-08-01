<?php

namespace App\Support\PowerGrid;

use PowerComponents\LivewirePowerGrid\Themes\Tailwind;

/**
 * PowerGrid theme aligned with Flowbite Blade Data Display tables
 * (x-fwb.table / .head / .row / .cell) — striped + hoverable by default.
 *
 * @see https://github.com/themesberg/flowbite-laravel-components#data-display
 * @see vendor/themesberg/flowbite-laravel-components/src/View/Components/Table
 */
class FlowbiteTheme extends Tailwind
{
    public string $name = 'tailwind';

    public function table(): array
    {
        return [
            'layout' => [
                'base' => 'align-middle inline-block min-w-full w-full',
                'div' => 'relative overflow-x-auto rounded-base',
                'table' => 'w-full text-sm text-left rtl:text-right text-body',
                'container' => 'w-full',
                'actions' => 'flex items-center gap-2',
            ],

            'header' => [
                'thead' => 'text-sm text-body bg-neutral-secondary-soft border-b border-default',
                'tr' => '',
                'th' => 'px-6 py-3 font-medium text-heading whitespace-nowrap',
                'thAction' => 'px-6 py-3 font-medium text-heading whitespace-nowrap',
            ],

            'body' => [
                'tbody' => '',
                'tbodyEmpty' => '',
                // Matches x-fwb.table.row with striped + hoverable
                'tr' => 'border-b border-default odd:bg-neutral-primary even:bg-neutral-secondary-soft hover:bg-neutral-secondary-medium',
                'td' => 'px-6 py-4 whitespace-nowrap text-body',
                'tdEmpty' => 'px-6 py-4 whitespace-nowrap text-body',
                'tdSummarize' => 'px-6 py-4 whitespace-nowrap text-sm text-body text-right space-y-2',
                'trSummarize' => 'border-b border-default bg-neutral-primary',
                'tdFilters' => '',
                'trFilters' => 'border-b border-default bg-neutral-secondary-soft',
                'tdActionsContainer' => 'flex items-center gap-2',
            ],
        ];
    }

    public function footer(): array
    {
        return [
            'view' => $this->root().'.footer',
            'select' => 'block w-auto rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 text-sm text-heading focus:border-brand focus:ring-brand-medium dark:bg-neutral-secondary-soft',
            'footer' => 'border-t border-default bg-neutral-primary',
            'footer_with_pagination' => 'md:flex md:flex-row w-full items-center py-3 bg-neutral-primary overflow-x-auto px-2 relative',
        ];
    }

    public function cols(): array
    {
        return [
            'div' => 'select-none flex items-center gap-1',
        ];
    }

    public function editable(): array
    {
        return [
            'view' => $this->root().'.editable',
            'input' => 'block w-full rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 text-sm text-heading focus:border-brand focus:ring-brand-medium',
        ];
    }

    public function checkbox(): array
    {
        return [
            'th' => 'px-6 py-3 font-medium text-heading',
            'base' => '',
            'label' => 'flex items-center gap-2',
            'input' => 'w-4 h-4 text-brand bg-neutral-secondary-soft border-default-medium rounded-base focus:ring-brand-medium focus:ring-2',
        ];
    }

    public function radio(): array
    {
        return [
            'th' => 'px-6 py-3 font-medium text-heading',
            'base' => '',
            'label' => 'flex items-center gap-2',
            'input' => 'w-4 h-4 text-brand bg-neutral-secondary-soft border-default-medium focus:ring-brand-medium focus:ring-2',
        ];
    }

    public function filterBoolean(): array
    {
        return [
            'view' => $this->root().'.filters.boolean',
            'base' => 'min-w-[5rem]',
            'select' => 'block w-full rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 text-sm text-heading focus:border-brand focus:ring-brand-medium',
        ];
    }

    public function filterDatePicker(): array
    {
        return [
            'base' => '',
            'view' => $this->root().'.filters.date-picker',
            'input' => 'flatpickr flatpickr-input block w-auto rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 text-sm text-heading focus:border-brand focus:ring-brand-medium',
        ];
    }

    public function filterMultiSelect(): array
    {
        return [
            'view' => $this->root().'.filters.multi-select',
            'base' => 'inline-block relative w-full',
            'select' => 'mt-1',
        ];
    }

    public function filterNumber(): array
    {
        return [
            'view' => $this->root().'.filters.number',
            'input' => 'w-full min-w-[5rem] block rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 text-sm text-heading focus:border-brand focus:ring-brand-medium',
        ];
    }

    public function filterSelect(): array
    {
        return [
            'view' => $this->root().'.filters.select',
            'base' => '',
            'select' => 'block w-full rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 text-sm text-heading focus:border-brand focus:ring-brand-medium',
        ];
    }

    public function filterInputText(): array
    {
        return [
            'view' => $this->root().'.filters.input-text',
            'base' => 'min-w-[9.5rem]',
            'select' => 'block w-full rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 text-sm text-heading focus:border-brand focus:ring-brand-medium',
            'input' => 'block w-full rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 text-sm text-heading focus:border-brand focus:ring-brand-medium',
        ];
    }

    public function searchBox(): array
    {
        return [
            'input' => 'block w-full rounded-base border border-default-medium bg-neutral-secondary-soft p-2.5 ps-10 text-sm text-heading placeholder:text-body focus:border-brand focus:ring-brand-medium',
            'iconClose' => 'text-body',
            'iconSearch' => 'text-body w-5 h-5',
        ];
    }
}
