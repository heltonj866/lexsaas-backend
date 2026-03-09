<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant; // A nossa proteção automática
use Illuminate\Database\Eloquent\SoftDeletes;

class Processo extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 
        'cliente_id', 
        'numero_processo', 
        'titulo', 
        'descricao', 
        'status'
    ];

    protected $hidden = [
        'tenant_id',
        'password',       // No caso do User
        'remember_token', // No caso do User
    ];

    // Relacionamento: Um processo pertence a um cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // 👇 NOVAS RELAÇÕES ADICIONADAS AQUI 👇

    /**
     * Relacionamento: Um processo tem várias tarefas/prazos
     */
    public function tarefas()
    {
        return $this->hasMany(Tarefa::class);
    }

    /**
     * Relacionamento: Um processo tem vários documentos anexados
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    // 👆 FIM DAS NOVAS RELAÇÕES 👆

    /**
     * Escopo para filtrar processos dinamicamente.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('titulo', 'like', '%'.$search.'%')
                      ->orWhere('numero_processo', 'like', '%'.$search.'%')
                      ->orWhere('descricao', 'like', '%'.$search.'%');
            });
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['cliente_id'] ?? null, function ($query, $clienteId) {
            $query->where('cliente_id', $clienteId);
        });
    }
}