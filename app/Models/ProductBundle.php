<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductBundle extends Model
{
    protected $fillable = ['promotion_id', 'bundle_product_id', 'quantity'];

    public function promotion() { return $this->belongsTo(Promotion::class); }
    public function product() { return $this->belongsTo(Product::class, 'bundle_product_id'); }
}
