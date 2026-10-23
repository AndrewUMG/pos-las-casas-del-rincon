<?php

namespace App\Application\Devoluciones\Services;

use App\Application\Devoluciones\Ports\In\ConsultarFacturaUseCase;
use App\Application\Devoluciones\Ports\In\ProcesarDevolucionUseCase;
use App\Application\Devoluciones\Ports\Out\DevolucionRepository;
use App\Application\Facturacion\Ports\Out\FacturaRepository;
use App\Application\Shared\Ports\CajaPort;
use App\Application\Shared\Ports\InventarioPort;
use App\Application\Shared\Ports\Reloj;
use App\Application\Shared\Ports\Transaccion;
use App\Domain\Devoluciones\Devolucion;
use App\Domain\Devoluciones\MotivoDevolucion;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Devolución de producto por daño o vencimiento: se registra la baja en el kardex
 * y el reembolso como egreso de la caja abierta.
 */
class DevolucionesService implements ProcesarDevolucionUseCase, ConsultarFacturaUseCase
{
    public function __construct(
        private DevolucionRepository $devoluciones,
        private FacturaRepository $facturas,
        private InventarioPort $inventario,
        private CajaPort $caja,
        private Transaccion $transaccion,
        private Reloj $reloj,
        private string $serie,
    ) {
    }

    /** Busca por «A-000015», «A-15» o solo el número. */
    public function buscar(string $numero): ?array
    {
        $numero = strtoupper(trim($numero));
        [$serie, $num] = str_contains($numero, '-') ? explode('-', $numero, 2) : [$this->serie, $numero];
        if (! ctype_digit($num)) {
            return null;
        }
        $factura = $this->facturas->buscarPorNumero($serie, (int) $num);

        return $factura ? $this->conDisponibles($factura) : null;
    }

    public function procesar(int $facturaId, int $usuarioId, string $motivo, ?string $observacion, array $cantidades): int
    {
        $cajaId = $this->caja->cajaAbiertaDe($usuarioId)
            ?? throw new ReglaNegocioException('Debe tener la caja abierta para registrar el reembolso.');
        $motivoDev = MotivoDevolucion::tryFrom($motivo) ?? throw new ReglaNegocioException('Indique el motivo: daño o vencimiento.');
        $factura = $this->conDisponibles($this->facturas->obtener($facturaId) ?? throw new ReglaNegocioException('La factura no existe.'));

        $devolucion = new Devolucion(null, $facturaId, $usuarioId, $cajaId, $motivoDev, $observacion, $this->reloj->ahora());
        foreach ($factura['lineas'] as $l) {
            $devolucion->agregarProducto($l['producto_id'], $l['nombre'], (int) ($cantidades[$l['producto_id']] ?? 0),
                $l['disponible'], $l['precio_efectivo']);
        }
        $devolucion->validar();

        return $this->transaccion->ejecutar(function () use ($devolucion, $factura, $cajaId, $motivoDev) {
            $id = $this->devoluciones->guardar($devolucion);
            $ref = "DEV-{$id}";
            foreach ($devolucion->lineas() as $l) {
                $this->inventario->darDeBajaPorDevolucion($l['producto_id'], $l['cantidad'], $ref, $motivoDev->etiqueta());
            }
            if ($devolucion->total() > 0) {
                $this->caja->registrarEgreso($cajaId, 'efectivo', $devolucion->total(),
                    "Devolución #{$id} – factura {$factura['numero_completo']}", $ref);
            }

            return $id;
        });
    }

    public function listar(): array
    {
        return $this->devoluciones->listar();
    }

    public function obtener(int $id): array
    {
        return $this->devoluciones->obtener($id) ?? throw new ReglaNegocioException('La devolución no existe.');
    }

    private function conDisponibles(array $factura): array
    {
        $devuelto = $this->devoluciones->devueltoPorProducto($factura['id']);
        foreach ($factura['lineas'] as &$l) {
            $l['devuelto'] = $devuelto[$l['producto_id']] ?? 0;
            $l['disponible'] = $l['cantidad'] - $l['devuelto'];
            // Precio efectivamente pagado por unidad (después de descuentos de línea).
            $l['precio_efectivo'] = round($l['subtotal'] / $l['cantidad'], 2);
        }

        return $factura;
    }
}
