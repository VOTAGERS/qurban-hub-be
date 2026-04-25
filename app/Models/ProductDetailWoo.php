<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasAuditFields;

class ProductDetailWoo extends Model
{
    use HasFactory, HasAuditFields;

    protected $table = 'productsdetail_woo';

    protected $fillable = [
        'idproduct_woo',
        'country',
        'max_share',
        'status',
        'created_by',
        'updated_by',
    ];

    public function productWoo()
    {
        return $this->belongsTo(ProductWoo::class, 'idproduct_woo', 'id');
    }
}
