<?php

namespace App\Infrastructure\Http\Controllers\Seguridad;

use App\Application\Seguridad\Ports\In\AsignarPermisosUseCase;
use Illuminate\Http\Request;

/**
 * Asignación de las opciones del menú permitidas para cada rol.
 */
class RolController
{
    public function __construct(private AsignarPermisosUseCase $permisos)
    {
    }

    public function index()
    {
        return view('seguridad.roles', ['roles' => $this->permisos->roles(), 'catalogoPermisos' => $this->permisos->permisos()]);
    }

    public function asignar(Request $request, int $rol)
    {
        $this->permisos->asignar($rol, (array) $request->input('permisos', []));

        return back()->with('ok', 'Permisos del rol actualizados.');
    }
}
