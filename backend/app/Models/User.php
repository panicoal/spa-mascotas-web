<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;
    use SoftDeletes;
    use HasUuids;

    protected $table = 'usuarios';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $appends = ['role_names'];
    public function getRoleNamesAttribute()
    {
        return $this->getRoleNames();
    }

    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'telefono',
        'ci',
        'google_id',
        'avatar_url',
        'is_active',
        'email_verified_at',
        'failed_login_attempts',
        'locked_until',
        'last_login',
        'last_activity_at',
        'password_change_required',
        'turno',

        'verification_token',
        'verification_expires_at',

        'two_factor_enabled',
        'two_factor_secret',
        'backup_codes',

    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'backup_codes',
        'verification_token',
        'password_reset_token'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login' => 'datetime',
            'last_activity_at' => 'datetime',
            'password' => 'hashed',
            'backup_codes' => 'array',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'password_change_required' => 'boolean',
            'failed_login_attempts' => 'integer',
            'verification_expires_at' => 'datetime',

        ];
    }
    public function pets()
    {
        return $this->hasMany(
            Pet::class,
            'cliente_id'
        );
    }
}
