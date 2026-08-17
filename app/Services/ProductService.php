<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Membuat produk baru beserta sku, photo, dan activity log.
     */
    public function createProduct(array $data, ?UploadedFile $photo = null, ?int $userId = null): Product
    {
        $data['sku'] = $data['sku'] ?? 'SKU-' . Str::upper(Str::random(8));

        if ($photo) {
            $data['photo'] = $photo->store('products', 'public');
        }

        $product = Product::create($data);

        ActivityLog::create([
            'user_id'      => $userId,
            'module'       => 'Product',
            'action'       => 'create',
            'description'  => "Membuat produk {$product->name}",
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
            'new_data'     => $product->toArray(),
        ]);

        return $product;
    }

    /**
     * Memperbarui produk beserta photo dan activity log.
     */
    public function updateProduct(Product $product, array $data, ?UploadedFile $photo = null, ?int $userId = null): Product
    {
        $oldData = $product->toArray();

        if ($photo) {
            $data['photo'] = $photo->store('products', 'public');
        }

        $product->update($data);

        ActivityLog::create([
            'user_id'      => $userId,
            'module'       => 'Product',
            'action'       => 'update',
            'description'  => "Mengubah produk {$product->name}",
            'subject_type' => Product::class,
            'subject_id'   => $product->id,
            'old_data'     => $oldData,
            'new_data'     => $product->fresh()->toArray(),
        ]);

        return $product;
    }

    /**
     * Menghapus produk dan mencatat activity log.
     */
    public function deleteProduct(Product $product, ?int $userId = null): void
    {
        $productName = $product->name;
        $productId   = $product->id;

        $product->delete();

        ActivityLog::create([
            'user_id'      => $userId,
            'module'       => 'Product',
            'action'       => 'delete',
            'description'  => "Menghapus produk {$productName}",
            'subject_type' => Product::class,
            'subject_id'   => $productId,
        ]);
    }
}
