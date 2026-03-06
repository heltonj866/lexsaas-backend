<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        // O Laravel injeta automaticamente: WHERE tenant_id = user_logado->tenant_id
        $clientes = Cliente::all();
        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'cpf_cnpj' => 'required|string',
        ]);

        // Graças ao método bootBelongsToTenant (creating), 
        // o tenant_id é preenchido automaticamente!
        $cliente = Cliente::create($request->all());

        return response()->json($cliente, 201);
    }

    public function show($id)
    {
        $tenantId = auth()->user()->tenant_id;

        // Busca o cliente garantindo que é do escritório logado
        $cliente = \App\Models\Cliente::where('tenant_id', $tenantId)
            ->where('id', $id)
            // Aqui é onde a mágica do relacionamento acontece:
            ->with([
                'processos', 
                'documentos'
            ])
            ->firstOrFail();

        // Se você tiver URLs nos documentos, precisamos formatá-las
        if ($cliente->documentos) {
            $cliente->documentos->each(function ($doc) {
                $doc->url = \Illuminate\Support\Facades\Storage::url($doc->caminho_arquivo);
            });
        }

        return response()->json($cliente);
    }
}
