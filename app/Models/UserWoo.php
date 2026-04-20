<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWoo extends Model
{
    protected $fillable = [
        'woo_customer_id',
        'name',
        'email'
    ];

    public function orders()
    {
        return $this->hasMany(OrderWoo::class);
    }
}
