<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin', 'permissions' => json_encode(['manage_users', 'manage_products', 'manage_sales', 'manage_purchases', 'view_reports'])],
            ['name' => 'Kasir', 'slug' => 'cashier', 'permissions' => json_encode(['create_sales', 'view_products', 'view_customers'])],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'permissions' => json_encode(['view_reports', 'approve_returns', 'view_inventory'])],
            ['name' => 'Gudang', 'slug' => 'warehouse', 'permissions' => json_encode(['manage_stock', 'receive_purchase', 'view_products'])],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
