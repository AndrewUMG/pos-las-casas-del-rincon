<?php

namespace App\Application\Cobros\Services;

use App\Application\Cobros\Ports\In\CalcularCambioUseCase;
use App\Application\Cobros\Ports\In\CobrarVentaUseCase;
use App\Application\Cobros\Ports\Out\PagoRepository;
use App\Application\Shared\Ports\CajaPort;
use App\Application\Shared\Ports\InventarioPort;
use App\Application\Shared\Ports\Reloj;
use App\Application\Shared\Ports\Transaccion;
use App\Application\Ventas\Ports\Out\MotorPromocionesPort;
use App\Application\Ventas\Ports\Out\VentaRepository;
use App\Domain\Cobros\MetodoPago;
use App\Domain\Cobros\Pago;
use App\Domain\Shared\Dinero;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Cobro de la venta. Al cobrar: se registra el pago, se descuentan las existencias
 * y se registra el ingreso en la caja abierta.
 */
class CobroService implements CobrarVentaUseCase, CalcularCambioUseCase
{
    public function __construct(
        private VentaRepository $ventas,
        private PagoRepository $pagos,
        private MotorPromocionesPort $promociones,
        private InventarioPort $inventario,
        private CajaPort $caja,
        private Transaccion $transaccion,
        private Reloj $reloj,
    ) {
    }

    public function cobrar(int $ventaId, string $metodo, ?float $montoRecibido, ?string $referencia): Pago
    {
        $metodoPago = MetodoPago::tryFrom($metodo) ?? throw new ReglaNegocioException('Seleccione el método de pago.');

        return $this->transaccion->ejecutar(function () use ($ventaId, $metodoPago, $montoRecibido, $referencia) {
            $venta = $this->ventas->buscarPorId($ventaId) ?? throw new ReglaNegocioException('La venta no existe.');
            $venta->asegurarEnProceso();
            $this->promociones->aplicar($venta);
            $total = $venta->total();

            $pago = Pago::registrar($venta->id, $metodoPago, $total, $montoRecibido, $referencia, $this->reloj->ahora());
            $venta->marcarPagada();
            $this->ventas->guardar($venta);
            $this->pagos->guardar($pago);

            $ref = "VENTA-{$venta->id}";
            foreach ($venta->lineas() as $linea) {
                $this->inventario->descontarPorVenta($linea->productoId, $linea->cantidad, $ref);
            }
            if ($total > 0) {
                $this->caja->registrarIngreso($venta->cajaId, $metodoPago->value, $total, "Venta #{$venta->id}", $ref);
            }

            return $pago;
        });
    }

    public function cambio(float $total, float $montoRecibido): float
    {
        if ($montoRecibido < $total) {
            throw new ReglaNegocioException('El monto recibido es menor al total.');
        }

        return Dinero::redondear($montoRecibido - $total);
    }
}
