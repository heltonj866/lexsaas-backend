<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    // 1. Desativa o auto-incremento (já que você está usando UUID)
    public $incrementing = false;

    // 2. Avisa que a chave primária é uma string
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nome_escritorio',
        'cnpj_nif',
        'ativo',
    ];

    use HasFactory;
}
