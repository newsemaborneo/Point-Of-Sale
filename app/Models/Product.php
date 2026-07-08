<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    // Pastikan atribut-atribut ini sesuai dengan kolom di tabel 'products' Anda
    protected $fillable = [
        'category_id', 'unit_id', 'supplier_id', 'name', 'sku', 'barcode',
        'description', 'photo', 'purchase_price', 'sale_price',
        'discount_type', 'discount_value', 'tax_percent', 'min_stock',
        'is_active', 'has_expiry', 'expired_date',
    ];

    // Accessor untuk mendapatkan URL foto atau emoji default
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return Storage::disk('public')->url($this->photo);
        }
        return '📦'; // Emoji default jika tidak ada foto
    }

    /**
     * Relasi ke stok produk.
     */
    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Menghitung total stok produk dari semua gudang.
     */
    public function totalStock(): int
    {
        return $this->stocks->sum('quantity');
    }

    /**
     * Menghitung total stok produk untuk gudang tertentu.
     *
     * @param int $warehouseId ID gudang yang ingin diperiksa stoknya.
     * @return int Total stok produk di gudang tersebut.
     */
    public function stockInWarehouse(int $warehouseId): int
    {
        return $this->stocks()->where('warehouse_id', $warehouseId)->sum('quantity');
    }

    /**
     * Memeriksa apakah stok produk rendah.
     */
    public function isLowStock(): bool
    {
        // Asumsi min_stock adalah batas stok rendah
        return $this->totalStock() <= $this->min_stock;
    }

    /**
     * Relasi ke kategori produk.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke satuan produk.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi ke supplier produk.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}