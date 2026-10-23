<?php

namespace App\Application\Caja\Ports\Out;

/**
 * Ingresos y egresos de la caja.
 */
interface MovimientoCajaRepository
{
    public function registrar(int $cajaId, string $tipo, string $metodo, float $monto, string $descripcion, string $referencia, \DateTimeImmutable $fecha): void;

    /** @return array{ingreso: array<string,float>, egreso: array<string,float>} */
    public function totales(int $cajaId): array;
    public function listar(int $cajaId): array;
}
