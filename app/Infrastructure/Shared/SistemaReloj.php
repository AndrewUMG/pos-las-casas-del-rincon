<?php

namespace App\Infrastructure\Shared;

use App\Application\Shared\Ports\Reloj;

class SistemaReloj implements Reloj
{
    public function ahora(): \DateTimeImmutable
    {
        return now()->toDateTimeImmutable();
    }
}
