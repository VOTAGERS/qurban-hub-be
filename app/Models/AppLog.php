<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppLog extends Model
{
    use HasFactory;

    protected $table = 'apps_log';
    protected $primaryKey = 'id_apps_log';

    protected $fillable = [
        'data_capture',
        'message',
        'status',
        'created_by',
        'updated_by',
    ];
}
