<?php

namespace App\Infrastructure\Http\Controllers\Seguridad;

use App\Application\Seguridad\Ports\In\CerrarSesionUseCase;
use App\Application\Seguridad\Ports\In\IniciarSesionUseCase;
use Illuminate\Http\Request;

/**
 * Adaptador primario de la interfaz de acceso.
 */
class LoginController
{
    public function formulario()
    {
        return view('seguridad.login');
    }

    public function iniciar(Request $request, IniciarSesionUseCase $sesion)
    {
        $datos = $request->validate(['usuario' => 'required|string', 'password' => 'required|string']);
        $sesion->iniciar($datos['usuario'], $datos['password']);

        return redirect()->intended(route('inicio'));
    }

    public function cerrar(CerrarSesionUseCase $sesion)
    {
        $sesion->cerrar();

        return redirect()->route('login');
    }
}
