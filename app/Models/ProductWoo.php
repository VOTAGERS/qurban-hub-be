<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductWoo extends Model
{
    protected $table = 'products_woo';

    protected $fillable = [
        'name',
        'price',
        'status'
    ];
}