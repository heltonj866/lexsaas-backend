<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Tenant;
use App\Models\User;

class ConfiguracaoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = Tenant::findOrFail($user->tenant_id);

        $response = [
            'user' => $user,
            'tenant' => $tenant
        ];

        // Se for admin, devolve também a lista de todos os funcionários do escritório
        if ($user->role === 'admin') {
            $response['equipe'] = User::where('tenant_id', $user->tenant_id)->get();
        }

        return response()->json($response);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $tenant = Tenant::findOrFail($user->tenant_id);

        // Regras base para todos
        $rules = [
            'perfil.nome' => 'required|string|max:255',
            'senhas.nova' => 'nullable|min:8',
        ];

        // Regras exclusivas para o Admin
        if ($user->role === 'admin') {
            $rules['escritorio.nome'] = 'required|string|max:255';
        }

        $request->validate($rules);

        // 🛡️ Segurança: Alteração de Senha Própria
        if ($request->filled('senhas.nova')) {
            if (!Hash::check($request->input('senhas.atual'), $user->password)) {
                throw ValidationException::withMessages([
                    'senha_atual' => ['A senha atual fornecida está incorreta.']
                ]);
            }
            $user->password = Hash::make($request->input('senhas.nova'));
        }

        // 👤 Atualização de Perfil (Todos podem)
        $user->update([
            'name' => $request->input('perfil.nome'),
            'oab' => $request->input('perfil.oab'),
            'telefone' => $request->input('perfil.telefone'),
        ]);

        // 🏢 Atualização do Escritório (SÓ ADMIN)
        if ($user->role === 'admin') {
            $tenant->update([
                'nome' => $request->input('escritorio.nome'),
                'cnpj' => $request->input('escritorio.cnpj'),
                'endereco' => $request->input('escritorio.endereco'),
                'cnj_key' => $request->input('sistema.cnj_key'),
                'config_prazos' => (int) $request->input('sistema.dias_antecedencia_aviso'),
            ]);
        }

        return response()->json(['message' => 'Configurações atualizadas!']);
    }

    // 👇 NOVAS ROTAS DE EQUIPA 👇

    public function storeMembro(Request $request)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado. Apenas o administrador pode convidar.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,advogado,estagiario',
        ]);

        $membro = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'tenant_id' => $admin->tenant_id, // Vincula o funcionário ao mesmo escritório!
        ]);

        return response()->json($membro, 201);
    }

    public function destroyMembro(Request $request, $id)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') return response()->json(['error' => 'Acesso negado'], 403);
        if ($admin->id == $id) return response()->json(['error' => 'Você não pode remover-se a si próprio'], 400);

        $membro = User::where('tenant_id', $admin->tenant_id)->findOrFail($id);
        $membro->delete();

        return response()->json(['message' => 'Membro removido']);
    }
}