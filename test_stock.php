<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::where('is_active', true)->with('stocks')->take(3)->get();
foreach ($products as $p) {
    echo "Product: {$p->name}, Stock: {$p->totalStock()}, Price: {$p->sale_price}\n";
}
echo "Total active products: " . \App\Models\Product::where('is_active', true)->count() . "\n";
