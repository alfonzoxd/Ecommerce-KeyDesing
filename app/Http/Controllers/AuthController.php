<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ResponseTrait;

    public function register(RegisterRequest $request)
    {
        // 1. Validación: Ya se hizo automáticamente en RegisterRequest.
        // Si falla, Laravel devuelve error 422 automáticamente.

        // 2. Crear Usuario
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'client',
        ]);

        // 3. Generar Token
        $token = Auth::guard('api')->login($user);

        // 4. Responder usando el Trait y el Resource
        $data = [
            'user' => new UserResource($user), // Transformamos el usuario
            'token' => $token,
            'token_type' => 'bearer',
        ];

        return $this->responseJson($data, 201); // Usamos método del Trait
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            // Usamos el método de error del Trait
            return $this->responseErrorJson('Credenciales inválidas', [], 401);
        }

        $user = Auth::guard('api')->user();

        // Estructura limpia de respuesta
        $data = [
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];

        return $this->responseJson($data);
    }

    public function me()
    {
        $user = Auth::guard('api')->user();
        // Usamos el Resource para no devolver datos basura
        return $this->responseJson(new UserResource($user));
    }

    public function logout()
    {
        Auth::guard('api')->logout();
        return $this->responseJsonMessageOk('Sesión cerrada exitosamente');
    }
}
