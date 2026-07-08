<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehousesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $centralBranch = Branch::where('code', 'BR-CENTRAL')->first();
        $eastBranch = Branch::where('code', 'BR-EAST')->first();

        $warehouses = [
            ['branch_id' => $centralBranch?->id, 'name' => 'Central Warehouse', 'code' => 'WH-CENTRAL', 'address' => 'Gudang Utama, Central Branch', 'is_main' => true],
            ['branch_id' => $eastBranch?->id, 'name' => 'East Warehouse', 'code' => 'WH-EAST', 'address' => 'Gudang Timur, East Branch', 'is_main' => false],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::updateOrCreate(['code' => $warehouse['code']], $warehouse);
        }
    }
}
