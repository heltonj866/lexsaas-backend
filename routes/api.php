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

// Rota Pública
Route::post('/login', [AuthController::class, 'login']);
Route::get('/me', function (Request $request) {
        return $request->user();
    });

// Rota para logout (protegida)
Route::post('/logout', [AuthController::class, 'logout']);

// Todas as rotas protegidas em um único grupo
Route::middleware('auth:sanctum')->group(function () {
    
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Módulos Principais (apiResource cria: index, store, show, update, destroy)
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
    Route::get('/notificacoes', [NotificacaoController::class, 'index']);
    Route::put('/notificacoes/{id}/lida', [NotificacaoController::class, 'marcarComoLida']);
    Route::post('/notificacoes/ler-todas', [NotificacaoController::class, 'marcarTodasComoLidas']);
    
});