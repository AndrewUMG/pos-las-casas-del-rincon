<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Caja\Ports\Out\MovimientoCajaRepository;
use App\Infrastructure\Persistence\Eloquent\Models\MovimientoCajaModel;

class EloquentMovimientoCajaRepository implements MovimientoCajaRepository
{
    public function registrar(int $cajaId, string $tipo, string $metodo, float $monto, string $descripcion,
        string $referencia, \DateTimeImmutable $fecha): void
    {
        MovimientoCajaModel::create([
            'caja_id' => $cajaId, 'tipo' => $tipo, 'metodo_pago' => $metodo, 'monto' => $monto,
            'descripcion' => $descripcion, 'referencia' => $referencia, 'fecha' => $fecha,
        ]);
    }

    public function totales(int $cajaId): array
    {
        $totales = ['ingreso' => [], 'egreso' => []];
        MovimientoCajaModel::where('caja_id', $cajaId)
            ->selectRaw('tipo, metodo_pago, sum(monto) as total')->groupBy('tipo', 'metodo_pago')->get()
            ->each(function ($r) use (&$totales) {
                $totales[$r->tipo][$r->metodo_pago] = round((float) $r->total, 2);
            });

        return $totales;
    }

    public function listar(int $cajaId): array
    {
        return MovimientoCajaModel::where('caja_id', $cajaId)->orderBy('fecha')->orderBy('id')->get()->map(fn ($m) => [
            'fecha' => $m->fecha->format('d/m/Y H:i'), 'tipo' => $m->tipo, 'metodo' => $m->metodo_pago,
            'monto' => $m->monto, 'descripcion' => $m->descripcion, 'referencia' => $m->referencia,
        ])->all();
    }
}
