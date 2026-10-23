<?php

namespace App\Infrastructure\Shared;

use App\Application\Shared\Ports\Transaccion;
use Illuminate\Support\Facades\DB;

class LaravelTransaccion implements Transaccion
{
    public function ejecutar(callable $trabajo): mixed
    {
        return DB::transaction(fn () => $trabajo());
    }
}
