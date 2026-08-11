<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class IranDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provincesPath = database_path('data/iran/provinces.json');
        $citiesPath = database_path('data/iran/cities.json');

        if (! File::exists($provincesPath) || ! File::exists($citiesPath)) {
            $this->command?->error('Iran provinces/cities JSON files are missing.');

            return;
        }

        /** @var list<array{id: int, name: string}> $provinces */
        $provinces = json_decode(File::get($provincesPath), true, 512, JSON_THROW_ON_ERROR);

        /** @var list<array{name: string, province_id: int}> $cities */
        $cities = json_decode(File::get($citiesPath), true, 512, JSON_THROW_ON_ERROR);

        /** @var array<int, int> $provinceIdMap */
        $provinceIdMap = [];

        foreach ($provinces as $province) {
            $model = Province::query()->firstOrCreate(
                ['name' => $province['name']],
            );

            $provinceIdMap[(int) $province['id']] = $model->id;
        }

        $seen = [];

        foreach ($cities as $city) {
            $provinceId = $provinceIdMap[(int) $city['province_id']] ?? null;

            if ($provinceId === null) {
                continue;
            }

            $name = trim((string) $city['name']);

            if ($name === '') {
                continue;
            }

            $key = $provinceId.'|'.$name;

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            City::query()->firstOrCreate([
                'province_id' => $provinceId,
                'name' => $name,
            ]);
        }
    }
}
