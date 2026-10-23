<?php

namespace App\Application\Shared\Ports;

/**
 * Puerto hacia el contexto Caja (usado por Cobros y Devoluciones).
 */
interface CajaPort
{
    /** Id de la caja abierta del usuario o null si no tiene. */
    public function cajaAbiertaDe(int $usuarioId): ?int;

    public function registrarIngreso(int $cajaId, string $metodo, float $monto, string $descripcion, string $referencia): void;
    public function registrarEgreso(int $cajaId, string $metodo, float $monto, string $descripcion, string $referencia): void;
}
