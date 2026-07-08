<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $cashierRole = Role::where('slug', 'cashier')->first();
        $warehouseManagerRole = Role::where('slug', 'warehouse')->first();
        $centralBranch = Branch::where('code', 'BR-CENTRAL')->first();

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin Sistem', 'role_id' => $adminRole?->id, 'branch_id' => $centralBranch?->id, 'email' => 'admin@example.com', 'phone' => '+62 811 0000 0000', 'password' => Hash::make('password'), 'is_active' => true]
        );

        User::updateOrCreate(
            ['email' => 'kasir@example.com'],
            ['name' => 'Kasir Utama', 'role_id' => $cashierRole?->id, 'branch_id' => $centralBranch?->id, 'email' => 'kasir@example.com', 'phone' => '+62 811 1111 1111', 'password' => Hash::make('password'), 'is_active' => true]
        );

        User::updateOrCreate(
            ['email' => 'gudang@example.com'],
            ['name' => 'Gudang Utama', 'role_id' => $warehouseManagerRole?->id, 'branch_id' => $centralBranch?->id, 'email' => 'gudang@example.com', 'phone' => '+62 811 2222 2222', 'password' => Hash::make('password'), 'is_active' => true]
        );
    }
}
