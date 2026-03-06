<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacao;

class NotificacaoController extends Controller
{
    // 1. Devolve as notificações do utilizador logado
    public function index(Request $request)
    {
        $notificacoes = Notificacao::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(30) // Traz só as últimas 30
            ->get()
            ->map(function ($notificacao) {
                return [
                    'id' => $notificacao->id,
                    'tipo' => $notificacao->tipo,
                    'titulo' => $notificacao->titulo,
                    'mensagem' => $notificacao->mensagem,
                    // O diffForHumans transforma a data em "Há 10 minutos", "Há 2 horas" automaticamente!
                    'tempo' => $notificacao->created_at->diffForHumans(),
                    'lida' => (bool) $notificacao->lida,
                ];
            });

        return response()->json($notificacoes);
    }

    // 2. Marca uma notificação específica como lida
    public function marcarComoLida($id, Request $request)
    {
        $notificacao = Notificacao::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
            
        $notificacao->update(['lida' => true]);

        return response()->json(['message' => 'Notificação lida']);
    }

    // 3. Marca todas as notificações do utilizador como lidas
    public function marcarTodasComoLidas(Request $request)
    {
        Notificacao::where('user_id', $request->user()->id)
            ->where('lida', false)
            ->update(['lida' => true]);

        return response()->json(['message' => 'Todas marcadas como lidas']);
    }
}
