<?php

use App\Domain\Shared\Dinero;

if (! function_exists('q')) {
    /** Formatea un monto en quetzales: Q 1,234.50 */
    function q(float|int|string|null $monto): string
    {
        return Dinero::formato((float) $monto);
    }
}
