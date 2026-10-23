<?php

namespace App\Application\Inventario\Ports\In;

/**
 * Salidas y bajas de inventario solicitadas por otros contextos.
 */
interface AjustarExistenciasUseCase
{
    public function registrarSalida(int $productoId, int $cantidad, string $referencia, string $descripcion): void;
    public function registrarBaja(int $productoId, int $cantidad, string $referencia, string $descripcion): void;
}
