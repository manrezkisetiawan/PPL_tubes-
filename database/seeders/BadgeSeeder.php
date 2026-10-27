<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['Langkah Pertama', 'Mencatat aktivitas pertama di CarbonTrace.', 'activities_logged', 1],
            ['Pencatat Rajin', 'Konsisten mencatat hingga 50 aktivitas.', 'activities_logged', 50],
            ['Konsisten 7 Hari', 'Mencatat aktivitas tujuh hari berturut-turut.', 'streak', 7],
            ['Penantang Pemula', 'Menyelesaikan tantangan pertama.', 'challenges_completed', 1],
            ['Pejuang Iklim', 'Menyelesaikan lima tantangan pengurangan emisi.', 'challenges_completed', 5],
            ['Kolektor EcoPoint', 'Mengumpulkan 500 EcoPoint.', 'points', 500],
        ];

        foreach ($badges as [$name, $description, $type, $value]) {
            Badge::updateOrCreate(
                ['name' => $name],
                ['description' => $description, 'type' => $type, 'required_value' => $value, 'is_active' => true]
            );
        }
    }
}
