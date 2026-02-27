<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_firstname',
        'user_middlename',
        'user_lastname',
        'user_suffix',
        'user_tupid',
        'user_email',
        'user_password',
        'user_type',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'user_password' => 'hashed',
        ];
    }

    /**
     * Get the email field for authentication.
     */
    public function getEmailForPasswordReset()
    {
        return $this->user_email;
    }

    /**
     * Get the password field for authentication.
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles_tbl', 'user_id_fk', 'role_id_fk');
    }

    public function userDepartment()
    {
        return $this->hasMany(UserDepartment::class, 'user_roles_tbl', 'user_id_fk');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'user_departments_tbl', 'user_id_fk', 'department_id_fk');
    }
}
