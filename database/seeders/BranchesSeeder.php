<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $branches = [
            ['name' => 'Central Branch', 'code' => 'BR-CENTRAL', 'address' => 'Jl. Raya Utama No. 1, Jakarta', 'phone' => '+62 21 1234 5678', 'is_active' => true],
            ['name' => 'East Branch', 'code' => 'BR-EAST', 'address' => 'Jl. Timur Raya No. 4, Jakarta', 'phone' => '+62 21 8765 4321', 'is_active' => true],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['code' => $branch['code']], $branch);
        }
    }
}
