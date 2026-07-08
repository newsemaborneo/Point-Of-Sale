<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $promotion = Promotion::updateOrCreate(
            ['name' => 'Diskon Musim Promo'],
            ['type' => 'percent_discount', 'value' => 15, 'start_date' => now()->subDays(10)->toDateString(), 'end_date' => now()->addDays(20)->toDateString(), 'is_active' => true]
        );

        $promotionProducts = Product::whereIn('sku', ['P001', 'P002', 'P005'])->pluck('id')->toArray();
        $promotion->products()->sync($promotionProducts);
    }
}
