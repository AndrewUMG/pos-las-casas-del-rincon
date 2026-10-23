<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class CuponModel extends Model
{
    protected $table = 'cupones';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'canjeado_en' => 'datetime',
        ];
    }

    public function promocion()
    {
        return $this->belongsTo(PromocionModel::class, 'promocion_id');
    }

    public function factura()
    {
        return $this->belongsTo(FacturaModel::class, 'factura_id');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteModel::class, 'cliente_id');
    }
}
