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
        'id_fileupload',
        'created_by',
        'updated_by'
    ];

    public function productDetail()
    {
        return $this->hasOne(ProductDetailWoo::class, 'idproduct_woo', 'id');
    }

    public function fileUpload()
    {
        return $this->belongsTo(FileUpload::class, 'id_fileupload', 'id');
    }
}