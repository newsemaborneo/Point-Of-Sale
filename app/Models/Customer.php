<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'address', 'member_type_id', 'member_code', 'loyalty_points', 'total_spend'
    ];

    public function memberType() { return $this->belongsTo(MemberType::class); }
    public function sales() { return $this->hasMany(Sale::class); }
    public function debts() { return $this->hasMany(CustomerDebt::class); }
    public function points() { return $this->hasMany(CustomerPoint::class); }

    /**
     * Evaluates total spend and upgrades the member level automatically if thresholds are met.
     */
    public function checkAndUpgradeLevel()
    {
        $this->total_spend = $this->sales()->where('status', 'completed')->sum('grand_total');
        
        $eligibleType = MemberType::orderByDesc('minimum_spend')
            ->where('minimum_spend', '<=', $this->total_spend)
            ->first();
            
        if ($eligibleType && $this->member_type_id !== $eligibleType->id) {
            $this->member_type_id = $eligibleType->id;
        }
        
        $this->save();
    }
}
