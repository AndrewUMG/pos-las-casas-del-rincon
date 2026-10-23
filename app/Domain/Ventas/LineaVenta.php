<?php

namespace App\Domain\Ventas;

use App\Domain\Shared\Dinero;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Línea del despacho: un producto leído con su cantidad, precio y descuento.
 */
class LineaVenta
{
    public float $descuento = 0;

    public ?string $promocion = null;

    public function __construct(
        public readonly int $productoId,
        public readonly string $codigo,
        public readonly string $nombre,
        public readonly int $familiaId,
        public readonly float $precioUnitario,
        public readonly ?float $precioMayoreo,
        public int $cantidad,
    ) {
        if ($cantidad <= 0) {
            throw new ReglaNegocioException('La cantidad debe ser mayor a cero.');
        }
    }

    public function importeBruto(): float
    {
        return Dinero::redondear($this->cantidad * $this->precioUnitario);
    }

    /** Subtotal de línea = cantidad × precio unitario − descuento. */
    public function subtotal(): float
    {
        return Dinero::redondear($this->importeBruto() - $this->descuento);
    }

    public function agregarDescuento(float $monto, string $promocion): void
    {
        $monto = Dinero::redondear(min($monto, $this->importeBruto() - $this->descuento));
        if ($monto <= 0) {
            return;
        }
        $this->descuento = Dinero::redondear($this->descuento + $monto);
        $this->promocion = $this->promocion ? $this->promocion.' · '.$promocion : $promocion;
    }

    public function limpiarDescuentos(): void
    {
        $this->descuento = 0;
        $this->promocion = null;
    }
}
