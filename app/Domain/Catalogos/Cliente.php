<?php

namespace App\Domain\Catalogos;

use App\Domain\Shared\ReglaNegocioException;

class Cliente
{
    public const CONSUMIDOR_FINAL = 'CF';

    public function __construct(
        public readonly ?int $id,
        public string $nit,
        public string $nombre,
        public ?string $direccion = null,
        public ?string $telefono = null,
        public ?string $email = null,
        public int $puntos = 0,
    ) {
        $this->nit = strtoupper(trim($nit));
        if ($this->nit === '' || trim($nombre) === '') {
            throw new ReglaNegocioException('El NIT y el nombre del cliente son obligatorios.');
        }
        if ($puntos < 0) {
            throw new ReglaNegocioException('Los puntos del cliente no pueden ser negativos.');
        }
    }

    public function acumularPuntos(int $puntos): void
    {
        $this->puntos += max(0, $puntos);
    }

    public function canjearPuntos(int $puntos): void
    {
        if ($puntos <= 0) {
            throw new ReglaNegocioException('Indique una cantidad de puntos válida.');
        }
        if ($puntos > $this->puntos) {
            throw new ReglaNegocioException("El cliente solo tiene {$this->puntos} puntos disponibles.");
        }
        $this->puntos -= $puntos;
    }
}
