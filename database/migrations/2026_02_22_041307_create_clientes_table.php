<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id(); // ID do cliente (pode ser número normal)
            
            // Chave estrangeira em formato UUID apontando para tenants
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            
            $table->string('nome');
            $table->string('cpf_cnpj');
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            
            $table->timestamps();
            
            // Opcional, mas recomendado: Evita cadastrar o mesmo CPF no MESMO escritório
            $table->unique(['tenant_id', 'cpf_cnpj']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
