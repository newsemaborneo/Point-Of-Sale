<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $foodCategory = Category::where('slug', 'food')->first();
        $beverageCategory = Category::where('slug', 'beverage')->first();
        $householdCategory = Category::where('slug', 'household')->first();
        $electronicsCategory = Category::where('slug', 'electronics')->first();

        $pcsUnit = Unit::where('symbol', 'pcs')->first();
        $boxUnit = Unit::where('symbol', 'box')->first();
        $kgUnit = Unit::where('symbol', 'kg')->first();

        $supplier1 = Supplier::where('code', 'SUP-001')->first();
        $supplier2 = Supplier::where('code', 'SUP-002')->first();
        $supplier3 = Supplier::where('code', 'SUP-003')->first();

        $products = [
            ['category_id' => $foodCategory?->id, 'unit_id' => $pcsUnit?->id, 'supplier_id' => $supplier1?->id, 'name' => 'Indomie Goreng', 'sku' => 'P001', 'barcode' => '8999999000011', 'description' => 'Mie instan rasa ayam bawang.', 'purchase_price' => 2500, 'sale_price' => 3500, 'discount_type' => 'percent', 'discount_value' => 10, 'tax_percent' => 0, 'min_stock' => 50, 'is_active' => true],
            ['category_id' => $beverageCategory?->id, 'unit_id' => $boxUnit?->id, 'supplier_id' => $supplier2?->id, 'name' => 'Teh Botol', 'sku' => 'P002', 'barcode' => '8999999000028', 'description' => 'Minuman teh manis kemasan botol.', 'purchase_price' => 4000, 'sale_price' => 6000, 'discount_type' => 'nominal', 'discount_value' => 500, 'tax_percent' => 0, 'min_stock' => 20, 'is_active' => true],
            ['category_id' => $householdCategory?->id, 'unit_id' => $pcsUnit?->id, 'supplier_id' => $supplier2?->id, 'name' => 'Sabun Mandi', 'sku' => 'P003', 'barcode' => '8999999000035', 'description' => 'Sabun mandi wangi untuk keluarga.', 'purchase_price' => 2500, 'sale_price' => 4500, 'discount_type' => null, 'discount_value' => 0, 'tax_percent' => 0, 'min_stock' => 10, 'is_active' => true],
            ['category_id' => $electronicsCategory?->id, 'unit_id' => $pcsUnit?->id, 'supplier_id' => $supplier3?->id, 'name' => 'Batterai AA', 'sku' => 'P004', 'barcode' => '8999999000042', 'description' => 'Baterai alkaline ukuran AA.', 'purchase_price' => 5000, 'sale_price' => 9000, 'discount_type' => null, 'discount_value' => 0, 'tax_percent' => 0, 'min_stock' => 30, 'is_active' => true],
            ['category_id' => $foodCategory?->id, 'unit_id' => $kgUnit?->id, 'supplier_id' => $supplier1?->id, 'name' => 'Beras Premium', 'sku' => 'P005', 'barcode' => '8999999000059', 'description' => 'Beras premium kualitas terbaik.', 'purchase_price' => 12000, 'sale_price' => 15000, 'discount_type' => 'percent', 'discount_value' => 5, 'tax_percent' => 0, 'min_stock' => 15, 'is_active' => true],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
