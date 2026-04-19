<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasAuditFields;

class Package extends Model
{
    use HasFactory, HasAuditFields;

    protected $primaryKey = 'id_package';

    protected $fillable = [
        'animal_type',
        'country',
        'price',
        'max_share',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_package');
    }
}
