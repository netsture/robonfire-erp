<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function getRoleAttribute($value)
    {
        return $value ? ucfirst($value) : $value;
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function returnableMaterials(): HasMany
    {
        return $this->hasMany(ReturnableMaterial::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Get counts of existing child records associated with this user.
     *
     * @return array<string, int>
     */
    public function getChildRelationsCount(): array
    {
        $counts = [
            'sale' => $this->sales()->count(),
            'purchase' => $this->purchases()->count(),
            'returnable material' => $this->returnableMaterials()->count(),
            'stock adjustment' => $this->stockAdjustments()->count(),
        ];

        return array_filter($counts, fn ($count) => $count > 0);
    }

    /**
     * Check if user has any child records.
     */
    public function hasChildRecords(): bool
    {
        return !empty($this->getChildRelationsCount());
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
