<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QurbanExecution extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_execution';

    protected $fillable = [
        'id_order',
        'execution_date',
        'notes',
        'execution_status',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'execution_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function media()
    {
        return $this->hasMany(QurbanMedia::class, 'id_execution');
    }
}
