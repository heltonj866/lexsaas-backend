<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('honorarios', function (Blueprint $table) {
            // Adiciona a coluna deleted_at de forma segura
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::table('honorarios', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};