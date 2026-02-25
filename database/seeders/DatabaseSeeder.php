<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingSeeder::class,
            DistrictSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
