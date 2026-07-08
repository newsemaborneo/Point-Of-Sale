<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = ['return_number', 'purchase_id', 'user_id', 'return_date', 'reason', 'total'];

    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(PurchaseReturnItem::class); }
}
