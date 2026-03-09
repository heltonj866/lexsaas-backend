<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    // 👇 1. Listagem com Paginação e Busca 👇
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $busca = $request->query('busca');

        // Começa a query garantindo que só vê os clientes do seu escritório
        $query = Cliente::where('tenant_id', $tenantId);

        // Se o React enviou um termo de busca, filtra por nome ou CPF/CNPJ
        if ($busca) {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'LIKE', "%{$busca}%")
                  ->orWhere('cpf_cnpj', 'LIKE', "%{$busca}%");
            });
        }

        // O React espera 10 itens por página
        $clientes = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($clientes);
    }

    // 👇 2. Criação (Mantido com a sua lógica de boot) 👇
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'cpf_cnpj' => 'required|string',
        ]);

        // Graças ao método bootBelongsToTenant (creating) no Model, 
        // o tenant_id é preenchido automaticamente!
        $cliente = Cliente::create($request->all());

        return response()->json($cliente, 201);
    }

    // 👇 3. Exibição Detalhada (Mantido o seu excelente código) 👇
    public function show($id)
    {
        $tenantId = auth()->user()->tenant_id;

        // Busca o cliente garantindo que é do escritório logado
        $cliente = Cliente::where('tenant_id', $tenantId)
            ->where('id', $id)
            // Aqui é onde a mágica do relacionamento acontece:
            ->with(['processos', 'documentos'])
            ->firstOrFail();

        // Se tiver URLs nos documentos, formatamos
        if ($cliente->documentos) {
            $cliente->documentos->each(function ($doc) {
                $doc->url = \Illuminate\Support\Facades\Storage::url($doc->caminho_arquivo);
            });
        }

        return response()->json($cliente);
    }

    // 👇 4. NOVO: Atualizar (PUT) 👇
    public function update(Request $request, $id)
    {
        $tenantId = $request->user()->tenant_id;

        // Encontra o cliente garantindo que pertence ao escritório
        $cliente = Cliente::where('tenant_id', $tenantId)->findOrFail($id);
        
        // Atualiza os dados com o que veio do React
        $cliente->update($request->all());

        return response()->json(['message' => 'Cliente atualizado com sucesso', 'cliente' => $cliente]);
    }

    // 👇 5. NOVO: Excluir (DELETE) 👇
    public function destroy($id)
    {
        $user = auth()->user();

        // 1. Trava do Estagiário: Impede a exclusão
        if ($user->role === 'estagiario') {
            return response()->json(['error' => 'Estagiários não têm permissão para excluir clientes do sistema.'], 403);
        }

        // 2. Trava do SaaS (Multi-Tenant): Garante que o cliente pertence ao escritório
        $cliente = \App\Models\Cliente::where('tenant_id', $user->tenant_id)->findOrFail($id);

        $cliente->delete();

        return response()->json(null, 204);
    }
}