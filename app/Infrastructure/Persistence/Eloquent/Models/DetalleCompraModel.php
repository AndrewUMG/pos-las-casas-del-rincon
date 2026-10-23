<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompraModel extends Model
{
    protected $table = 'detalle_compras';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'costo_unitario' => 'float',
            'subtotal' => 'float',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'producto_id');
    }
}
