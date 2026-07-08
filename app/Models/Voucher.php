<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_purchase', 'max_discount', 'quota',
        'used_count', 'start_date', 'end_date', 'is_active',
    ];

    public function sales() { return $this->hasMany(Sale::class); }

    public function isValid(float $subtotal): bool
    {
        if (!$this->is_active) return false;
        if ($this->quota !== null && $this->used_count >= $this->quota) return false;
        if ($subtotal < $this->min_purchase) return false;
        $today = now()->toDateString();
        if ($this->start_date && $today < $this->start_date) return false;
        if ($this->end_date && $today > $this->end_date) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = $this->type === 'percent' ? $subtotal * ($this->value / 100) : $this->value;
        if ($this->max_discount) {
            $discount = min($discount, $this->max_discount);
        }
        return $discount;
    }
}
