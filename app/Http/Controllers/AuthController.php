<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->json()->all(); // Obtiene todos los datos JSON enviados en la solicitud
        //dd($data);
        // Verifica si se enviaron 'usuario' y 'clave'
        if (isset($data['email']) && isset($data['password'])) {
            $user = User::where('usuario', $data['email'])
                ->where('estado_usuario', 'A')->first();

            if (!$user || $user->clave !== md5($data['password'])) {
                return response()->json(['message' => 'Credenciales incorrectas'], 401);
            }

            $responseData = [
                'user' => $user,
            ];

            if ($data['rememberSession']) {
                $tokenResult = $user->createToken('apiToken')->accessToken;

                $responseData['tokenData'] = [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'expires_at' => now()->addDays(7)->toDateTimeString(),
                ];
            }

            return response()->json($responseData);
        } else {
            return response()->json(['message' => 'Faltan credenciales'], 400);
        }
    }
}
