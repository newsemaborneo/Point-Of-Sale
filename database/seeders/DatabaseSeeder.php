<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\BranchesSeeder;
use Database\Seeders\CategoriesSeeder;
use Database\Seeders\CustomersSeeder;
use Database\Seeders\ProductsSeeder;
use Database\Seeders\ProductStocksSeeder;
use Database\Seeders\PromotionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\SuppliersSeeder;
use Database\Seeders\UnitsSeeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\VouchersSeeder;
use Database\Seeders\WarehousesSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            BranchesSeeder::class,
            WarehousesSeeder::class,
            UnitsSeeder::class,
            CategoriesSeeder::class,
            SuppliersSeeder::class,
            CustomersSeeder::class,
            ProductsSeeder::class,
            ProductStocksSeeder::class,
            PromotionsSeeder::class,
            VouchersSeeder::class,
            SettingsSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
