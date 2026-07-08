<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['po_number', 'supplier_id', 'warehouse_id', 'user_id', 'order_date', 'expected_date', 'status', 'total', 'note'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }
    public function purchases() { return $this->hasMany(Purchase::class); }
}
