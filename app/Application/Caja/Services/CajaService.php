<?php

namespace App\Application\Caja\Services;

use App\Application\Caja\Ports\In\AbrirCajaUseCase;
use App\Application\Caja\Ports\In\CerrarCajaUseCase;
use App\Application\Caja\Ports\In\ConsultarCajaUseCase;
use App\Application\Caja\Ports\In\RegistrarMovimientoUseCase;
use App\Application\Caja\Ports\Out\CajaRepository;
use App\Application\Caja\Ports\Out\MovimientoCajaRepository;
use App\Application\Shared\Ports\Reloj;
use App\Application\Shared\Ports\Transaccion;
use App\Domain\Caja\Caja;
use App\Domain\Shared\ReglaNegocioException;

class CajaService implements AbrirCajaUseCase, RegistrarMovimientoUseCase, CerrarCajaUseCase, ConsultarCajaUseCase
{
    public function __construct(
        private CajaRepository $cajas,
        private MovimientoCajaRepository $movimientos,
        private Transaccion $transaccion,
        private Reloj $reloj,
    ) {
    }

    public function abrir(int $usuarioId, float $montoInicial): Caja
    {
        if ($this->cajas->abiertaDe($usuarioId)) {
            throw new ReglaNegocioException('Ya tiene una caja abierta; ciérrela antes de abrir otra.');
        }

        return $this->cajas->guardar(Caja::abrir($usuarioId, $montoInicial, $this->reloj->ahora()));
    }

    public function registrar(int $cajaId, string $tipo, string $metodo, float $monto, string $descripcion, string $referencia): void
    {
        $this->obtener($cajaId)->asegurarAbierta();
        if (! in_array($tipo, ['ingreso', 'egreso'], true) || $monto <= 0) {
            throw new ReglaNegocioException('Movimiento de caja inválido.');
        }
        $this->movimientos->registrar($cajaId, $tipo, $metodo, $monto, $descripcion, $referencia, $this->reloj->ahora());
    }

    public function cerrar(int $usuarioId, float $montoContado): Caja
    {
        $caja = $this->cajas->abiertaDe($usuarioId) ?? throw new ReglaNegocioException('No tiene una caja abierta.');
        $t = $this->movimientos->totales($caja->id);
        $caja->cerrar($t['ingreso']['efectivo'] ?? 0, $t['egreso']['efectivo'] ?? 0, $montoContado, $this->reloj->ahora());

        return $this->transaccion->ejecutar(fn () => $this->cajas->guardar($caja));
    }

    public function abiertaDe(int $usuarioId): ?Caja
    {
        return $this->cajas->abiertaDe($usuarioId);
    }

    public function obtener(int $cajaId): Caja
    {
        return $this->cajas->buscarPorId($cajaId) ?? throw new ReglaNegocioException('La caja no existe.');
    }

    /** Resumen para el corte: ingresos por método de pago, egresos y efectivo esperado. */
    public function resumen(int $cajaId): array
    {
        $caja = $this->obtener($cajaId);
        $t = $this->movimientos->totales($cajaId);
        $ingresos = $t['ingreso'];
        $egresos = $t['egreso'];

        return [
            'caja' => $caja,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'total_ventas' => round(array_sum($ingresos), 2),
            'total_egresos' => round(array_sum($egresos), 2),
            'efectivo_esperado' => round($caja->montoInicial + ($ingresos['efectivo'] ?? 0) - ($egresos['efectivo'] ?? 0), 2),
            'movimientos' => $this->movimientos->listar($cajaId),
        ];
    }

    public function historial(int $usuarioId): array
    {
        return $this->cajas->historial($usuarioId);
    }
}
