<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class CajaModel extends Model
{
    protected $table = 'cajas';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'monto_inicial' => 'float',
            'monto_esperado' => 'float',
            'monto_contado' => 'float',
            'diferencia' => 'float',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCajaModel::class, 'caja_id');
    }
}
