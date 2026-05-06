<?php

namespace App\Models;

use Laravel\Cashier\Billable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    protected $primaryKey = 'id_user';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'company',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'country',
        'country_code',
        'email',
        'phone',
        'password',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_user');
    }

    public function roles()
    {
        return $this->belongsToMany(RoleAccess::class, 'user_roles', 'id_user', 'role_code', 'id_user', 'role_code');
    }
}
