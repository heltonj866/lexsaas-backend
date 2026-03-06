<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Processo;
use App\Models\Documento;
use App\Models\Tarefa;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $totalClientes = Cliente::where('tenant_id', $tenantId)->count();
        
        $processosAtivos = Processo::where('tenant_id', $tenantId)
                                   ->where('status', 'Ativo')
                                   ->count();
                                   
        $documentos = Documento::where('tenant_id', $tenantId)->count();

        // Conta a quantidade para o Card
        $tarefasPendentes = Tarefa::where('tenant_id', $tenantId)
                                  ->whereIn('status', ['pendente', 'em_andamento'])
                                  ->count();

        // A MÁGICA PARA O CALENDÁRIO: Busca as tarefas reais
        $proximasTarefas = Tarefa::with('processo.cliente')
                                 ->where('tenant_id', $tenantId)
                                 ->whereIn('status', ['pendente', 'em_andamento'])
                                 ->orderBy('data_vencimento', 'asc')
                                 ->get();

        return response()->json([
            'totalClientes' => $totalClientes,
            'processosAtivos' => $processosAtivos,
            'documentos' => $documentos,
            'tarefasPendentes' => $tarefasPendentes,
            'tarefas' => $proximasTarefas // Nova lista enviada ao React
        ]);
    }
}