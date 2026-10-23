<?php

namespace App\Infrastructure\Http\Middleware;

use App\Application\Seguridad\Ports\In\AsignarPermisosUseCase;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/**
 * Permite el acceso a una opción del menú solo si el rol del usuario la tiene asignada.
 */
class VerificarPermiso
{
    public function __construct(private AsignarPermisosUseCase $permisos)
    {
    }

    public function handle(Request $request, Closure $next, string $clave)
    {
        if (! in_array($clave, self::delUsuario($request, $this->permisos), true)) {
            abort(403, 'Su rol no tiene acceso a este módulo.');
        }

        return $next($request);
    }

    /** Permisos del usuario autenticado (se calculan una vez por petición y se comparten con las vistas). */
    public static function delUsuario(Request $request, AsignarPermisosUseCase $permisos): array
    {
        if (! $request->attributes->has('permisos')) {
            $lista = $request->user() ? $permisos->permisosDeUsuario($request->user()->id) : [];
            $request->attributes->set('permisos', $lista);
            View::share('permisos', $lista);
        }

        return $request->attributes->get('permisos');
    }
}
