<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'opening_balance', 'closing_balance', 'expected_balance',
        'difference', 'opened_at', 'closed_at', 'status', 'note',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function sales() { return $this->hasMany(Sale::class); }
    public function movements() { return $this->hasMany(CashMovement::class); }
}
