<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Models\Scopes\TenantScope;

trait BelongsToTenant
{
    /**
     * Boot the trait to apply the global scope and set tenant_id on create.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->tenant_id !== null) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
