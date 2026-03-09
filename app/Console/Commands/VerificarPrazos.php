<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tarefa;
use App\Models\Notificacao;
use App\Models\User;
use Carbon\Carbon;

class VerificarPrazos extends Command
{
    // O nome do comando que vamos rodar
    protected $signature = 'prazos:verificar';
    protected $description = 'Verifica prazos próximos a vencer e gera notificações';

    public function handle()
    {
        // Pega as datas de hoje e de daqui a 2 dias (48 horas)
        $hoje = Carbon::now();
        $daquiA48Horas = Carbon::now()->addDays(2);

        // Procura tarefas que NÃO estão concluídas e vencem neste intervalo
        $tarefasUrgentes = Tarefa::where('status', '!=', 'concluido')
            ->whereBetween('data_vencimento', [$hoje, $daquiA48Horas])
            ->get();

        $contador = 0;

        foreach ($tarefasUrgentes as $tarefa) {
            // Encontra os utilizadores que pertencem ao mesmo escritório (tenant_id) desta tarefa
            $usuariosDoEscritorio = User::where('tenant_id', $tarefa->tenant_id)->get();

            foreach ($usuariosDoEscritorio as $user) {
                // Evita criar a mesma notificação repetida no mesmo dia
                $jaNotificado = Notificacao::where('user_id', $user->id)
                    ->where('tipo', 'prazo')
                    ->where('titulo', 'Prazo Vencendo: ' . $tarefa->titulo)
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if (!$jaNotificado) {
                    // Formata a data para ficar bonita (Ex: 05/03/2026 às 14:00)
                    $dataFormatada = Carbon::parse($tarefa->data_vencimento)->format('d/m/Y \à\s H:i');

                    Notificacao::create([
                        'user_id'  => $user->id,
                        'tipo'     => 'prazo',
                        'titulo'   => 'Prazo Vencendo: ' . $tarefa->titulo,
                        'mensagem' => "Atenção! Este prazo vence em {$dataFormatada}.",
                        'lida'     => false
                    ]);
                    $contador++;
                }
            }
        }

        $this->info("Verificação concluída. {$contador} notificações geradas.");
    }
}