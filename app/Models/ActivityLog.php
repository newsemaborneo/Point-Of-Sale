<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'module', 'action', 'description', 'subject_type', 'subject_id',
        'old_data', 'new_data', 'ip_address',
    ];
    protected $casts = ['old_data' => 'array', 'new_data' => 'array'];

    public function user() { return $this->belongsTo(User::class); }
}
