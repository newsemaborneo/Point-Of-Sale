<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductStocksSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $warehouseCentral = Warehouse::where('code', 'WH-CENTRAL')->first();
        $warehouseEast = Warehouse::where('code', 'WH-EAST')->first();

        $stockData = [
            ['sku' => 'P001', 'warehouse' => $warehouseCentral, 'quantity' => 120],
            ['sku' => 'P002', 'warehouse' => $warehouseCentral, 'quantity' => 80],
            ['sku' => 'P003', 'warehouse' => $warehouseEast, 'quantity' => 60],
            ['sku' => 'P004', 'warehouse' => $warehouseEast, 'quantity' => 40],
            ['sku' => 'P005', 'warehouse' => $warehouseCentral, 'quantity' => 90],
        ];

        foreach ($stockData as $stock) {
            $product = Product::where('sku', $stock['sku'])->first();
            if (!$product || !$stock['warehouse']) {
                continue;
            }

            ProductStock::updateOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $stock['warehouse']->id],
                ['quantity' => $stock['quantity']]
            );
        }
    }
}
