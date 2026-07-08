<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $units = [
            ['name' => 'Pcs', 'symbol' => 'pcs'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Kg', 'symbol' => 'kg'],
            ['name' => 'Liter', 'symbol' => 'l'],
            ['name' => 'Packet', 'symbol' => 'pkt'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['symbol' => $unit['symbol']], $unit);
        }
    }
}
