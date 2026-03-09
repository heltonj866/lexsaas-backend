<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->dateTime('data_vencimento');
            $table->enum('status', ['pendente', 'em_andamento', 'concluido'])->default('pendente');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            
            $table->foreignId('processo_id')->nullable()->constrained('processos')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes(); // Proteção de exclusão (Lixeira)
        });
    }

    public function down()
    {
        Schema::dropIfExists('tarefas');
    }
};