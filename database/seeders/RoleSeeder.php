<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => Role::ADMIN], ['description' => 'Administrator pengelola data master dan pengguna']);
        Role::firstOrCreate(['name' => Role::USER], ['description' => 'Pengguna terdaftar']);
    }
}
