<?php

namespace App\Application\Seguridad\Ports\In;

/**
 * Finaliza la sesión del usuario.
 */
interface CerrarSesionUseCase
{
    public function cerrar(): void;
}
