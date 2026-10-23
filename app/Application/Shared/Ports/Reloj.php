<?php

namespace App\Application\Shared\Ports;

/**
 * Fuente de la fecha y hora actual.
 */
interface Reloj
{
    public function ahora(): \DateTimeImmutable;
}
