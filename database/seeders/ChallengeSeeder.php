<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Challenge;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $category = fn (string $name) => Category::where('name', $name)->value('id');

        $challenges = [
            [
                'title' => 'Seminggu Kurangi Motor',
                'category_id' => $category('Transportasi'),
                'description' => 'Ganti perjalanan motor dengan jalan kaki, sepeda, atau transportasi umum selama seminggu.',
                'start_date' => today()->subDays(2),
                'end_date' => today()->addDays(4),
                'reduction_target' => 3,
                'reward_points' => 100,
            ],
            [
                'title' => 'Hemat Listrik Sepekan',
                'category_id' => $category('Listrik'),
                'description' => 'Matikan peralatan yang tidak dipakai dan kurangi pemakaian AC di malam hari.',
                'start_date' => today()->subDay(),
                'end_date' => today()->addDays(5),
                'reduction_target' => 4,
                'reward_points' => 75,
            ],
            [
                'title' => 'Sebulan Kurangi Daging Merah',
                'category_id' => $category('Makanan'),
                'description' => 'Ganti daging sapi dengan protein nabati seperti tahu dan tempe.',
                'start_date' => today()->addDays(3),
                'end_date' => today()->addDays(32),
                'reduction_target' => 15,
                'reward_points' => 250,
            ],
            [
                'title' => 'Pilah Sampah Dua Pekan',
                'category_id' => $category('Sampah'),
                'description' => 'Pisahkan sampah organik untuk dikompos agar sampah ke TPA berkurang.',
                'start_date' => today()->subDays(20),
                'end_date' => today()->subDays(7),
                'reduction_target' => 1,
                'reward_points' => 80,
            ],
        ];

        foreach ($challenges as $data) {
            Challenge::updateOrCreate(['title' => $data['title']], $data);
        }
    }
}
