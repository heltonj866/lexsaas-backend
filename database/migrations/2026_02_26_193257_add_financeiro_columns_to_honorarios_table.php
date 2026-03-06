<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona as colunas necessárias para a unificação financeira.
     */
    public function up(): void
    {
        Schema::table('honorarios', function (Blueprint $table) {
            // Verifica se a coluna já existe antes de criar para evitar novos erros
            if (!Schema::hasColumn('honorarios', 'tipo')) {
                $table->enum('tipo', ['receita', 'despesa'])->default('receita')->after('valor');
            }
            
            if (!Schema::hasColumn('honorarios', 'categoria')) {
                $table->string('categoria')->nullable()->after('tipo');
            }

            if (!Schema::hasColumn('honorarios', 'cliente_id')) {
                $table->foreignId('cliente_id')->nullable()->after('processo_id')->constrained('clientes')->onDelete('set null');
            }

            if (!Schema::hasColumn('honorarios', 'data_pagamento')) {
                $table->date('data_pagamento')->nullable()->after('data_vencimento');
            }
        });
    }

    /**
     * Reverte as alterações caso algo dê errado.
     */
    public function down(): void
    {
        Schema::table('honorarios', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn(['tipo', 'categoria', 'cliente_id', 'data_pagamento']);
        });
    }
};