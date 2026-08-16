<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['mobile' => '09177886099'],
            [
                'email' => 'admin@pcshiraz.ir',
                'password' => 'password',
            ]
        );

        $this->call(IranDataSeeder::class);
        $this->call(DemoDataSeeder::class);
        $this->call(InstallmentPlanSeeder::class);
    }
}
