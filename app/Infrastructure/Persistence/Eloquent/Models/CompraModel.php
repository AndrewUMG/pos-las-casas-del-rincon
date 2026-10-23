<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class CompraModel extends Model
{
    protected $table = 'compras';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total' => 'float',
            'fecha' => 'date',
        ];
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompraModel::class, 'compra_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(ProveedorModel::class, 'proveedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }
}
