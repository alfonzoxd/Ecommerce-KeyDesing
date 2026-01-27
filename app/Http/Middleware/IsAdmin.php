<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Verificamos si hay usuario logueado Y si su rol es 'admin'
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Si no es admin, bloqueamos el paso y devolvemos Error 403 (Prohibido)
        return response()->json([
            'error' => 'No autorizado. Se requieren privilegios de administrador.'
        ], 403);
    }
}
