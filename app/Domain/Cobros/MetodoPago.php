<?php

namespace App\Domain\Cobros;

enum MetodoPago: string
{
    case Efectivo = 'efectivo';
    case Credito = 'credito';
    case Debito = 'debito';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Efectivo => 'Efectivo',
            self::Credito => 'Tarjeta de crédito',
            self::Debito => 'Tarjeta de débito',
        };
    }
}
