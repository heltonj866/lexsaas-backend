<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Notificacao;

class DocumentoController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $documentos = Documento::with(['cliente', 'processo'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->get();

        $documentos->each(function ($doc) {
            $doc->url = Storage::url($doc->caminho_arquivo);
        });

        return response()->json(['data' => $documentos]);
    }

    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // 1. Validação de segurança SaaS: Garante que o cliente é do seu escritório
        $cliente = Cliente::where('tenant_id', $tenantId)->findOrFail($request->cliente_id);

        $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'processo_id' => 'nullable|exists:processos,id',
            'titulo'      => 'required|string|max:255',
            'arquivo'     => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('arquivo');
        $path = $file->store("documentos/{$tenantId}", 'public');

        $documento = Documento::create([
            'tenant_id'       => $tenantId,
            'cliente_id'      => $request->cliente_id,
            'processo_id'     => $request->processo_id,
            'titulo'          => $request->titulo,
            'caminho_arquivo' => $path,
            'extensao'        => $file->getClientOriginalExtension(),
            'tamanho_kb'      => round($file->getSize() / 1024),
        ]);

        // Se a tabela de notificações existir, ele cria o alerta
        if (class_exists(Notificacao::class)) {
            Notificacao::create([
                'user_id'  => $request->user()->id,
                'tipo'     => 'documento',
                'titulo'   => 'Novo ficheiro no Cofre',
                'mensagem' => "Você guardou o documento '{$request->titulo}' com sucesso.",
                'lida'     => false
            ]);
        }

        $documento->url = Storage::url($documento->caminho_arquivo);

        return response()->json($documento, 201);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        // 1. Trava do Estagiário: Impede a exclusão do ficheiro
        if ($user->role === 'estagiario') {
            return response()->json(['error' => 'Estagiários não têm permissão para apagar documentos do cofre.'], 403);
        }

        $documento = \App\Models\Documento::findOrFail($id);

        // 2. Trava do SaaS (Multi-Tenant): Verifica se o dono do documento pertence ao escritório atual
        $cliente = \App\Models\Cliente::findOrFail($documento->cliente_id);
        if ($cliente->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Acesso negado ao documento.'], 403);
        }

        // 3. Apaga o ficheiro físico do servidor (se existir)
        if ($documento->caminho_arquivo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($documento->caminho_arquivo);
        }

        // 4. Apaga o registo no banco de dados
        $documento->delete();

        return response()->json(null, 204);
    }
}