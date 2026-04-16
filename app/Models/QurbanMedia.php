<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QurbanMedia extends Model
{
    use HasFactory;

    protected $table = 'qurban_media';
    protected $primaryKey = 'id_media';

    protected $fillable = [
        'id_execution',
        'file_url',
        'type',
        'status',
        'created_by',
        'updated_by',
    ];

    public function execution()
    {
        return $this->belongsTo(QurbanExecution::class, 'id_execution');
    }
}
