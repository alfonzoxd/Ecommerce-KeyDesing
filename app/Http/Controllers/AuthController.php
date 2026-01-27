<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Registro de nuevos clientes.
     * (Los admins se crean manualmente en BD o con un seeder por seguridad)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // requiere campo password_confirmation
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'client', // Forzamos que sea cliente
        ]);

        // Generamos el token inmediatamente para que quede logueado
        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token,
            'role' => $user->role // Importante para el frontend
        ], 201);
    }

    /**
     * Login para todos (Admins y Clientes).
     * El Frontend decidirá a dónde redirigir basado en el 'role' que devolvemos.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $user = Auth::guard('api')->user();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role // <--- El frontend usará esto para redirigir
            ]
        ]);
    }

    /**
     * Obtener datos del usuario autenticado (Perfil).
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * Cerrar sesión (Invalidar token).
     */
    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}
