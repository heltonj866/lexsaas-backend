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
    Schema::table('honorarios', function (Blueprint $table) {
        // Altera a coluna para permitir valores nulos (nullable)
        $table->foreignId('processo_id')->nullable()->change();
    });
}

public function down()
{
    Schema::table('honorarios', function (Blueprint $table) {
        $table->foreignId('processo_id')->nullable(false)->change();
    });
}
};
