<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_order';

    protected $fillable = [
        'order_code',
        'id_user',
        'idproduct_woo',
        'quantity',
        'total_price',
        'payment_status',
        'qurban_status',
        'status',
        'created_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function productWoo()
    {
        return $this->belongsTo(ProductWoo::class, 'idproduct_woo');
    }

    public function participants()
    {
        return $this->hasMany(OrderParticipant::class, 'id_order');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'id_order');
    }

    public function execution()
    {
        return $this->hasOne(QurbanExecution::class, 'id_order');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'id_order');
    }

    public function billing()
    {
        return $this->hasOne(Billing::class, 'id_order');
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class, 'id_order');
    }
}
