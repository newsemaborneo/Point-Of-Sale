<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            ['name' => 'Food', 'slug' => 'food'],
            ['name' => 'Beverage', 'slug' => 'beverage'],
            ['name' => 'Household', 'slug' => 'household'],
            ['name' => 'Electronics', 'slug' => 'electronics'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
