<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant; // <-- Importante

class Cliente extends Model
{
    use HasFactory, BelongsToTenant; // <-- Aplica a trait

    protected $fillable = [
        'tenant_id',
        'nome',
        'cpf_cnpj',
        'telefone',
        'email',
        'cep',         
        'endereco',    
        'bairro',      
        'cidade'
    ];

    protected $hidden = [
    'tenant_id',
    'password',      // No caso do User
    'remember_token', // No caso do User
];

// Relacionamento: Um cliente tem muitos processos
    public function processos()
    {
        return $this->hasMany(Processo::class);
    }

    // Relacionamento: Um cliente tem muitos documentos (GED)
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
}