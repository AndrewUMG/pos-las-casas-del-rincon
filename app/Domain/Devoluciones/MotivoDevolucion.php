<?php

namespace App\Domain\Devoluciones;

enum MotivoDevolucion: string
{
    case Dano = 'dano';
    case Vencimiento = 'vencimiento';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Dano => 'Daño',
            self::Vencimiento => 'Vencimiento',
        };
    }
}
