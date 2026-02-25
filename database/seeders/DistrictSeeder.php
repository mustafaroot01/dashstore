<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            'بعقوبة', 'الخالص', 'بلدروز', 'المقدادية', 'الصويرة',
            'كنعان', 'العزيزية', 'الشعلة', 'الوجيهية', 'قره تبه',
        ];

        foreach ($districts as $name) {
            District::firstOrCreate(['name' => $name]);
        }
    }
}
