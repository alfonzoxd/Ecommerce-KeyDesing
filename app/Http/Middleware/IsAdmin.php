<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;

class IsAdmin
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next)
    {
        // Verificamos si hay usuario logueado Y si su rol es 'admin'
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Usamos el Trait para devolver el error con el mismo formato que el resto de la API
        return $this->responseErrorJson(
            'No autorizado. Se requieren privilegios de administrador.',
            [], // Data vacía
            403 // Código HTTP Forbidden
        );
    }
}
