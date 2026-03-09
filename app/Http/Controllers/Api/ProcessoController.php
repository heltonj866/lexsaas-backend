<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Processo;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProcessoController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // SEGREDO SAAS: Sempre filtrar pelo tenant_id
        $query = Processo::with('cliente')->where('tenant_id', $tenantId);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('numero_processo', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // 1. Validação de Segurança: O cliente pertence a este escritório?
        $cliente = Cliente::where('tenant_id', $tenantId)->findOrFail($request->cliente_id);

        // 2. Validação dos dados que o React envia
        $validated = $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'titulo'          => 'required|string|max:255',
            'numero_processo' => 'required|string',
            'tipo_acao'       => 'nullable|string', // Campo novo do React
            'vara'            => 'nullable|string', // Campo novo do React
            'status'          => 'nullable|string',
        ]);

        $processo = Processo::create($validated);

        return response()->json($processo, 201);
    }

    public function update(Request $request, $id)
    {
        $tenantId = $request->user()->tenant_id;
        $processo = Processo::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'titulo'          => 'required|string|max:255',
            'numero_processo' => 'required|string',
            'tipo_acao'       => 'nullable|string',
            'vara'            => 'nullable|string',
            'status'          => 'nullable|string',
        ]);

        $processo->update($validated);

        return response()->json($processo);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        // 1. Trava do Estagiário: Impede a exclusão
        if ($user->role === 'estagiario') {
            return response()->json(['error' => 'Estagiários não têm permissão para excluir processos.'], 403);
        }

        // 2. Trava do SaaS (Multi-Tenant): Garante que o processo pertence ao escritório
        $processo = \App\Models\Processo::where('tenant_id', $user->tenant_id)->findOrFail($id);

        $processo->delete();

        return response()->json(null, 204);
    }

    public function consultarCnj($npu)
    {
        $npuLimpo = preg_replace('/[^0-9]/', '', $npu);

        if (strlen($npuLimpo) !== 20) {
            return response()->json(['erro' => 'NPU inválido. Deve conter 20 dígitos.'], 400);
        }

        $apiKey = env('CNJ_API_KEY', 'sua_chave_do_cnj_aqui');
        $urlCnj = "https://api-publica.datajud.cnj.jus.br/api_publica_tjpi/v1/_search";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'APIKey ' . $apiKey,
                'Content-Type'  => 'application/json'
            ])->post($urlCnj, [
                "query" => [
                    "match" => [
                        "numeroProcesso" => $npuLimpo
                    ]
                ]
            ]);

            if ($response->failed()) {
                return response()->json(['erro' => 'Não foi possível consultar o CNJ no momento.'], 400);
            }

            $dados = $response->json();

            if (empty($dados['hits']['hits'])) {
                return response()->json(['erro' => 'Processo não encontrado no DataJud.'], 404);
            }

            $processoGov = $dados['hits']['hits'][0]['_source'];

            return response()->json([
                'numero_processo' => $processoGov['numeroProcesso'],
                'classe'          => $processoGov['classe']['nome'] ?? 'Ação Judicial',
                'orgao'           => $processoGov['orgaoJulgador']['nome'] ?? 'Tribunal de Justiça',
            ]);

        } catch (\Exception $e) {
            return response()->json(['erro' => 'Erro interno ao consultar o CNJ'], 500);
        }
    }

    public function show($id)
    {
        $user = auth()->user();

        // Busca o processo e traz as relações vinculadas a ele
        $processo = \App\Models\Processo::with([
            'cliente', 
            'tarefas' => function($query) {
                $query->orderBy('data_vencimento', 'asc');
            },
            'documentos'
        ])
        ->where('tenant_id', $user->tenant_id)
        ->findOrFail($id);

        return response()->json($processo);
    }
}