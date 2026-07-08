<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'phone', 'is_active'];

    public function users() { return $this->hasMany(User::class); }
    public function warehouses() { return $this->hasMany(Warehouse::class); }
    public function sales() { return $this->hasMany(Sale::class); }
}
