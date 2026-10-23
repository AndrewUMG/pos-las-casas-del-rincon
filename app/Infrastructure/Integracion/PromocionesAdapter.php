<?php

namespace App\Infrastructure\Integracion;

use App\Application\Facturacion\Ports\Out\AsignarPromocionPort;
use App\Application\Promociones\Ports\In\AplicarPromocionUseCase;
use App\Application\Promociones\Ports\In\CanjearPuntosUseCase;
use App\Application\Ventas\Ports\Out\MotorPromocionesPort;
use App\Domain\Promociones\Promocion;
use App\Domain\Ventas\Venta;

/**
 * Adaptador entre contextos: Despacho y Facturación → Promociones.
 */
class PromocionesAdapter implements MotorPromocionesPort, AsignarPromocionPort
{
    public function __construct(
        private AplicarPromocionUseCase $promociones,
        private CanjearPuntosUseCase $puntos,
    ) {
    }

    public function aplicar(Venta $venta): void
    {
        $this->promociones->aplicar($venta);
    }

    public function canjearCupon(Venta $venta, string $token): Promocion
    {
        return $this->promociones->canjearCupon($venta, $token);
    }

    public function canjearPuntos(Venta $venta, int $puntos): void
    {
        $this->puntos->canjear($venta, $puntos);
    }

    /** Al cancelar la venta se liberan sus cupones y se devuelven los puntos canjeados. */
    public function liberar(Venta $venta): void
    {
        if ($venta->id) {
            $this->promociones->liberarCupones($venta->id);
        }
        if ($venta->clienteId && $venta->puntosCanjeados > 0) {
            $this->puntos->devolver($venta->clienteId, $venta->puntosCanjeados);
        }
    }

    public function emitirCupon(int $facturaId, ?int $clienteId): ?array
    {
        return $this->promociones->emitirCupon($facturaId, $clienteId);
    }

    public function cuponDeFactura(int $facturaId): ?array
    {
        return $this->promociones->cuponDeFactura($facturaId);
    }

    public function acreditarPuntos(int $clienteId, int $puntos): void
    {
        $this->puntos->acreditar($clienteId, $puntos);
    }
}
