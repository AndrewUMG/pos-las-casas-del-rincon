<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVentaModel extends Model
{
    protected $table = 'detalle_ventas';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'float',
            'descuento' => 'float',
            'subtotal' => 'float',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'producto_id');
    }
}
