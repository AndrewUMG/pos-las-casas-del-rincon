<?php

namespace App\Domain\Promociones;

use App\Domain\Ventas\LineaVenta;
use App\Domain\Ventas\Venta;

/**
 * Servicio de dominio que recalcula los descuentos de una venta
 * aplicando las promociones vigentes (automáticas y las de cupones QR canjeados).
 */
class MotorPromociones
{
    /**
     * @param  Promocion[]  $promociones
     */
    public function aplicar(Venta $venta, array $promociones): void
    {
        $venta->limpiarPromociones();

        foreach ($promociones as $promo) {
            $enAlcance = array_filter($venta->lineas(), fn (LineaVenta $l) => $promo->enAlcance($l));
            $unidades = array_sum(array_map(fn (LineaVenta $l) => $l->cantidad, $enAlcance));
            $beneficiadas = array_filter($venta->lineas(), fn (LineaVenta $l) => $promo->esBeneficiada($l));

            match ($promo->tipo) {
                TipoPromocion::SegundoMitad => $this->segundoMitad($beneficiadas, $promo),
                TipoPromocion::Mayoreo => $unidades >= $promo->cantidadMinima ? $this->mayoreo($beneficiadas, $promo) : null,
                TipoPromocion::ProductoGratuito => $unidades >= $promo->cantidadMinima ? $this->gratuito($beneficiadas, $promo) : null,
                TipoPromocion::ValeRegalo => $venta->agregarDescuentoVale($promo->valor),
                TipoPromocion::Puntos => $venta->puntosGanados += (int) floor($unidades * $promo->valor),
            };
        }
    }

    /** Por cada par de unidades, la segunda se cobra a mitad de precio. */
    private function segundoMitad(array $lineas, Promocion $promo): void
    {
        foreach ($lineas as $l) {
            $pares = intdiv($l->cantidad, 2);
            $l->agregarDescuento($pares * $l->precioUnitario * 0.5, $promo->nombre);
        }
    }

    /** Se cobra el precio de mayoreo del producto promocionado. */
    private function mayoreo(array $lineas, Promocion $promo): void
    {
        foreach ($lineas as $l) {
            if ($l->precioMayoreo !== null && $l->precioMayoreo < $l->precioUnitario) {
                $l->agregarDescuento(($l->precioUnitario - $l->precioMayoreo) * $l->cantidad, $promo->nombre);
            }
        }
    }

    /** Se regala una unidad del producto promocionado. */
    private function gratuito(array $lineas, Promocion $promo): void
    {
        foreach ($lineas as $l) {
            $l->agregarDescuento($l->precioUnitario, $promo->nombre);
            break;
        }
    }
}
