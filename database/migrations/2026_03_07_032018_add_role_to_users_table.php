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
        Schema::table('users', function (Blueprint $table) {
            // Adiciona a coluna de perfil. 
            // Colocamos o padrão como 'admin' para que a sua conta atual não perca os acessos!
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'advogado', 'estagiario'])->default('admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
