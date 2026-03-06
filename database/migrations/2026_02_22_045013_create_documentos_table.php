<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            
            // Segurança SaaS
            $table->char('tenant_id', 36)->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // Todo documento pertence a um Cliente (obrigatório)
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            
            // Mas nem todo documento pertence a um Processo (opcional, pode ser só o RG do cliente)
            $table->foreignId('processo_id')->nullable()->constrained('processos')->onDelete('cascade');

            $table->string('titulo'); // Ex: "Procuração Assinada", "Print do WhatsApp"
            $table->string('caminho_arquivo'); // Onde o arquivo está salvo no servidor
            $table->string('extensao', 10); // Ex: pdf, jpg, docx
            $table->integer('tamanho_kb')->nullable(); // Para controlar o espaço em disco do servidor
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documentos');
    }
};