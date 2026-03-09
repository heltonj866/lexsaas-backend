<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Honorario; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceiroController extends Controller
{
    public function __construct()
    {
        // Trava absoluta: Só o Admin passa daqui!
        $this->middleware(function ($request, $next) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['error' => 'Acesso restrito. Apenas administradores podem ver o financeiro.'], 403);
            }
            return $next($request);
        });
    }}

class HonorarioController extends Controller 
{
    
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Busca apenas os lançamentos do escritório atual
            $query = Honorario::where('tenant_id', $user->tenant_id);

            // Traz os relacionamentos para o React mostrar o nome do Cliente e o NPU do Processo
            $lancamentos = $query->with(['processo', 'cliente'])
                                 ->orderBy('data_vencimento', 'desc')
                                 ->paginate(15);

            // O Laravel Pagination já embrulha o resultado num objeto 'data', 
            // que é exatamente o que o nosso Financeiro.jsx espera (res.data.data)
            return response()->json($lancamentos);

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
            'status' => 'nullable|in:pendente,pago,atrasado,cancelado',
            'cliente_id' => 'nullable',
            'processo_id' => 'nullable', 
        ]);

        $data['tenant_id'] = $request->user()->tenant_id;
        $data['status'] = $request->status ?: 'pendente';
        
        // TRATAMENTO IMPORTANTE: Converte string vazia em NULL
        $data['processo_id'] = $request->processo_id ?: null;
        $data['cliente_id'] = $request->cliente_id ?: null;

        $registro = Honorario::create($data);

        return response()->json($registro, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
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

        $data['processo_id'] = $request->processo_id ?: null;
        $data['cliente_id'] = $request->cliente_id ?: null;

        $registro->update($data);

        return response()->json($registro);
    }

    // 👇 NOVA FUNÇÃO: Para o botão de Excluir do React 👇
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        // Garante que só apaga se for do próprio escritório
        $registro = Honorario::where('tenant_id', $user->tenant_id)->findOrFail($id);
        
        $registro->delete();

        return response()->json(['message' => 'Lançamento excluído com sucesso'], 204);
    }

}