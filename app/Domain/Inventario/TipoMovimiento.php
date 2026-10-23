<?php

namespace App\Domain\Inventario;

/**
 * Tipos de movimiento del kardex.
 */
enum TipoMovimiento: string
{
    case Entrada = 'entrada';       // compra a proveedor
    case Salida = 'salida';         // venta
    case Baja = 'baja';             // devolución por daño o vencimiento

    public function etiqueta(): string
    {
        return match ($this) {
            self::Entrada => 'Entrada',
            self::Salida => 'Salida',
            self::Baja => 'Baja',
        };
    }
}
