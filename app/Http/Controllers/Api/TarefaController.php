<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarefa;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function index()
    {
        // Pega o UUID do escritório do usuário logado
        $tenantId = auth()->user()->tenant_id;

        // Traz as tarefas do escritório ordenadas pela data de vencimento (as mais urgentes primeiro)
        // e já carrega os dados do processo e do cliente para o React não ter que fazer requisições extras
        $tarefas = Tarefa::with('processo.cliente')
            ->where('tenant_id', $tenantId)
            ->orderBy('data_vencimento', 'asc')
            ->get();

        return response()->json(['data' => $tarefas]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'          => 'required|string|max:255',
            'descricao'       => 'nullable|string',
            'data_vencimento' => 'required|date',
            'status'          => 'required|in:pendente,em_andamento,concluido',
            'prioridade'      => 'required|in:baixa,media,alta,urgente',
            'processo_id'     => 'nullable|exists:processos,id'
        ]);

        // Injeta a identidade do escritório automaticamente
        $validated['tenant_id'] = auth()->user()->tenant_id;

        $tarefa = Tarefa::create($validated);

        // Carrega o relacionamento recém-criado para devolver ao Frontend completo
        $tarefa->load('processo.cliente');

        return response()->json($tarefa, 201);
    }

    public function update(Request $request, Tarefa $tarefa)
    {
        // Trava de segurança SaaS: Garante que a tarefa pertence a este escritório
        if ($tarefa->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $validated = $request->validate([
            'titulo'          => 'required|string|max:255',
            'descricao'       => 'nullable|string',
            'data_vencimento' => 'required|date',
            'status'          => 'required|in:pendente,em_andamento,concluido',
            'prioridade'      => 'required|in:baixa,media,alta,urgente',
            'processo_id'     => 'nullable|exists:processos,id'
        ]);

        $tarefa->update($validated);
        $tarefa->load('processo.cliente');

        return response()->json($tarefa);
    }

    public function destroy(Tarefa $tarefa)
    {
        // Trava de segurança SaaS
        if ($tarefa->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        // SoftDelete: Apenas marca como deletado no banco de dados
        $tarefa->delete();

        return response()->json(null, 204);
    }
}