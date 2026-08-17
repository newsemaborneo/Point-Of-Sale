<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberType extends Model
{
    protected $fillable = [
        'name',
        'discount_percentage',
        'minimum_spend',
        'description'
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
