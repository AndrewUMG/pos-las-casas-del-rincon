<?php

namespace App\Domain\Catalogos;

use App\Domain\Shared\ReglaNegocioException;

class Proveedor
{
    public function __construct(
        public readonly ?int $id,
        public string $nit,
        public string $nombre,
        public ?string $contacto = null,
        public ?string $telefono = null,
        public ?string $direccion = null,
    ) {
        $this->nit = strtoupper(trim($nit));
        if ($this->nit === '' || trim($nombre) === '') {
            throw new ReglaNegocioException('El NIT y el nombre del proveedor son obligatorios.');
        }
    }
}
