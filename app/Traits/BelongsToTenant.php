<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * O método "boot" de uma trait é executado automaticamente 
     * quando o Model é inicializado.
     */
    protected static function bootBelongsToTenant()
    {
        if (auth()->check()) {
            // 1. Filtra as buscas (O que já tínhamos feito)
            static::addGlobalScope('tenant_isolation', function (Builder $builder) {
                $builder->where('tenant_id', auth()->user()->tenant_id);
            });

            // 2. NOVA MÁGICA: Preenche o tenant_id automaticamente ao criar um registro
            static::creating(function ($model) {
                $model->tenant_id = auth()->user()->tenant_id;
            });
        }
    }

    /**
     * Define a relação com o Escritório.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}