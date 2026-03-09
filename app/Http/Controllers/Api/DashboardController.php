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

        // 1. Total de Clientes do Escritório
        $totalClientes = Cliente::where('tenant_id', $tenantId)->count();

        // 2. Total de Processos Ativos
        $processosAtivos = Processo::where('tenant_id', $tenantId)
            ->where('status', 'Ativo')
            ->count();

        // 3. Contagem de Documentos no Cofre
        // (Assumindo que os documentos estão vinculados aos clientes do Tenant)
        $documentos = Documento::whereHas('cliente', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })->count();

        // 4. Contagem de Tarefas Pendentes/Em Andamento
        $tarefasPendentes = Tarefa::where('tenant_id', $tenantId)
            ->whereIn('status', ['pendente', 'em_andamento'])
            ->count();

        // 5. Lista das próximas Tarefas/Prazos (Ordenadas por vencimento)
        $tarefasLista = Tarefa::with('processo')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pendente', 'em_andamento'])
            ->orderBy('data_vencimento', 'asc')
            ->get();

        return response()->json([
            'totalClientes' => $totalClientes,
            'processosAtivos' => $processosAtivos,
            'documentos' => $documentos,
            'tarefasPendentes' => $tarefasPendentes,
            'tarefas' => $tarefasLista // Isto alimenta o Calendário e a Barra Lateral
        ]);
    }
}