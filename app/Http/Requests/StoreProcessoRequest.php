<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcessoRequest extends FormRequest
{
    /**
     * Determina se o usuário tem permissão para fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true; // Por enquanto, permitimos qualquer usuário logado
    }

    /**
     * Define as regras de validação.
     */
    public function rules(): array
    {
        return [
            'titulo' => 'required|string|min:5|max:255',
            'descricao' => 'nullable|string',
            'status' => 'required|in:ativo,suspenso,concluido',
            
            // Validação do número do processo (deve ser único na tabela)
            'numero_processo' => 'required|string|unique:processos,numero_processo',
            
            // Validação do Cliente: 
            // 1. Deve existir na tabela clientes
            // 2. Deve pertencer ao MESMO tenant do usuário logado (segurança extra!)
            'cliente_id' => [
                'required',
                Rule::exists('clientes', 'id')->where(function ($query) {
                    $query->where('tenant_id', auth()->user()->tenant_id);
                }),
            ],
        ];
    }

    public function messages(): array
{
    return [
        'titulo.required' => 'O título do processo é obrigatório.',
        'titulo.min' => 'O título deve ter pelo menos 5 caracteres.',
        'numero_processo.required' => 'O número do processo judicial é indispensável.',
        'numero_processo.unique' => 'Este número de processo já consta em nosso sistema.',
        'status.in' => 'O status selecionado é inválido. Escolha entre: Ativo, Suspenso ou Concluído.',
        'cliente_id.required' => 'É necessário selecionar um cliente válido.',
        'cliente_id.exists' => 'O cliente selecionado não foi encontrado ou não pertence ao seu escritório.',
    ];
}
}