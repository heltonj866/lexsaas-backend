<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Processo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar o Escritório Matriz
        $tenant = Tenant::firstOrCreate(
            ['cnpj_nif' => '00.000.000/0001-00'],
            [
                'id' => Str::uuid(),
                'nome_escritorio' => 'Escritório Matriz - Demonstração',
                'ativo' => true,
            ]
        );

        // 2. Criar o Administrador
        $user = User::firstOrCreate(
            ['email' => 'admin@advocacia.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Doutor Admin',
                'password' => Hash::make('senha123'),
                'role' => 'admin',
            ]
        );

        // 3. Criar Clientes para este Escritório
        $cliente1 = Cliente::firstOrCreate(
            ['cpf_cnpj' => '123.456.789-00', 'tenant_id' => $tenant->id],
            ['nome' => 'João da Silva', 'email' => 'joao@email.com']
        );

        $cliente2 = Cliente::firstOrCreate(
            ['cpf_cnpj' => '987.654.321-11', 'tenant_id' => $tenant->id],
            ['nome' => 'Maria Oliveira', 'email' => 'maria@email.com']
        );

        // 4. Criar Processos para os Clientes
        Processo::firstOrCreate(
            ['numero_processo' => '5001234-55.2024.8.26.0000'],
            [
                'tenant_id' => $tenant->id,
                'cliente_id' => $cliente1->id,
                'titulo' => 'Ação de Cobrança - João',
                'status' => 'ativo'
            ]
        );

        Processo::firstOrCreate(
            ['numero_processo' => '5009876-11.2024.8.26.0000'],
            [
                'tenant_id' => $tenant->id,
                'cliente_id' => $cliente2->id,
                'titulo' => 'Inventário - Maria',
                'status' => 'ativo'
            ]
        );
    }
}