<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleDevolucionModel extends Model
{
    protected $table = 'detalle_devoluciones';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'float',
            'subtotal' => 'float',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'producto_id');
    }
}
