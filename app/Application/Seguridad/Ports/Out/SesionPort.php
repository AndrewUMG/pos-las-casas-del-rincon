<?php

namespace App\Application\Seguridad\Ports\Out;

/**
 * Mantiene la sesión del usuario autenticado.
 */
interface SesionPort
{
    public function iniciar(int $usuarioId): void;
    public function cerrar(): void;
}
