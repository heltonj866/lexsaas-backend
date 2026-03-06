<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Honorario; //
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HonorarioController extends Controller 
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            // Unificado: Agora usamos apenas Honorario para tudo
            $query = Honorario::where('tenant_id', $user->tenant_id);

            if ($request->has('search')) {
                $query->where('descricao', 'like', '%' . $request->search . '%');
            }

            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            $lancamentos = $query->with(['processo.cliente', 'cliente'])
                                 ->orderBy('data_vencimento', 'desc')
                                 ->paginate(15);

            // Cálculos de resumo
            $totalReceitas = Honorario::where('tenant_id', $user->tenant_id)
                ->where('tipo', 'receita')
                ->where('status', '!=', 'cancelado')
                ->sum('valor');

            $totalDespesas = Honorario::where('tenant_id', $user->tenant_id)
                ->where('tipo', 'despesa')
                ->where('status', '!=', 'cancelado')
                ->sum('valor');

            return response()->json([
                'resumo' => [
                    'receitas' => (float) $totalReceitas,
                    'despesas' => (float) $totalDespesas,
                    'saldo' => (float) ($totalReceitas - $totalDespesas),
                ],
                'lancamentos' => $lancamentos
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'descricao' => 'required|string|max:255',
        'valor' => 'required|numeric|min:0',
        'tipo' => 'required|in:receita,despesa',
        'categoria' => 'nullable|string',
        'data_vencimento' => 'required|date',
        'cliente_id' => 'nullable',
        'processo_id' => 'nullable', // Permitimos nulo na validação
    ]);

    $data['tenant_id'] = Auth::user()->tenant_id;
    $data['status'] = 'pendente';
    
    // TRATAMENTO IMPORTANTE: Converte string vazia em NULL
    $data['processo_id'] = $request->processo_id ?: null;
    $data['cliente_id'] = $request->cliente_id ?: null;

    $registro = Honorario::create($data);

    return response()->json($registro, 201);
}

// Atualização de honorário
public function update(Request $request, $id)
{
    $user = Auth::user();
    $registro = Honorario::where('tenant_id', $user->tenant_id)->findOrFail($id);

    $data = $request->validate([
        'descricao' => 'required|string|max:255',
        'valor' => 'required|numeric',
        'tipo' => 'required|in:receita,despesa',
        'categoria' => 'nullable|string',
        'data_vencimento' => 'required|date',
        'status' => 'required|in:pendente,pago,atrasado,cancelado',
        'cliente_id' => 'nullable|exists:clientes,id',
        'processo_id' => 'nullable|exists:processos,id',
    ]);

    // Tratamento para garantir que campos vazios sejam gravados como null
    $data['processo_id'] = $request->processo_id ?: null;
    $data['cliente_id'] = $request->cliente_id ?: null;

    $registro->update($data);

    return response()->json($registro);
}

}