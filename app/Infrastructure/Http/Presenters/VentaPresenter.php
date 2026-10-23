<?php

namespace App\Infrastructure\Http\Presenters;

use App\Domain\Ventas\LineaVenta;
use App\Domain\Ventas\Venta;

/**
 * Convierte el agregado Venta en datos para las vistas y respuestas JSON.
 */
final class VentaPresenter
{
    public static function arreglo(Venta $v): array
    {
        return [
            'id' => $v->id,
            'estado' => $v->estado,
            'cliente_id' => $v->clienteId,
            'lineas' => array_map(fn (LineaVenta $l) => [
                'producto_id' => $l->productoId, 'codigo' => $l->codigo, 'nombre' => $l->nombre,
                'cantidad' => $l->cantidad, 'precio' => $l->precioUnitario, 'descuento' => $l->descuento,
                'subtotal' => $l->subtotal(), 'promocion' => $l->promocion,
            ], $v->lineas()),
            'subtotal' => $v->subtotal(),
            'descuento_lineas' => $v->descuentoLineas(),
            'descuento_vale' => $v->descuentoVale,
            'descuento_puntos' => $v->descuentoPuntos,
            'puntos_canjeados' => $v->puntosCanjeados,
            'puntos_ganados' => $v->puntosGanados,
            'total' => $v->total(),
            'articulos' => array_sum(array_map(fn (LineaVenta $l) => $l->cantidad, $v->lineas())),
        ];
    }
}
