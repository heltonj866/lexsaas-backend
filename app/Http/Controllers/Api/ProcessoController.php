<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Processo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProcessoController extends Controller
{
    /**
     * Listagem de Processos com Busca e Relacionamento
     */
    public function index(Request $request)
    {
        // O "with('cliente')" permite que o React acesse "processo.cliente.nome"
        $query = Processo::with('cliente');

        // Filtro de busca (pesquisa no título ou no número do processo)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('numero_processo', 'like', "%{$search}%");
            });
        }

        // Retorna os dados (ajustado para o padrão que o React espera)
        return response()->json([
            'data' => $query->latest()->get()
        ]);
    }

    /**
     * Criação de Novo Processo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'          => 'required|string|max:255',
            'numero_processo' => 'required|string|unique:processos,numero_processo',
            'cliente_id'      => 'required|exists:clientes,id',
            'status'          => 'required|string',
            'descricao'       => 'nullable|string',
        ], [
            // Mensagens personalizadas (opcional, mas deixa o sistema mais amigável)
            'numero_processo.unique' => 'Este número de processo já consta em nosso sistema.',
            'cliente_id.exists'      => 'O cliente selecionado é inválido.',
        ]);

        // O tenant_id costuma ser preenchido automaticamente pela Trait, 
        // mas se precisar manual: $validated['tenant_id'] = Auth::user()->tenant_id;

        $processo = Processo::create($validated);

        return response()->json($processo, 201);
    }

    /**
     * Atualização de Processo Existente
     */
    public function update(Request $request, Processo $processo)
    {
        $validated = $request->validate([
            'titulo'          => 'required|string|max:255',
            // AQUI ESTÁ O SEGREDO: unique:tabela,coluna,id_para_ignorar
            'numero_processo' => 'required|string|unique:processos,numero_processo,' . $processo->id,
            'cliente_id'      => 'required|exists:clientes,id',
            'status'          => 'required|string',
            'descricao'       => 'nullable|string',
        ]);

        $processo->update($validated);

        return response()->json($processo);
    }

    /**
     * Remoção (Soft Delete ou Permanent)
     */
    public function destroy(Processo $processo)
    {
        $processo->delete();

        return response()->json(null, 204);
    }

    /**
     * Consulta ao CNJ */
    public function consultarCnj($npu)
{
    //
    $npuLimpo = preg_replace('/[^0-9]/', '', $npu);

    if (strlen($npuLimpo) !== 20) {
        return response()->json(['erro' => 'NPU inválido. Deve conter 20 dígitos.'], 400);
    }

    $apiKey = env('CNJ_API_KEY', 'sua_chave_do_cnj_aqui'); //
    $urlCnj = "https://api-publica.datajud.cnj.jus.br/api_publica_tjpi/v1/_search"; // Focado no TJPI

    try {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
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
            return response()->json(['erro' => 'Não foi possível consultar o CNJ no momento. Verifique a API Key.'], 400);
        }

        $dados = $response->json();

        if (empty($dados['hits']['hits'])) {
            return response()->json(['erro' => 'Processo não encontrado no DataJud.'], 404);
        }

        $processoGov = $dados['hits']['hits'][0]['_source'];

        return response()->json([
            'numero_processo' => $processoGov['numeroProcesso'],
            'classe_judicial' => $processoGov['classe']['nome'] ?? 'Ação Judicial',
            'orgao_julgador'  => $processoGov['orgaoJulgador']['nome'] ?? 'Tribunal de Justiça',
            'status'          => 'Ativo'
        ]);

    } catch (\Exception $e) {
        return response()->json(['erro' => 'Erro interno ao consultar o CNJ: ' . $e->getMessage()], 500); //
    }
}
}