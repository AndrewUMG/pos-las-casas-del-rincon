<?php

namespace App\Infrastructure\Integracion;

use App\Application\Caja\Ports\In\ConsultarCajaUseCase;
use App\Application\Caja\Ports\In\RegistrarMovimientoUseCase;
use App\Application\Shared\Ports\CajaPort;

/**
 * Adaptador entre contextos: Despacho/Cobros/Devoluciones → Caja.
 */
class CajaAdapter implements CajaPort
{
    public function __construct(
        private ConsultarCajaUseCase $consulta,
        private RegistrarMovimientoUseCase $movimientos,
    ) {
    }

    public function cajaAbiertaDe(int $usuarioId): ?int
    {
        return $this->consulta->abiertaDe($usuarioId)?->id;
    }

    public function registrarIngreso(int $cajaId, string $metodo, float $monto, string $descripcion, string $referencia): void
    {
        $this->movimientos->registrar($cajaId, 'ingreso', $metodo, $monto, $descripcion, $referencia);
    }

    public function registrarEgreso(int $cajaId, string $metodo, float $monto, string $descripcion, string $referencia): void
    {
        $this->movimientos->registrar($cajaId, 'egreso', $metodo, $monto, $descripcion, $referencia);
    }
}
