<?php

namespace App\Domain\Facturacion;

use App\Domain\Catalogos\Cliente;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Factura con los datos del cliente y el detalle de la venta.
 * Si el cliente no da NIT se usa «CF» (consumidor final).
 */
class Factura
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $ventaId,
        public readonly string $serie,
        public readonly int $numero,
        public readonly \DateTimeImmutable $fecha,
        public readonly string $nit,
        public readonly string $nombre,
        public readonly ?string $direccion,
        public readonly float $total,
    ) {
    }

    public static function emitir(int $ventaId, string $serie, int $numero, ?string $nit, ?string $nombre,
        ?string $direccion, float $total, \DateTimeImmutable $ahora): self
    {
        $nit = strtoupper(trim((string) $nit)) ?: Cliente::CONSUMIDOR_FINAL;
        $nombre = trim((string) $nombre);
        if ($nombre === '') {
            if ($nit !== Cliente::CONSUMIDOR_FINAL) {
                throw new ReglaNegocioException('Ingrese el nombre del cliente.');
            }
            $nombre = 'Consumidor Final';
        }

        return new self(null, $ventaId, $serie, $numero, $ahora, $nit, $nombre, $direccion ?: 'Ciudad', $total);
    }

    public function numeroCompleto(): string
    {
        return $this->serie.'-'.str_pad((string) $this->numero, 6, '0', STR_PAD_LEFT);
    }
}
