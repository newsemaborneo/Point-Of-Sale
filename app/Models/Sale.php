<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number', 'customer_id', 'warehouse_id', 'branch_id', 'user_id', 'cash_register_id',
        'voucher_id', 'subtotal', 'discount_total', 'tax_total', 'grand_total', 'paid_amount',
        'change_amount', 'points_earned', 'status', 'note',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function cashRegister() { return $this->belongsTo(CashRegister::class); }
    public function voucher() { return $this->belongsTo(Voucher::class); }
    public function items() { return $this->hasMany(SaleItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function returns() { return $this->hasMany(SaleReturn::class); }
}
