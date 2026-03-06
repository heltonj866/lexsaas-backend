<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::dropIfExists('honorarios');

    Schema::create('financeiros', function (Blueprint $table) {
    $table->id();
    $table->char('tenant_id', 36)->index(); //
    
    // Relacionamentos (Opcionais, pois uma conta de internet não tem processo/cliente)
    $table->foreignId('processo_id')->nullable()->constrained('processos')->onDelete('set null');
    $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
    
    // O Coração da Unificação
    $table->enum('tipo', ['receita', 'despesa'])->default('receita'); 
    $table->enum('categoria', ['honorarios', 'custas', 'administrativo', 'impostos', 'marketing'])->default('honorarios');
    
    $table->string('descricao');
    $table->decimal('valor', 12, 2); //
    $table->date('data_vencimento');
    $table->date('data_pagamento')->nullable(); // Para saber quando o dinheiro REALMENTE entrou
    $table->enum('status', ['pendente', 'pago', 'atrasado', 'cancelado'])->default('pendente'); //
    
    $table->timestamps();
    $table->softDeletes(); //

    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade'); //
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honorarios');
    }
};
