<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductWoo extends Model
{
    protected $fillable = [
        'woo_id',
        'name',
        'price',
        'status'
    ];
}