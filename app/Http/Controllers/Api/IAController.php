<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IAController extends Controller
{
    public function resumirProcesso(Request $request)
    {
        $request->validate(['texto' => 'required|string']);

        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        $response = Http::post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "Você é um assistente jurídico sênior. Resuma o seguinte andamento processual de forma simples para um cliente entender: " . $request->texto]
                    ]
                ]
            ]
        ]);

        return response()->json([
            'resumo' => $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'Não foi possível gerar o resumo.'
        ]);
    }
}