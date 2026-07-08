<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomerPoint extends Model
{
    protected $fillable = ['customer_id', 'sale_id', 'type', 'points', 'note'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function sale() { return $this->belongsTo(Sale::class); }
}
