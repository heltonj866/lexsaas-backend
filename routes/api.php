<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\Api\ProcessoController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HonorarioController;
use App\Http\Controllers\Api\TarefaController;
use App\Http\Controllers\Api\IAController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\Api\ConfiguracaoController;
use Illuminate\Http\Request;

// 👇 ROTAS PÚBLICAS 👇
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// 👇 ROTAS PROTEGIDAS (Apenas com sessão iniciada) 👇
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/configuracoes/equipe', [ConfiguracaoController::class, 'storeMembro']);
    Route::delete('/configuracoes/equipe/{id}', [ConfiguracaoController::class, 'destroyMembro']);
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Módulos Principais
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('processos', ProcessoController::class);
    Route::apiResource('financeiro', HonorarioController::class);
    Route::apiResource('tarefas', TarefaController::class);
    
    // Documentos
    Route::apiResource('documentos', DocumentoController::class)->except(['update', 'show']);
    Route::post('/processos/{processo}/documentos', [DocumentoController::class, 'store']);

    // Inteligência Artificial
    Route::post('/ia/resumir', [IAController::class, 'resumirProcesso']);

    // Consulta ao CNPJ
    Route::get('/processos/cnj/{npu}', [ProcessoController::class, 'consultarCnj']);

    // Perfil do Usuário
    Route::post('/profile', [App\Http\Controllers\API\ProfileController::class, 'update']);
    Route::post('/profile/password', [App\Http\Controllers\API\ProfileController::class, 'updatePassword']);

    // Notificações
    // Rotas de Notificações
    Route::get('/notificacoes', [NotificacaoController::class, 'index']);
    Route::put('/notificacoes/ler-todas', [NotificacaoController::class, 'marcarTodasComoLidas']); // Esta rota fixa tem que vir ANTES da rota com {id}
    Route::put('/notificacoes/{id}/ler', [NotificacaoController::class, 'marcarComoLida']);

    // Rotas de Configurações
    Route::get('/configuracoes', [ConfiguracaoController::class, 'index']);
    Route::put('/configuracoes', [ConfiguracaoController::class, 'update']);
});