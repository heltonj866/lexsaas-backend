<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            
            // Ligação obrigatória com o Escritório (UUID)
            $table->char('tenant_id', 36)->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // Ligação opcional com o Processo (onDelete set null para não apagar a tarefa se o processo for apagado)
            $table->foreignId('processo_id')->nullable()->constrained('processos')->onDelete('set null');

            $table->string('titulo');
            $table->text('descricao')->nullable();
            
            // Prazos fatais precisam de data E hora (dateTime)
            $table->dateTime('data_vencimento'); 
            
            $table->enum('status', ['pendente', 'em_andamento', 'concluido'])->default('pendente');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');

            $table->timestamps();
            $table->softDeletes(); // Nunca apagamos prazos por segurança
        });
    }

    public function down()
    {
        Schema::dropIfExists('tarefas');
    }
};