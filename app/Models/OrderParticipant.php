<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderParticipant extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_participant';

    protected $fillable = [
        'id_order',
        'qurban_name',
        'email',
        'phone_number',
        'remarks',
        'status',
        'created_by',
        'updated_by',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'id_participant');
    }
}
