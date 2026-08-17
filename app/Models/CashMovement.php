<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ClearsAiCache;

class CashMovement extends Model
{
    use ClearsAiCache;
    protected $fillable = ['cash_register_id', 'user_id', 'type', 'amount', 'category', 'description'];

    public function cashRegister() { return $this->belongsTo(CashRegister::class); }
    public function user() { return $this->belongsTo(User::class); }
}
