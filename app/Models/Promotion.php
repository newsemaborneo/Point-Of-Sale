<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'name', 'type', 'value', 'buy_qty', 'get_qty', 'start_time', 'end_time',
        'start_date', 'end_date', 'is_active',
    ];

    public function products() { return $this->belongsToMany(Product::class, 'promotion_products'); }
    public function bundleProducts() { return $this->hasMany(ProductBundle::class); }
}
