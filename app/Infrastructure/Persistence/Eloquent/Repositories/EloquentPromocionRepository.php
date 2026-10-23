<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Promociones\Ports\Out\PromocionRepository;
use App\Domain\Promociones\Promocion;
use App\Domain\Promociones\TipoPromocion;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\PromocionModel;

class EloquentPromocionRepository implements PromocionRepository
{
    public function listar(): array
    {
        return PromocionModel::with(['producto', 'familia'])->orderByDesc('activa')->orderBy('nombre')->get()
            ->map(fn ($m) => $this->aDominio($m))->all();
    }

    public function buscarPorId(int $id): ?Promocion
    {
        $m = PromocionModel::with(['producto', 'familia'])->find($id);

        return $m ? $this->aDominio($m) : null;
    }

    public function vigentes(string $modo, \DateTimeImmutable $hoy): array
    {
        $d = $hoy->format('Y-m-d');

        return PromocionModel::with(['producto', 'familia'])->where('activa', true)->where('modo', $modo)
            ->whereDate('fecha_inicio', '<=', $d)->whereDate('fecha_fin', '>=', $d)->orderBy('id')->get()
            ->map(fn ($m) => $this->aDominio($m))->all();
    }

    public function guardar(Promocion $p): Promocion
    {
        $m = Mapeador::persistir(PromocionModel::class, $p->id, [
            'nombre' => $p->nombre, 'tipo' => $p->tipo->value, 'modo' => $p->modo, 'producto_id' => $p->productoId,
            'familia_id' => $p->familiaId, 'cantidad_minima' => $p->cantidadMinima, 'valor' => $p->valor,
            'descripcion' => $p->descripcion, 'fecha_inicio' => $p->fechaInicio->format('Y-m-d'),
            'fecha_fin' => $p->fechaFin->format('Y-m-d'), 'activa' => $p->activa,
        ]);

        return $this->buscarPorId($m->id);
    }

    private function aDominio(PromocionModel $m): Promocion
    {
        return new Promocion($m->id, $m->nombre, TipoPromocion::from($m->tipo), $m->modo, $m->producto_id, $m->familia_id,
            (int) $m->cantidad_minima, (float) $m->valor, $m->descripcion, Mapeador::fecha($m->fecha_inicio),
            Mapeador::fecha($m->fecha_fin), (bool) $m->activa, $m->producto?->nombre, $m->familia?->nombre);
    }
}
