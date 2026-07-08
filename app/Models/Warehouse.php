<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['branch_id', 'name', 'address', 'is_main'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function productStocks()
    {
        return $this->hasMany(ProductStock::class);
    }
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
