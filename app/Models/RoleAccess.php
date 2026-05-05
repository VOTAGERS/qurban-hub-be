<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use App\Traits\HasAuditFields;

class RoleAccess extends Model
{
    use HasFactory, HasAuditFields;

    protected $table = 'role_accesses';
    protected $primaryKey = 'id_role_access';

    protected $fillable = [
        'role_name',
        'role_code',
        'status',
        'created_by',
        'updated_by',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_code', 'id_user', 'role_code', 'id_user');
    }
}
