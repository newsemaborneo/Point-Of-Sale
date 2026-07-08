<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => 'Serkom POS', 'group' => 'general'],
            ['key' => 'company_address', 'value' => 'Jl. Contoh No. 1, Jakarta', 'group' => 'general'],
            ['key' => 'company_phone', 'value' => '+62 21 9999 8888', 'group' => 'general'],
            ['key' => 'default_currency', 'value' => 'IDR', 'group' => 'general'],
            ['key' => 'receipt_footer', 'value' => 'Terima kasih telah berbelanja di Serkom POS.', 'group' => 'receipt'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
