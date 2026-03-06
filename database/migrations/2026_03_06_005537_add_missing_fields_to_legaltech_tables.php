<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adiciona campos de endereço e softDeletes aos Clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('cep')->nullable()->after('email');
            $table->string('endereco')->nullable()->after('cep');
            $table->string('bairro')->nullable()->after('endereco');
            $table->string('cidade')->nullable()->after('bairro');
            $table->softDeletes();
        });

        // 2. Adiciona detalhes ao Processo
        Schema::table('processos', function (Blueprint $table) {
            $table->string('tipo_acao')->nullable()->after('titulo');
            $table->string('vara')->nullable()->after('tipo_acao');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['cep', 'endereco', 'bairro', 'cidade']);
            $table->dropSoftDeletes();
        });

        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn(['tipo_acao', 'vara']);
        });
    }
};