<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = ['return_number', 'sale_id', 'user_id', 'return_date', 'reason', 'total', 'refund_method'];

    public function sale() { return $this->belongsTo(Sale::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(SaleReturnItem::class); }
}
