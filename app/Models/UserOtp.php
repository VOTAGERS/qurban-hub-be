<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasAuditFields;

class UserOtp extends Model
{
    use HasFactory, HasAuditFields;

    protected $table = 'user_otps';
    protected $primaryKey = 'id_user_otp';

    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
