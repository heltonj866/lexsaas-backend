<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarefa;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $tarefas = Tarefa::with('processo.cliente')
            ->where('tenant_id', $tenantId)
            ->orderBy('data_vencimento', 'asc')
            ->get();

        return response()->json(['data' => $tarefas]);
    }

    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'titulo'          => 'required|string|max:255',
            'descricao'       => 'nullable|string',
            'data_vencimento' => 'required|date',
            'status'          => 'required|in:pendente,em_andamento,concluido',
            'prioridade'      => 'required|in:baixa,media,alta,urgente',
            'processo_id'     => 'nullable|exists:processos,id'
        ]);

        $validated['tenant_id'] = $tenantId;
        
        // TRATAMENTO: Converte string vazia do React em NULL para o banco de dados
        $validated['processo_id'] = $request->processo_id ?: null;

        $tarefa = Tarefa::create($validated);
        $tarefa->load('processo.cliente');

        return response()->json($tarefa, 201);
    }

    public function update(Request $request, $id)
    {
        $tenantId = $request->user()->tenant_id;
        
        // Busca Segura: Garante que a tarefa pertence ao escritório antes de atualizar
        $tarefa = Tarefa::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'titulo'          => 'required|string|max:255',
            'descricao'       => 'nullable|string',
            'data_vencimento' => 'required|date',
            'status'          => 'required|in:pendente,em_andamento,concluido',
            'prioridade'      => 'required|in:baixa,media,alta,urgente',
            'processo_id'     => 'nullable|exists:processos,id'
        ]);

        $validated['processo_id'] = $request->processo_id ?: null;

        $tarefa->update($validated);
        $tarefa->load('processo.cliente');

        return response()->json($tarefa);
    }

    public function destroy(Tarefa $tarefa)
    {
        $user = auth()->user();

        // 1. Trava do Estagiário
        if ($user->role === 'estagiario') {
            return response()->json(['error' => 'Estagiários não têm permissão para excluir registros.'], 403);
        }

        // 2. Trava de segurança SaaS
        if ($tarefa->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $tarefa->delete();

        return response()->json(null, 204);
    }
}