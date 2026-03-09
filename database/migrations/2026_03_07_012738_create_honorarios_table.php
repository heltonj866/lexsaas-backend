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
        Schema::create('honorarios', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id'); // Relação com o escritório
            
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->enum('tipo', ['receita', 'despesa']);
            $table->string('categoria')->nullable();
            $table->date('data_vencimento');
            $table->string('status')->default('pendente');
            
            // Relacionamentos nulos (para despesas do escritório que não têm cliente/processo)
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('processo_id')->nullable()->constrained('processos')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honorarios');
    }
};
