<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Data master kategori dan faktor emisi untuk demo.
 *
 * Nilai faktor adalah contoh berbasis rujukan publik; pemutakhiran nilai dan
 * rujukannya menjadi lingkup PBI-07/PBI-08 (Modul Data Master Emisi).
 */
class CategorySeeder extends Seeder
{
    private const DEFRA = 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2023';

    private const OWID_FOOD = 'https://ourworldindata.org/environmental-impacts-of-food';

    private const OWID_GRID = 'https://ourworldindata.org/grapher/carbon-intensity-electricity';

    public function run(): void
    {
        $categories = [
            ['name' => 'Transportasi', 'icon' => '🚗', 'color' => '#2563eb', 'base_unit' => 'km', 'factors' => [
                ['Sepeda motor bensin', 'km', 0.1137, self::DEFRA],
                ['Mobil bensin', 'km', 0.1642, self::DEFRA],
                ['Bus kota', 'km', 0.1023, self::DEFRA],
                ['Kereta komuter (KRL)', 'km', 0.0355, self::DEFRA],
            ]],
            ['name' => 'Listrik', 'icon' => '⚡', 'color' => '#ca8a04', 'base_unit' => 'kWh', 'factors' => [
                ['Listrik jaringan PLN', 'kWh', 0.6810, self::OWID_GRID],
            ]],
            ['name' => 'Makanan', 'icon' => '🍽️', 'color' => '#16a34a', 'base_unit' => 'porsi', 'factors' => [
                ['Daging sapi (porsi 100 g)', 'porsi', 9.9480, self::OWID_FOOD],
                ['Daging ayam (porsi 100 g)', 'porsi', 0.9870, self::OWID_FOOD],
                ['Nasi (porsi 100 g)', 'porsi', 0.4450, self::OWID_FOOD],
                ['Tahu/tempe (porsi 100 g)', 'porsi', 0.3160, self::OWID_FOOD],
                ['Sayuran (porsi 100 g)', 'porsi', 0.0530, self::OWID_FOOD],
            ]],
            ['name' => 'Sampah', 'icon' => '🗑️', 'color' => '#78716c', 'base_unit' => 'kg', 'factors' => [
                ['Sampah campuran ke TPA', 'kg', 0.4970, self::DEFRA],
                ['Sampah organik dikompos', 'kg', 0.0089, self::DEFRA],
            ]],
        ];

        foreach ($categories as $data) {
            $category = Category::updateOrCreate(
                ['name' => $data['name']],
                ['icon' => $data['icon'], 'color' => $data['color'], 'base_unit' => $data['base_unit'], 'is_active' => true]
            );

            foreach ($data['factors'] as [$name, $unit, $value, $url]) {
                $category->emissionFactors()->updateOrCreate(
                    ['name' => $name],
                    ['unit' => $unit, 'factor_value' => $value, 'reference_url' => $url, 'is_active' => true]
                );
            }
        }
    }
}
