<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Promociones\Ports\Out\CuponRepository;
use App\Domain\Promociones\Cupon;
use App\Domain\Promociones\TipoPromocion;
use App\Infrastructure\Persistence\Eloquent\Mapeador;
use App\Infrastructure\Persistence\Eloquent\Models\CuponModel;

class EloquentCuponRepository implements CuponRepository
{
    public function buscarPorToken(string $token): ?Cupon
    {
        $m = CuponModel::where('token', $token)->first();

        return $m ? $this->aDominio($m) : null;
    }

    public function canjeadosEnVenta(int $ventaId): array
    {
        return CuponModel::where('venta_canje_id', $ventaId)->where('estado', Cupon::CANJEADO)->get()
            ->map(fn ($m) => $this->aDominio($m))->all();
    }

    public function deFactura(int $facturaId): ?Cupon
    {
        $m = CuponModel::where('factura_id', $facturaId)->first();

        return $m ? $this->aDominio($m) : null;
    }

    public function guardar(Cupon $c): Cupon
    {
        $m = CuponModel::updateOrCreate(['token' => $c->token], [
            'promocion_id' => $c->promocionId, 'factura_id' => $c->facturaId, 'cliente_id' => $c->clienteId,
            'estado' => $c->estado, 'fecha_vencimiento' => $c->fechaVencimiento->format('Y-m-d'),
            'venta_canje_id' => $c->ventaCanjeId, 'canjeado_en' => $c->canjeadoEn,
        ]);

        return $this->aDominio($m->fresh());
    }

    public function listar(): array
    {
        return CuponModel::with(['promocion', 'factura', 'cliente'])->orderByDesc('id')->limit(300)->get()->map(fn ($c) => [
            'token' => $c->token, 'promocion' => $c->promocion->nombre,
            'tipo' => TipoPromocion::from($c->promocion->tipo)->etiqueta(),
            'factura' => $c->factura->serie.'-'.str_pad($c->factura->numero, 6, '0', STR_PAD_LEFT),
            'cliente' => $c->cliente?->nombre ?? $c->factura->nombre, 'estado' => $c->estado,
            'vence' => $c->fecha_vencimiento->format('d/m/Y'), 'canjeado_en' => $c->canjeado_en?->format('d/m/Y H:i'),
        ])->all();
    }

    private function aDominio(CuponModel $m): Cupon
    {
        return new Cupon($m->id, $m->token, $m->promocion_id, $m->factura_id, $m->cliente_id, $m->estado,
            Mapeador::fecha($m->fecha_vencimiento), $m->venta_canje_id, Mapeador::fecha($m->canjeado_en));
    }
}
