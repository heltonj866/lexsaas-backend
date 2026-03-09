<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nome_escritorio' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            // Cria o Escritório
            $tenantId = Str::uuid()->toString();
            DB::table('tenants')->insert([
                'id' => $tenantId,
                'nome_escritorio' => $request->nome_escritorio,
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Cria o Sócio Administrador
            $user = User::create([
                'tenant_id' => $tenantId,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
            ]);

            DB::commit();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token, // Alinhado com o seu login
                'token_type' => 'Bearer',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao criar conta.', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sessão encerrada com sucesso']);
    }

    // Rota para solicitar redefinição de senha
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // O Laravel lida com a criação do Token seguro e o disparo do e-mail
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Retornamos sempre sucesso para evitar que descubram quais e-mails existem no sistema
        return response()->json([
            'message' => 'Se o e-mail existir, o link de recuperação foi enviado.'
        ]);
    }

    // Rota para redefinir a senha (o React vai chamar essa rota com o Token e o e-mail)
    public function resetPassword(Request $request)
    {
        // O Laravel exige a regra 'confirmed' que verifica o campo 'password_confirmation'
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                // Se o token for válido, troca a senha e salva
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Senha redefinida com sucesso.']);
        }

        throw ValidationException::withMessages([
            'email' => ['Não foi possível redefinir a senha. O link pode ser inválido.'],
        ]);
    }
}