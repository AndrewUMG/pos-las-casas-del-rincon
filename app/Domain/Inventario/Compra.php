<?php

namespace App\Domain\Inventario;

use App\Domain\Shared\Dinero;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Compra de mercadería a un proveedor: aumenta las existencias.
 */
class Compra
{
    /** @var array<int, array{producto_id:int, cantidad:int, costo_unitario:float, subtotal:float}> */
    private array $lineas = [];

    public function __construct(
        public readonly ?int $id,
        public int $proveedorId,
        public int $usuarioId,
        public string $numeroDocumento,
        public \DateTimeImmutable $fecha,
    ) {
        if (trim($numeroDocumento) === '') {
            throw new ReglaNegocioException('El número de documento de la compra es obligatorio.');
        }
    }

    public function agregarLinea(int $productoId, int $cantidad, float $costoUnitario): void
    {
        if ($cantidad <= 0 || $costoUnitario <= 0) {
            throw new ReglaNegocioException('Cada producto comprado debe tener cantidad y costo mayores a cero.');
        }
        $this->lineas[] = [
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'costo_unitario' => Dinero::redondear($costoUnitario),
            'subtotal' => Dinero::redondear($cantidad * $costoUnitario),
        ];
    }

    public function lineas(): array
    {
        return $this->lineas;
    }

    public function total(): float
    {
        return Dinero::redondear(array_sum(array_column($this->lineas, 'subtotal')));
    }

    public function validar(): void
    {
        if ($this->lineas === []) {
            throw new ReglaNegocioException('La compra debe tener al menos un producto.');
        }
    }
}
