<?php

namespace App\Domain\Promociones;

use App\Domain\Shared\ReglaNegocioException;

/**
 * Cupón entregado con el código QR de la factura. Es válido una sola vez y dentro de su vigencia.
 */
class Cupon
{
    public const EMITIDO = 'emitido';
    public const CANJEADO = 'canjeado';

    public function __construct(
        public readonly ?int $id,
        public readonly string $token,
        public readonly int $promocionId,
        public readonly int $facturaId,
        public ?int $clienteId,
        public string $estado,
        public \DateTimeImmutable $fechaVencimiento,
        public ?int $ventaCanjeId = null,
        public ?\DateTimeImmutable $canjeadoEn = null,
    ) {
    }

    public static function emitir(int $promocionId, int $facturaId, ?int $clienteId, \DateTimeImmutable $hoy, int $diasVigencia): self
    {
        $token = strtoupper(bin2hex(random_bytes(6)));

        return new self(null, $token, $promocionId, $facturaId, $clienteId, self::EMITIDO, $hoy->modify("+{$diasVigencia} days"));
    }

    public function vencido(\DateTimeImmutable $hoy): bool
    {
        return $hoy->format('Y-m-d') > $this->fechaVencimiento->format('Y-m-d');
    }

    public function canjear(int $ventaId, \DateTimeImmutable $ahora): void
    {
        if ($this->estado !== self::EMITIDO) {
            throw new ReglaNegocioException('Este cupón ya fue canjeado.');
        }
        if ($this->vencido($ahora)) {
            throw new ReglaNegocioException('El cupón está vencido.');
        }
        $this->estado = self::CANJEADO;
        $this->ventaCanjeId = $ventaId;
        $this->canjeadoEn = $ahora;
    }

    /** Si la venta se cancela antes de cobrarse, el cupón vuelve a estar disponible. */
    public function liberar(): void
    {
        $this->estado = self::EMITIDO;
        $this->ventaCanjeId = null;
        $this->canjeadoEn = null;
    }
}
