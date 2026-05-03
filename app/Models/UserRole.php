<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use App\Traits\HasAuditFields;

class UserRole extends Model
{
    use HasFactory, HasAuditFields;

    protected $table = 'user_roles';
    protected $primaryKey = 'id_user_role';

    protected $fillable = [
        'id_user',
        'id_role_access',
        'status',
        'created_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function role()
    {
        return $this->belongsTo(RoleAccess::class, 'id_role_access');
    }
}
