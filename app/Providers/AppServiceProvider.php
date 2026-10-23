<?php

namespace App\Providers;

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
    }
}
