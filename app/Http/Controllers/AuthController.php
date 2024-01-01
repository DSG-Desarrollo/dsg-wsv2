<?php

namespace App\Http\Controllers;

use App\Models\Employees;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->json()->all(); // Obtiene todos los datos JSON enviados en la solicitud
        //dd($data);
        // Verifica si se enviaron 'usuario' y 'clave'
        if (isset($data['usuario']) && isset($data['clave'])) {
            $user = User::where('usuario', $data['usuario'])
            ->where('estado_usuario', 'A')->first();
    
            if (!$user || $user->clave !== md5($data['clave'])) {
                return response()->json(['message' => 'Credenciales incorrectas'], 401);
            }
    
            // Genera un token de acceso aquí (utilizando Laravel Passport u otro método apropiado)
    
            return response()->json(['user' => $user]);
        } else {
            return response()->json(['message' => 'Faltan credenciales'], 400);
        }
    }
    
}