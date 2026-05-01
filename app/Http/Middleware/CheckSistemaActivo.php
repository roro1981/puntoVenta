<?php

namespace App\Http\Middleware;

use App\Models\Globales;
use Closure;
use Illuminate\Http\Request;

class CheckSistemaActivo
{
    public function handle(Request $request, Closure $next)
    {
        $activo = Globales::where('nom_var', 'SISTEMA_ACTIVO')->value('valor_var');

        if ((string) $activo === '0') {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Sistema suspendido por falta de pago. Contacte al administrador para regularizar su cuenta.'
            ], 403);
        }

        return $next($request);
    }
}
