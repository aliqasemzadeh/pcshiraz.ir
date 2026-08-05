<?php

namespace App\Services\Sale\Catalog;

use App\Models\Item;
use Illuminate\Support\Collection;

class ItemColorService
{
    /**
     * @return Collection<int, Item>
     */
    public function distinctColors(): Collection
    {
        return Item::query()
            ->whereNotNull('color_name')
            ->where('color_name', '!=', '')
            ->select('color_name', 'color_code')
            ->distinct()
            ->orderBy('color_name')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public function optionsForSelect(): array
    {
        $options = ['' => __('general.select_existing_color')];

        foreach ($this->distinctColors() as $row) {
            $name = (string) $row->color_name;
            $code = is_string($row->color_code) && $row->color_code !== '' ? $row->color_code : null;
            $key = $name.'|'.($code ?? '');
            $label = $name;

            if ($code !== null) {
                $label .= ' ('.$code.')';
            }

            $options[$key] = $label;
        }

        return $options;
    }

    /**
     * @return array{name: string, code: ?string}|null
     */
    public function resolveByName(?string $colorName): ?array
    {
        if ($colorName === null || trim($colorName) === '') {
            return null;
        }

        $normalized = mb_strtolower(trim($colorName));

        foreach ($this->distinctColors() as $row) {
            if (mb_strtolower(trim((string) $row->color_name)) === $normalized) {
                $code = is_string($row->color_code) && $row->color_code !== ''
                    ? $row->color_code
                    : null;

                return [
                    'name' => (string) $row->color_name,
                    'code' => $code,
                ];
            }
        }

        return null;
    }

    /**
     * @return array{name: string, code: ?string}|null
     */
    public function parseSelectKey(?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        $parts = explode('|', $key, 2);
        $name = $parts[0] ?? '';

        if ($name === '') {
            return null;
        }

        $code = ($parts[1] ?? '') !== '' ? $parts[1] : null;

        return [
            'name' => $name,
            'code' => $code,
        ];
    }
}
