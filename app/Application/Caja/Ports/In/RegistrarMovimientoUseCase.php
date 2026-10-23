<?php

namespace App\Application\Caja\Ports\In;

/**
 * Ingresos y egresos de la caja abierta.
 */
interface RegistrarMovimientoUseCase
{
    public function registrar(int $cajaId, string $tipo, string $metodo, float $monto, string $descripcion, string $referencia): void;
}
