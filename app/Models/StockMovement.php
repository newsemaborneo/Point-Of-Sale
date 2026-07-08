<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'warehouse_id', 'destination_warehouse_id', 'type', 'quantity',
        'quantity_before', 'quantity_after', 'reference_type', 'reference_id', 'note', 'user_id',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function destinationWarehouse() { return $this->belongsTo(Warehouse::class, 'destination_warehouse_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
