<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteModel extends Model
{
    protected $table = 'clientes';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'puntos' => 'integer',
        ];
    }
}
