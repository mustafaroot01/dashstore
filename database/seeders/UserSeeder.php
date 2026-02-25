<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['first_name' => 'علي',      'last_name' => 'حسن',    'phone' => '07701000001', 'gender' => 'male',   'address' => 'بعقوبة — حي الجهاد'],
            ['first_name' => 'فاطمة',    'last_name' => 'جاسم',   'phone' => '07701000002', 'gender' => 'female', 'address' => 'الخالص — شارع 40'],
            ['first_name' => 'محمد',     'last_name' => 'كريم',   'phone' => '07701000003', 'gender' => 'male',   'address' => 'بلدروز — المنطقة الصناعية'],
            ['first_name' => 'زينب',     'last_name' => 'علاوي',  'phone' => '07701000004', 'gender' => 'female', 'address' => 'المقدادية — حي النصر'],
            ['first_name' => 'أحمد',     'last_name' => 'صالح',   'phone' => '07701000005', 'gender' => 'male',   'address' => 'كنعان — القسم الثاني'],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['phone' => $u['phone']],
                array_merge($u, ['password' => Hash::make('12345678'), 'is_active' => true])
            );
        }
    }
}
