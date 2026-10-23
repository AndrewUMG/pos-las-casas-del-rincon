<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class VentaModel extends Model
{
    protected $table = 'ventas';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'subtotal' => 'float',
            'descuento' => 'float',
            'total' => 'float',
        ];
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVentaModel::class, 'venta_id');
    }

    public function pago()
    {
        return $this->hasOne(PagoModel::class, 'venta_id');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteModel::class, 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }
}
