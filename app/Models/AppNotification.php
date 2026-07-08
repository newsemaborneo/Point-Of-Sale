<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'message', 'reference_type', 'reference_id', 'is_read'];

    public function user() { return $this->belongsTo(User::class); }
}
