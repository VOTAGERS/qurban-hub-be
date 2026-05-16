<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Hashids\Hashids;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_payment';

    protected $fillable = [
        'id_order',
        'id_stripe',
        'payment_method',
        'amount',
        'payment_status',
        'paid_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['hash_id'];

    protected $casts = [
        'paid_at' => 'datetime',
    ];
    protected function hashId(): Attribute
    {
        return Attribute::make(
            get: fn () => (new Hashids(
                config('hashids.connections.alternative.salt'),
                12
            ))->encode($this->id_payment)
        );
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order');
    }
}