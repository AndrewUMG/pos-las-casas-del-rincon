<?php

namespace App\Domain\Promociones;

enum TipoPromocion: string
{
    case ProductoGratuito = 'producto_gratuito';
    case ValeRegalo = 'vale_regalo';
    case SegundoMitad = 'segundo_mitad';
    case Mayoreo = 'mayoreo';
    case Puntos = 'puntos';

    public function etiqueta(): string
    {
        return match ($this) {
            self::ProductoGratuito => 'Producto gratuito',
            self::ValeRegalo => 'Vale de regalo',
            self::SegundoMitad => 'Segundo producto a mitad de precio',
            self::Mayoreo => 'Compra por mayoreo',
            self::Puntos => 'Puntos por producto',
        };
    }
}
