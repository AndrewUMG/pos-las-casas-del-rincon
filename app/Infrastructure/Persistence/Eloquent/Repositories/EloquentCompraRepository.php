<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Inventario\Ports\Out\CompraRepository;
use App\Domain\Inventario\Compra;
use App\Infrastructure\Persistence\Eloquent\Models\CompraModel;

class EloquentCompraRepository implements CompraRepository
{
    public function guardar(Compra $compra): int
    {
        $m = CompraModel::create([
            'proveedor_id' => $compra->proveedorId, 'usuario_id' => $compra->usuarioId,
            'numero_documento' => $compra->numeroDocumento, 'fecha' => $compra->fecha->format('Y-m-d'),
            'total' => $compra->total(),
        ]);
        $m->detalles()->createMany($compra->lineas());

        return $m->id;
    }

    public function listar(): array
    {
        return CompraModel::with(['proveedor', 'usuario'])->withCount('detalles')->orderByDesc('fecha')->orderByDesc('id')
            ->limit(300)->get()->map(fn ($c) => [
                'id' => $c->id, 'fecha' => $c->fecha->format('d/m/Y'), 'numero_documento' => $c->numero_documento,
                'proveedor' => $c->proveedor->nombre, 'usuario' => $c->usuario->nombre,
                'productos' => $c->detalles_count, 'total' => $c->total,
            ])->all();
    }

    public function obtener(int $id): ?array
    {
        $c = CompraModel::with(['proveedor', 'usuario', 'detalles.producto'])->find($id);
        if (! $c) {
            return null;
        }

        return [
            'id' => $c->id, 'fecha' => $c->fecha->format('d/m/Y'), 'numero_documento' => $c->numero_documento,
            'proveedor' => $c->proveedor->nombre, 'usuario' => $c->usuario->nombre, 'total' => $c->total,
            'lineas' => $c->detalles->map(fn ($d) => [
                'codigo' => $d->producto->codigo, 'nombre' => $d->producto->nombre, 'cantidad' => $d->cantidad,
                'costo_unitario' => $d->costo_unitario, 'subtotal' => $d->subtotal,
            ])->all(),
        ];
    }
}
