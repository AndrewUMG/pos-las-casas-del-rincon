<?php

namespace App\Application\Facturacion\Services;

use App\Application\Catalogos\Ports\Out\ClienteRepository;
use App\Application\Facturacion\Ports\In\GenerarFacturaUseCase;
use App\Application\Facturacion\Ports\In\ImprimirFacturaUseCase;
use App\Application\Facturacion\Ports\Out\AsignarPromocionPort;
use App\Application\Facturacion\Ports\Out\FacturaRepository;
use App\Application\Facturacion\Ports\Out\GeneradorQrPort;
use App\Application\Shared\Ports\GeneradorPdfPort;
use App\Application\Shared\Ports\Reloj;
use App\Application\Shared\Ports\Transaccion;
use App\Application\Ventas\Ports\Out\VentaRepository;
use App\Domain\Catalogos\Cliente;
use App\Domain\Facturacion\Factura;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Factura con los datos del cliente y todos los productos comprados,
 * con el código QR que indica la promoción obtenida.
 */
class FacturacionService implements GenerarFacturaUseCase, ImprimirFacturaUseCase
{
    public function __construct(
        private FacturaRepository $facturas,
        private VentaRepository $ventas,
        private ClienteRepository $clientes,
        private AsignarPromocionPort $promociones,
        private GeneradorQrPort $qr,
        private GeneradorPdfPort $pdf,
        private Transaccion $transaccion,
        private Reloj $reloj,
        private string $serie,
    ) {
    }

    public function generar(int $ventaId, ?string $nit, ?string $nombre, ?string $direccion): int
    {
        if ($existente = $this->facturas->deVenta($ventaId)) {
            return $existente;
        }

        return $this->transaccion->ejecutar(function () use ($ventaId, $nit, $nombre, $direccion) {
            $venta = $this->ventas->buscarPorId($ventaId) ?? throw new ReglaNegocioException('La venta no existe.');
            $venta->marcarFacturada();

            $nit = strtoupper(trim((string) $nit));
            $cliente = null;
            if ($nit !== '' && $nit !== Cliente::CONSUMIDOR_FINAL) {
                $cliente = $this->clientes->buscarPorNit($nit)
                    ?? $this->clientes->guardar(new Cliente(null, $nit, (string) $nombre, $direccion));
            } elseif ($nit === '' && $venta->clienteId) {
                $cliente = $this->clientes->buscarPorId($venta->clienteId);
            }

            $factura = Factura::emitir($venta->id, $this->serie, $this->facturas->siguienteNumero($this->serie),
                $cliente?->nit ?? $nit, $nombre ?: $cliente?->nombre, $direccion ?: $cliente?->direccion,
                $venta->total(), $this->reloj->ahora());
            $facturaId = $this->facturas->guardar($factura);

            if ($cliente && $cliente->nit !== Cliente::CONSUMIDOR_FINAL) {
                $venta->clienteId = $cliente->id;
                $this->promociones->acreditarPuntos($cliente->id, $venta->puntosGanados);
            }
            $this->ventas->guardar($venta);
            $this->promociones->emitirCupon($facturaId, $venta->clienteId);

            return $facturaId;
        });
    }

    public function datos(int $facturaId): array
    {
        $factura = $this->facturas->obtener($facturaId) ?? throw new ReglaNegocioException('La factura no existe.');
        $cupon = $this->promociones->cuponDeFactura($facturaId);
        if ($cupon) {
            $cupon['url'] = $this->qr->urlPromocion($cupon['token']);
            $cupon['qr_svg'] = $this->qr->svg($cupon['url']);
        }

        return ['factura' => $factura, 'cupon' => $cupon];
    }

    public function pdf(int $facturaId): string
    {
        return $this->pdf->generar('facturacion.pdf', $this->datos($facturaId));
    }

    public function listar(): array
    {
        return $this->facturas->listar();
    }

    public function deVenta(int $ventaId): ?int
    {
        return $this->facturas->deVenta($ventaId);
    }
}
