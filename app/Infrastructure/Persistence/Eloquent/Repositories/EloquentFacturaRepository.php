<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Facturacion\Ports\Out\FacturaRepository;
use App\Domain\Cobros\MetodoPago;
use App\Domain\Facturacion\Factura;
use App\Infrastructure\Persistence\Eloquent\Models\FacturaModel;

class EloquentFacturaRepository implements FacturaRepository
{
    public function siguienteNumero(string $serie): int
    {
        // La restricción única (serie, numero) protege contra números duplicados.
        return (int) FacturaModel::where('serie', $serie)->max('numero') + 1;
    }

    public function guardar(Factura $f): int
    {
        return FacturaModel::create([
            'venta_id' => $f->ventaId, 'serie' => $f->serie, 'numero' => $f->numero, 'fecha' => $f->fecha,
            'nit' => $f->nit, 'nombre' => $f->nombre, 'direccion' => $f->direccion, 'total' => $f->total,
        ])->id;
    }

    public function obtener(int $id): ?array
    {
        $f = FacturaModel::with(['venta.detalles.producto', 'venta.pago', 'venta.usuario'])->find($id);

        return $f ? $this->aArreglo($f) : null;
    }

    public function buscarPorNumero(string $serie, int $numero): ?array
    {
        $id = FacturaModel::where('serie', strtoupper($serie))->where('numero', $numero)->value('id');

        return $id ? $this->obtener($id) : null;
    }

    public function deVenta(int $ventaId): ?int
    {
        return FacturaModel::where('venta_id', $ventaId)->value('id');
    }

    public function listar(): array
    {
        return FacturaModel::with('venta.pago')->orderByDesc('id')->limit(300)->get()->map(fn ($f) => [
            'id' => $f->id, 'numero_completo' => $this->numero($f), 'fecha' => $f->fecha->format('d/m/Y H:i'),
            'nit' => $f->nit, 'nombre' => $f->nombre, 'total' => $f->total,
            'metodo' => $f->venta->pago ? MetodoPago::from($f->venta->pago->metodo)->etiqueta() : '',
        ])->all();
    }

    private function aArreglo(FacturaModel $f): array
    {
        $v = $f->venta;
        $pago = $v->pago;

        return [
            'id' => $f->id, 'venta_id' => $v->id, 'serie' => $f->serie, 'numero' => $f->numero,
            'numero_completo' => $this->numero($f), 'fecha' => $f->fecha->format('d/m/Y H:i'),
            'nit' => $f->nit, 'nombre' => $f->nombre, 'direccion' => $f->direccion,
            'subtotal' => $v->subtotal, 'descuento' => $v->descuento, 'descuento_vale' => (float) $v->descuento_vale,
            'descuento_puntos' => (float) $v->descuento_puntos, 'puntos_canjeados' => $v->puntos_canjeados,
            'puntos_ganados' => $v->puntos_ganados, 'total' => $f->total, 'cajero' => $v->usuario->nombre,
            'pago' => $pago ? [
                'metodo' => MetodoPago::from($pago->metodo)->etiqueta(), 'monto_recibido' => $pago->monto_recibido,
                'cambio' => $pago->cambio, 'referencia_voucher' => $pago->referencia_voucher,
            ] : null,
            'lineas' => $v->detalles->map(fn ($d) => [
                'producto_id' => $d->producto_id, 'codigo' => $d->producto->codigo, 'nombre' => $d->producto->nombre,
                'cantidad' => $d->cantidad, 'precio_unitario' => $d->precio_unitario, 'descuento' => $d->descuento,
                'subtotal' => $d->subtotal, 'promocion' => $d->promocion,
            ])->all(),
        ];
    }

    private function numero(FacturaModel $f): string
    {
        return $f->serie.'-'.str_pad((string) $f->numero, 6, '0', STR_PAD_LEFT);
    }
}
