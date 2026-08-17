<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['role_id', 'branch_id', 'name', 'email', 'phone', 'password', 'is_active'];
    protected $hidden = ['password', 'remember_token'];


    //relasi antar database 
    public function role() { return $this->belongsTo(Role::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function sales() { return $this->hasMany(Sale::class); }
    public function cashRegisters() { return $this->hasMany(CashRegister::class); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }

    //relasi user dengan role (satu user punya satu role)
    
    public function hasRole(string $slug): bool
    {
        return $this->role && $this->role->slug === $slug;
    }
}
