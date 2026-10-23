<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Devoluciones\Ports\Out\DevolucionRepository;
use App\Domain\Devoluciones\Devolucion;
use App\Domain\Devoluciones\MotivoDevolucion;
use App\Infrastructure\Persistence\Eloquent\Models\DevolucionModel;
use Illuminate\Support\Facades\DB;

class EloquentDevolucionRepository implements DevolucionRepository
{
    public function guardar(Devolucion $d): int
    {
        $m = DevolucionModel::create([
            'factura_id' => $d->facturaId, 'usuario_id' => $d->usuarioId, 'caja_id' => $d->cajaId, 'fecha' => $d->fecha,
            'motivo' => $d->motivo->value, 'observacion' => $d->observacion, 'total' => $d->total(),
        ]);
        $m->detalles()->createMany(array_map(fn ($l) => [
            'producto_id' => $l['producto_id'], 'cantidad' => $l['cantidad'],
            'precio_unitario' => $l['precio_unitario'], 'subtotal' => $l['subtotal'],
        ], $d->lineas()));

        return $m->id;
    }

    public function devueltoPorProducto(int $facturaId): array
    {
        return DB::table('detalle_devoluciones')
            ->join('devoluciones', 'devoluciones.id', '=', 'detalle_devoluciones.devolucion_id')
            ->where('devoluciones.factura_id', $facturaId)
            ->groupBy('detalle_devoluciones.producto_id')
            ->selectRaw('detalle_devoluciones.producto_id, sum(detalle_devoluciones.cantidad) as cantidad')
            ->pluck('cantidad', 'producto_id')->map(fn ($c) => (int) $c)->all();
    }

    public function listar(): array
    {
        return DevolucionModel::with(['factura', 'usuario'])->withCount('detalles')->orderByDesc('id')->limit(300)->get()
            ->map(fn ($d) => $this->encabezado($d))->all();
    }

    public function obtener(int $id): ?array
    {
        $d = DevolucionModel::with(['factura', 'usuario', 'detalles.producto'])->find($id);
        if (! $d) {
            return null;
        }

        return $this->encabezado($d) + [
            'observacion' => $d->observacion,
            'lineas' => $d->detalles->map(fn ($l) => [
                'codigo' => $l->producto->codigo, 'nombre' => $l->producto->nombre, 'cantidad' => $l->cantidad,
                'precio_unitario' => $l->precio_unitario, 'subtotal' => $l->subtotal,
            ])->all(),
        ];
    }

    private function encabezado(DevolucionModel $d): array
    {
        return [
            'id' => $d->id, 'fecha' => $d->fecha->format('d/m/Y H:i'),
            'factura' => $d->factura->serie.'-'.str_pad((string) $d->factura->numero, 6, '0', STR_PAD_LEFT),
            'cliente' => $d->factura->nombre, 'motivo' => MotivoDevolucion::from($d->motivo)->etiqueta(),
            'usuario' => $d->usuario->nombre, 'total' => $d->total,
        ];
    }
}
