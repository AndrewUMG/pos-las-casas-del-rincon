<?php

namespace App\Application\Seguridad\Ports\Out;

/**
 * Cifrado de contraseñas.
 */
interface PasswordHasher
{
    public function hash(string $plano): string;
    public function verificar(string $plano, string $hash): bool;
}
