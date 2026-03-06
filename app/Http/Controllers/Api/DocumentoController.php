<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Notificacao;

class DocumentoController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        // Traz os documentos com os dados do cliente e do processo vinculado
        $documentos = Documento::with(['cliente', 'processo'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get();

        // Adiciona a URL completa do arquivo para o React conseguir criar o botão de "Baixar"
        $documentos->each(function ($doc) {
            $doc->url = Storage::url($doc->caminho_arquivo);
        });

        return response()->json(['data' => $documentos]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'processo_id' => 'nullable|exists:processos,id',
            'titulo'      => 'required|string|max:255',
            // Validação de segurança: apenas estes formatos e tamanho máximo de 10MB (10240 KB)
            'arquivo'     => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $file = $request->file('arquivo');

        // Salva o arquivo em uma pasta separada por escritório (Tenant) para organização
        $path = $file->store("documentos/{$tenantId}", 'public');

        $documento = Documento::create([
            'tenant_id'       => $tenantId,
            'cliente_id'      => $request->cliente_id,
            'processo_id'     => $request->processo_id,
            'titulo'          => $request->titulo,
            'caminho_arquivo' => $path,
            'extensao'        => $file->getClientOriginalExtension(),
            'tamanho_kb'      => round($file->getSize() / 1024), // Converte bytes para KB
        ]);

        $documento->load(['cliente', 'processo']);
        $documento->url = Storage::url($documento->caminho_arquivo);

        Notificacao::create([
            'user_id' => $request->user()->id, // Aqui estamos a avisar o próprio utilizador logado para testar
            'tipo' => 'documento',
            'titulo' => 'Novo ficheiro no Cofre',
            'mensagem' => "Você guardou o documento '{$request->titulo}' com sucesso.",
            'lida' => false
        ]);

        return response()->json(['message' => 'Documento salvo com sucesso!']);

        return response()->json($documento, 201);
    }

    public function destroy(Documento $documento)
    {
        // Trava de segurança SaaS
        if ($documento->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        // Deleta o arquivo físico do servidor primeiro
        if (Storage::disk('public')->exists($documento->caminho_arquivo)) {
            Storage::disk('public')->delete($documento->caminho_arquivo);
        }

        // Depois deleta o registro no banco de dados
        $documento->delete();

        return response()->json(null, 204);
    }
}