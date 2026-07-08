<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $suppliers = [
            ['name' => 'PT Sumber Makmur', 'code' => 'SUP-001', 'phone' => '+62 21 1111 2222', 'email' => 'info@sumbermakmur.co.id', 'address' => 'Jl. Industri 10, Jakarta', 'contact_person' => 'Arif'],
            ['name' => 'CV Bahan Segar', 'code' => 'SUP-002', 'phone' => '+62 21 3333 4444', 'email' => 'sales@bahansegar.id', 'address' => 'Jl. Perdagangan 7, Bekasi', 'contact_person' => 'Rina'],
            ['name' => 'PT Elektronik Nusantara', 'code' => 'SUP-003', 'phone' => '+62 21 5555 6666', 'email' => 'support@elektronik.co.id', 'address' => 'Jl. Elektronika 21, Tangerang', 'contact_person' => 'Tomi'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['code' => $supplier['code']], $supplier);
        }
    }
}
