<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            // Decisão Arquitetural: Uso de UUID (Universal Unique Identifier) em vez de ID sequencial.
            // Impede que um invasor ou cliente descubra quantos escritórios o sistema tem
            // ou tente adivinhar o ID de outro escritório na URL (ex: /tenant/1 para /tenant/2).
            $table->uuid('id')->primary(); 
            
            $table->string('nome_escritorio');
            $table->string('cnpj_nif')->unique()->nullable(); 
            $table->string('dominio')->unique()->nullable(); // Útil no futuro para subdomínios (ex: cliente.seusistema.com)
            $table->boolean('ativo')->default(true); // Controle para suspender inadimplentes
            
            $table->timestamps();
            $table->softDeletes(); // Nunca apagamos dados (Soft Delete), apenas marcamos como removidos.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
