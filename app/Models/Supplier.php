<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'code', 'phone', 'email', 'address', 'contact_person'];

    public function products() { return $this->hasMany(Product::class); }
    public function purchases() { return $this->hasMany(Purchase::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
    public function debts() { return $this->hasMany(SupplierDebt::class); }
}
