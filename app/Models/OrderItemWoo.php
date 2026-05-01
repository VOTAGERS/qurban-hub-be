<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemWoo extends Model
{
    protected $table = 'order_items_woo';
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'qty',
        'price'
    ];

    public function order()
    {
        return $this->belongsTo(OrderWoo::class);
    }
}
