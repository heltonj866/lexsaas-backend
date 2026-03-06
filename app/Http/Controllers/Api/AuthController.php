<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Valida se o React mandou e-mail e senha
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Busca o usuário no banco
        $user = User::where('email', $request->email)->first();

        // 3. Verifica se o usuário existe e se a senha bate com o Hash do banco
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Esse erro volta como 422 para o React tratar visualmente
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // 4. Se passou, cria o "Crachá" (Token)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Devolve o Token e os dados do usuário para o React guardar
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        // Pega o token atual que fez a requisição e o destrói (Invalida o crachá)
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Sessão encerrada com sucesso']);
    }
}