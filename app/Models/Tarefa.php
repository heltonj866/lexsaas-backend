<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarefa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'processo_id',
        'titulo',
        'descricao',
        'data_vencimento',
        'status',
        'prioridade'
    ];

    // Converte automaticamente para objeto de data do PHP (Carbon)
    protected $casts = [
        'data_vencimento' => 'datetime',
    ];

    // Relacionamento: Uma tarefa PODE pertencer a um processo
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }
}