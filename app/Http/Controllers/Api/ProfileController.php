<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Atualiza os dados normais e as preferências
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'oab' => 'sometimes|nullable|string|max:50',
            'preferencias' => 'sometimes|array',
        ]);

        $user->fill($validated);

        // Lógica para salvar a foto (avatar)
        if ($request->hasFile('avatar')) {
            // Deleta a foto antiga se existir
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Configurações salvas com sucesso!',
            'user' => $user
        ]);
    }

    // Atualiza apenas a senha
    public function updatePassword(Request $request)
    {
        $request->validate([
            // current_password verifica automaticamente se a senha atual bate com a do banco
            'current_password' => 'required|current_password', 
            'password' => 'required|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Senha atualizada com segurança!']);
    }
}