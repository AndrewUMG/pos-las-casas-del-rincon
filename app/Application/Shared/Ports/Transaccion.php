<?php

namespace App\Application\Shared\Ports;

/**
 * Ejecuta un bloque de trabajo de forma atómica.
 */
interface Transaccion
{
    public function ejecutar(callable $trabajo): mixed;
}
