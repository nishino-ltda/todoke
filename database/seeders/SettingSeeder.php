<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'site_name' => 'TODOKE',
            'maintenance_mode' => 'false',
            'allow_registration' => 'true',
            'delivery_fee_base' => '5.00',
            'delivery_fee_per_km' => '1.50',
            'support_email' => 'support@todoke.com',
            'contact_phone' => '+55 11 99999-9999'
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
