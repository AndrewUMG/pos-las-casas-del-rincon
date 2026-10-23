<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Caja\Ports\Out\CajaRepository;
use App\Domain\Caja\Caja;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\CajaModel;

class EloquentCajaRepository implements CajaRepository
{
    public function abiertaDe(int $usuarioId): ?Caja
    {
        $m = CajaModel::where('usuario_id', $usuarioId)->where('estado', Caja::ABIERTA)->latest('id')->first();

        return $m ? $this->aDominio($m) : null;
    }

    public function buscarPorId(int $id): ?Caja
    {
        $m = CajaModel::find($id);

        return $m ? $this->aDominio($m) : null;
    }

    public function guardar(Caja $c): Caja
    {
        $m = Mapeador::persistir(CajaModel::class, $c->id, [
            'usuario_id' => $c->usuarioId, 'fecha_apertura' => $c->fechaApertura, 'monto_inicial' => $c->montoInicial,
            'estado' => $c->estado, 'fecha_cierre' => $c->fechaCierre, 'monto_esperado' => $c->montoEsperado,
            'monto_contado' => $c->montoContado, 'diferencia' => $c->diferencia,
        ]);

        return $this->aDominio($m->fresh());
    }

    public function historial(int $usuarioId): array
    {
        return CajaModel::where('usuario_id', $usuarioId)->orderByDesc('id')->limit(20)->get()->map(fn ($c) => [
            'id' => $c->id, 'apertura' => $c->fecha_apertura->format('d/m/Y H:i'),
            'cierre' => $c->fecha_cierre?->format('d/m/Y H:i'), 'monto_inicial' => $c->monto_inicial,
            'monto_esperado' => $c->monto_esperado, 'monto_contado' => $c->monto_contado,
            'diferencia' => $c->diferencia, 'estado' => $c->estado,
        ])->all();
    }

    private function aDominio(CajaModel $m): Caja
    {
        return new Caja($m->id, $m->usuario_id, Mapeador::fecha($m->fecha_apertura), (float) $m->monto_inicial, $m->estado,
            Mapeador::fecha($m->fecha_cierre), $m->monto_esperado, $m->monto_contado, $m->diferencia);
    }
}
