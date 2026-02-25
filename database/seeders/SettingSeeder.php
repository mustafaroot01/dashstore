<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'otp_channel'     => 'whatsapp',
            'invoice_name'    => 'أمواج ديالى',
            'invoice_phone'   => '07700000000',
            'invoice_address' => 'ديالى — بعقوبة',
            'invoice_footer'  => 'شكراً لثقتكم بنا',
            'privacy_policy'  => 'سياسة الخصوصية لأمواج ديالى...',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
