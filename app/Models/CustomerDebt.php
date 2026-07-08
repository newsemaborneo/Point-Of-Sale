<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomerDebt extends Model
{
    protected $fillable = ['customer_id', 'sale_id', 'amount', 'paid_amount', 'due_date', 'status'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function sale() { return $this->belongsTo(Sale::class); }
    public function payments() { return $this->hasMany(CustomerDebtPayment::class); }
}
