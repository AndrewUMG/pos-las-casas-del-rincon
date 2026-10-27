<?php

namespace App\Providers;

use App\Application\Caja\Ports\In\ConsultarCajaUseCase;
use App\Application\Seguridad\Ports\In\AsignarPermisosUseCase;
use App\Infrastructure\Http\Middleware\VerificarPermiso;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // El menú interactivo muestra solo las opciones permitidas para el rol del usuario.
        View::composer('*', function ($view) {
            $view->with('permisos', VerificarPermiso::delUsuario(request(), app(AsignarPermisosUseCase::class)));
        });

        // Estado de la caja del usuario, visible en la barra superior durante todo el turno.
        View::composer('layouts.app', function ($view) {
            $usuario = request()->user();
            $caja = null;
            if ($usuario && in_array('caja', VerificarPermiso::delUsuario(request(), app(AsignarPermisosUseCase::class)), true)) {
                $caja = app(ConsultarCajaUseCase::class)->abiertaDe($usuario->id);
            }
            $view->with('cajaAbierta', $caja);
        });
    }
}
