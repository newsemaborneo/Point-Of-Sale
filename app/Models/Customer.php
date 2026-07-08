<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'address', 'member_type', 'member_code', 'loyalty_points'
    ];

    public function sales() { return $this->hasMany(Sale::class); }
    public function debts() { return $this->hasMany(CustomerDebt::class); }
    public function points() { return $this->hasMany(CustomerPoint::class); }
}
