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
    // Ajuste na tabela de Usuários
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'oab')) {
            $table->string('oab')->nullable();
        }
        if (!Schema::hasColumn('users', 'telefone')) {
            $table->string('telefone')->nullable();
        }
    });

    // Ajuste na tabela de Escritórios (Tenants)
    Schema::table('tenants', function (Blueprint $table) {
        // Se a coluna 'nome' não existir, vamos criá-la agora
        if (!Schema::hasColumn('tenants', 'nome') && !Schema::hasColumn('tenants', 'name')) {
            $table->string('nome')->nullable();
        }
        
        if (!Schema::hasColumn('tenants', 'cnpj')) {
            $table->string('cnpj')->nullable();
        }
        if (!Schema::hasColumn('tenants', 'endereco')) {
            $table->string('endereco')->nullable();
        }
        if (!Schema::hasColumn('tenants', 'logo_url')) {
            $table->string('logo_url')->nullable();
        }
        if (!Schema::hasColumn('tenants', 'cnj_key')) {
            $table->string('cnj_key')->nullable();
        }
        if (!Schema::hasColumn('tenants', 'config_prazos')) {
            $table->integer('config_prazos')->default(2);
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_and_tenants', function (Blueprint $table) {
            //
        });
    }
};
