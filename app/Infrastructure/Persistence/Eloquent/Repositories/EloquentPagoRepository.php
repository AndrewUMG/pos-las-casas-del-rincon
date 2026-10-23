<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Cobros\Ports\Out\PagoRepository;
use App\Domain\Cobros\MetodoPago;
use App\Domain\Cobros\Pago;
use App\Infrastructure\Persistence\Eloquent\Models\PagoModel;

class EloquentPagoRepository implements PagoRepository
{
    public function guardar(Pago $p): void
    {
        PagoModel::create([
            'venta_id' => $p->ventaId, 'metodo' => $p->metodo->value, 'monto' => $p->monto,
            'monto_recibido' => $p->montoRecibido, 'cambio' => $p->cambio,
            'referencia_voucher' => $p->referenciaVoucher, 'fecha' => $p->fecha,
        ]);
    }

    public function deVenta(int $ventaId): ?array
    {
        $p = PagoModel::where('venta_id', $ventaId)->first();

        return $p ? [
            'metodo' => $p->metodo, 'metodo_etiqueta' => MetodoPago::from($p->metodo)->etiqueta(), 'monto' => $p->monto,
            'monto_recibido' => $p->monto_recibido, 'cambio' => $p->cambio, 'referencia_voucher' => $p->referencia_voucher,
        ] : null;
    }
}
