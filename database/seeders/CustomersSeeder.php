<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $customers = [
            ['name' => 'Sari Toko', 'phone' => '+62 812 3456 7890', 'email' => 'sari@example.com', 'address' => 'Jl. Kios Makmur No. 12, Jakarta', 'member_type' => 'gold', 'member_code' => 'CUST-001', 'loyalty_points' => 250],
            ['name' => 'Agus Retail', 'phone' => '+62 813 5555 6666', 'email' => 'agus@example.com', 'address' => 'Jl. Niaga 5, Depok', 'member_type' => 'silver', 'member_code' => 'CUST-002', 'loyalty_points' => 120],
            ['name' => 'Lestari Mart', 'phone' => '+62 814 7777 8888', 'email' => 'lestari@example.com', 'address' => 'Jl. Pasar 1, Bogor', 'member_type' => 'regular', 'member_code' => 'CUST-003', 'loyalty_points' => 30],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['member_code' => $customer['member_code']], $customer);
        }
    }
}
