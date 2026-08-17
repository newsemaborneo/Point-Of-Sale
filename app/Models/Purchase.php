<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Traits\ClearsAiCache;

class Purchase extends Model
{
    use ClearsAiCache;
    protected $fillable = [
        'invoice_number', 'purchase_order_id', 'supplier_id', 'warehouse_id', 'user_id',
        'purchase_date', 'total', 'paid_amount', 'payment_status', 'note',
    ];

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(PurchaseItem::class); }
    public function returns() { return $this->hasMany(PurchaseReturn::class); }
    public function debts() { return $this->hasMany(SupplierDebt::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}
