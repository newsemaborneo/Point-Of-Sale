<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $fillable = ['code', 'warehouse_id', 'user_id', 'status', 'opname_date', 'note'];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(StockOpnameItem::class); }
}
