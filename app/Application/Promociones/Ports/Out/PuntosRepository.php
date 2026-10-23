<?php

namespace App\Application\Promociones\Ports\Out;

/**
 * Saldo de puntos de los clientes.
 */
interface PuntosRepository
{
    public function saldo(int $clienteId): int;
    public function ajustar(int $clienteId, int $delta): void;
}
