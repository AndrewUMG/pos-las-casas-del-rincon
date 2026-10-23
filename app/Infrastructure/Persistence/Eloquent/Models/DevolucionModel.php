<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class DevolucionModel extends Model
{
    protected $table = 'devoluciones';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'total' => 'float',
        ];
    }

    public function detalles()
    {
        return $this->hasMany(DetalleDevolucionModel::class, 'devolucion_id');
    }

    public function factura()
    {
        return $this->belongsTo(FacturaModel::class, 'factura_id');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }
}
