<?php

namespace App\Infrastructure\Security;

use App\Application\Seguridad\Ports\Out\SesionPort;
use Illuminate\Support\Facades\Auth;

class LaravelSesionAdapter implements SesionPort
{
    public function iniciar(int $usuarioId): void
    {
        Auth::loginUsingId($usuarioId);
        request()->session()->regenerate();
    }

    public function cerrar(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
