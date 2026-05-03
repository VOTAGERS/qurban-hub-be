<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasAuditFields;

class ProductWoo extends Model
{
    use HasFactory, HasAuditFields;
    protected $table = 'products_woo';

    protected $fillable = [
        'name',
        'price',
        'status',
        'created_by',
        'updated_by'
    ];

    public function productDetail()
    {
        return $this->hasOne(ProductDetailWoo::class, 'idproduct_woo', 'id');
    }
}