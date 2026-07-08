<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = ['sale_return_id', 'product_id', 'quantity', 'price', 'subtotal'];

    public function saleReturn() { return $this->belongsTo(SaleReturn::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
