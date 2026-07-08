<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SupplierDebt extends Model
{
    protected $fillable = ['supplier_id', 'purchase_id', 'amount', 'paid_amount', 'due_date', 'status'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function purchase() { return $this->belongsTo(Purchase::class); }
}
