<?php

namespace App\Livewire\Concerns;

use App\Models\City;
use App\Models\Province;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;

trait InteractsWithIranLocations
{
    /**
     * @return array<int|string, string>
     */
    #[Computed]
    public function provinces(): array
    {
        /** @var array<int, string> $options */
        $options = Cache::remember('iran.provinces.options', 60 * 60 * 24, function () {
            return Province::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        });

        return ['' => __('app.select_province')] + $options;
    }

    /**
     * @return array<int|string, string>
     */
    #[Computed]
    public function cities(): array
    {
        if (blank($this->form->province_id)) {
            return ['' => __('app.select_city')];
        }

        $options = City::query()
            ->where('province_id', $this->form->province_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        return ['' => __('app.select_city')] + $options;
    }

    public function updatedFormProvinceId(mixed $value): void
    {
        $this->form->city_id = null;
        unset($this->cities);
    }
}
