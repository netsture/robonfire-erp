<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['firm_id', 'name', 'email', 'password', 'phone', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function isSuperAdmin()
    {
        return strtolower($this->role ?? '') === 'superadmin' || $this->roles()->where('slug', 'superadmin')->exists();
    }

    public function isAdmin()
    {
        return strtolower($this->role ?? '') === 'admin' || $this->hasRole('admin');
    }

    public function isUser()
    {
        return strtolower($this->role ?? '') === 'user' || $this->hasRole('user');
    }

    public function hasRole($roleSlug)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function hasPermission($permissionSlug)
    {
        if ($this->isSuperAdmin()) {
            if (str_starts_with($permissionSlug, 'firms.')) {
                return true;
            }
            if (str_contains($permissionSlug, '.view') || str_contains($permissionSlug, '.index') || str_contains($permissionSlug, '.show') || str_contains($permissionSlug, '.print') || str_contains($permissionSlug, '.report')) {
                return true;
            }
            return false;
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }
}
