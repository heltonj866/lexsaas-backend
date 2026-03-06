<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Honorario extends Model
{
    use HasFactory, SoftDeletes; //

    protected $fillable = [
        'tenant_id',
        'processo_id',
        'cliente_id', // Adicionado para unificação
        'valor',
        'tipo', // Adicionado (receita/despesa)
        'categoria', // Adicionado
        'data_vencimento',
        'data_pagamento',
        'status',
        'descricao'
    ]; //

    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}