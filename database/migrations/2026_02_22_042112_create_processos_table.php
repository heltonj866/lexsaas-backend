<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('processos', function (Blueprint $table) {
        $table->id();
        // Isolamento do Escritório (UUID)
        $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
        
        // Relacionamento com o Cliente
        $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

        $table->string('numero_processo')->unique(); // Ex: 0000000-00.2026.8.26.0000
        $table->string('titulo');
        $table->text('descricao')->nullable();
        $table->enum('status', ['ativo', 'suspenso', 'concluido'])->default('ativo');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processos');
    }
};
