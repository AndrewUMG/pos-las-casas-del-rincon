<?php

namespace App\Domain\Shared;

/**
 * Utilidades para montos en quetzales (dos decimales).
 */
final class Dinero
{
    public static function redondear(float $monto): float
    {
        return round($monto + 0.0, 2);
    }

    public static function formato(float $monto): string
    {
        return 'Q '.number_format($monto, 2, '.', ',');
    }
}
