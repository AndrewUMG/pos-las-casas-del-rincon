<?php

namespace App\Domain\Catalogos;

use App\Domain\Shared\ReglaNegocioException;

/**
 * Producto del catálogo. Todo producto pertenece a una familia
 * (Pastas, Cárnicos, Embutidos, Lácteos, Golosinas, Aguas gaseosas, Carbonatados).
 */
class Producto
{
    public function __construct(
        public readonly ?int $id,
        public string $codigo,
        public string $nombre,
        public int $familiaId,
        public ?int $proveedorId,
        public float $precio,
        public ?float $precioMayoreo = null,
        public int $existencia = 0,
        public bool $activo = true,
        public ?string $familiaNombre = null,
    ) {
        $this->codigo = trim($codigo);
        if ($this->codigo === '' || trim($nombre) === '') {
            throw new ReglaNegocioException('El código y el nombre del producto son obligatorios.');
        }
        $this->validarPrecios($precio, $precioMayoreo);
        if ($existencia < 0) {
            throw new ReglaNegocioException("La existencia de {$this->nombre} no puede quedar negativa.");
        }
    }

    public function cambiarPrecios(float $precio, ?float $precioMayoreo): void
    {
        $this->validarPrecios($precio, $precioMayoreo);
        $this->precio = $precio;
        $this->precioMayoreo = $precioMayoreo;
    }

    public function aumentarExistencia(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new ReglaNegocioException('La cantidad a ingresar debe ser mayor a cero.');
        }
        $this->existencia += $cantidad;
    }

    public function disminuirExistencia(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new ReglaNegocioException('La cantidad a descontar debe ser mayor a cero.');
        }
        if ($cantidad > $this->existencia) {
            throw new ReglaNegocioException("Existencia insuficiente de {$this->nombre}: disponible {$this->existencia}.");
        }
        $this->existencia -= $cantidad;
    }

    private function validarPrecios(float $precio, ?float $precioMayoreo): void
    {
        if ($precio <= 0) {
            throw new ReglaNegocioException('El precio debe ser mayor a cero.');
        }
        if ($precioMayoreo !== null && ($precioMayoreo <= 0 || $precioMayoreo > $precio)) {
            throw new ReglaNegocioException('El precio de mayoreo debe ser mayor a cero y no superar el precio normal.');
        }
    }
}
