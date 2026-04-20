<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItemWoo;

class OrderWoo extends Model
{
    protected $fillable = [
        'woo_id',
        'user_id',
        'email',
        'total',
        'status',
        'raw_payload'
    ];

    protected $casts = [
        'raw_payload' => 'array'
    ];

    public function items()
    {
        return $this->hasMany(OrderItemWoo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
