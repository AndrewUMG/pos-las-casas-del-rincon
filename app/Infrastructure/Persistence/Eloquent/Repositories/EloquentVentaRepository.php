<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Ventas\Ports\Out\VentaRepository;
use App\Domain\Ventas\LineaVenta;
use App\Domain\Ventas\Venta;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\VentaModel;

/**
 * Persiste el agregado Venta completo (encabezado + líneas).
 */
class EloquentVentaRepository implements VentaRepository
{
    public function buscarPorId(int $id): ?Venta
    {
        $m = VentaModel::with('detalles.producto')->find($id);

        return $m ? $this->aDominio($m) : null;
    }

    public function enProcesoDe(int $usuarioId): ?Venta
    {
        $m = VentaModel::with('detalles.producto')->where('usuario_id', $usuarioId)
            ->where('estado', Venta::EN_PROCESO)->latest('id')->first();

        return $m ? $this->aDominio($m) : null;
    }

    public function guardar(Venta $v): Venta
    {
        $m = Mapeador::persistir(VentaModel::class, $v->id, [
            'caja_id' => $v->cajaId, 'usuario_id' => $v->usuarioId, 'cliente_id' => $v->clienteId,
            'fecha' => $v->fecha, 'subtotal' => $v->subtotal(), 'descuento' => $v->descuentoTotal(),
            'descuento_vale' => $v->descuentoVale, 'descuento_puntos' => $v->descuentoPuntos, 'total' => $v->total(),
            'puntos_canjeados' => $v->puntosCanjeados, 'puntos_ganados' => $v->puntosGanados, 'estado' => $v->estado,
        ]);
        $v->id = $m->id;

        $m->detalles()->delete();
        $m->detalles()->createMany(array_map(fn (LineaVenta $l) => [
            'producto_id' => $l->productoId, 'cantidad' => $l->cantidad, 'precio_unitario' => $l->precioUnitario,
            'descuento' => $l->descuento, 'subtotal' => $l->subtotal(), 'promocion' => $l->promocion,
        ], $v->lineas()));

        return $v;
    }

    public function eliminar(int $id): void
    {
        VentaModel::whereKey($id)->delete();
    }

    private function aDominio(VentaModel $m): Venta
    {
        $v = new Venta($m->id, $m->caja_id, $m->usuario_id, $m->cliente_id, Mapeador::fecha($m->fecha), $m->estado);
        foreach ($m->detalles as $d) {
            $p = $d->producto;
            $linea = new LineaVenta($p->id, $p->codigo, $p->nombre, $p->familia_id, (float) $d->precio_unitario,
                $p->precio_mayoreo !== null ? (float) $p->precio_mayoreo : null, $d->cantidad);
            $linea->descuento = (float) $d->descuento;
            $linea->promocion = $d->promocion;
            $v->restaurarLinea($linea);
        }
        $v->descuentoVale = (float) $m->descuento_vale;
        $v->descuentoPuntos = (float) $m->descuento_puntos;
        $v->puntosCanjeados = (int) $m->puntos_canjeados;
        $v->puntosGanados = (int) $m->puntos_ganados;

        return $v;
    }
}
