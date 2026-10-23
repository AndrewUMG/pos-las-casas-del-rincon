<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Inventario\Ports\Out\MovimientoRepository;
use App\Domain\Inventario\TipoMovimiento;
use App\Infrastructure\Persistence\Eloquent\Models\MovimientoInventarioModel;

class EloquentMovimientoRepository implements MovimientoRepository
{
    public function registrar(int $productoId, TipoMovimiento $tipo, int $cantidad, int $existenciaResultante,
        string $referencia, string $descripcion, \DateTimeImmutable $fecha): void
    {
        MovimientoInventarioModel::create([
            'producto_id' => $productoId, 'tipo' => $tipo->value, 'cantidad' => $cantidad,
            'existencia_resultante' => $existenciaResultante, 'referencia' => $referencia,
            'descripcion' => $descripcion, 'fecha' => $fecha,
        ]);
    }

    public function kardex(int $productoId): array
    {
        return MovimientoInventarioModel::where('producto_id', $productoId)->orderBy('fecha')->orderBy('id')->get()
            ->map(fn ($m) => [
                'fecha' => $m->fecha->format('d/m/Y H:i'), 'tipo' => TipoMovimiento::from($m->tipo)->etiqueta(),
                'tipo_clave' => $m->tipo, 'cantidad' => $m->cantidad, 'existencia' => $m->existencia_resultante,
                'referencia' => $m->referencia, 'descripcion' => $m->descripcion,
            ])->all();
    }
}
