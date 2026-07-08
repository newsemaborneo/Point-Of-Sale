<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VouchersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $vouchers = [
            ['code' => 'DISC10', 'type' => 'percent', 'value' => 10, 'min_purchase' => 50000, 'max_discount' => 20000, 'quota' => 100, 'used_count' => 0, 'start_date' => now()->subDays(5)->toDateString(), 'end_date' => now()->addDays(30)->toDateString(), 'is_active' => true],
            ['code' => 'OFF25K', 'type' => 'nominal', 'value' => 25000, 'min_purchase' => 100000, 'max_discount' => null, 'quota' => 50, 'used_count' => 0, 'start_date' => now()->subDays(2)->toDateString(), 'end_date' => now()->addDays(15)->toDateString(), 'is_active' => true],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(['code' => $voucher['code']], $voucher);
        }
    }
}
