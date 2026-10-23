<?php

namespace App\Application\Catalogos\Services;

use App\Application\Catalogos\Ports\In\SincronizarCatalogoUseCase;
use App\Application\Catalogos\Ports\Out\CatalogoCorePort;
use App\Application\Catalogos\Ports\Out\ProductoRepository;
use App\Application\Catalogos\Ports\Out\ProveedorRepository;
use App\Application\Shared\Ports\Transaccion;
use App\Domain\Catalogos\Producto;
use App\Domain\Catalogos\Proveedor;

/**
 * Trae del Core de Empresas de Consumo Masivo los productos, proveedores y precios.
 * Las existencias no se modifican: solo cambian por compras, ventas y devoluciones.
 */
class SincronizarCatalogoService implements SincronizarCatalogoUseCase
{
    public function __construct(
        private CatalogoCorePort $core,
        private ProductoRepository $productos,
        private ProveedorRepository $proveedores,
        private Transaccion $transaccion,
    ) {
    }

    public function origen(): string
    {
        return $this->core->descripcion();
    }

    public function sincronizar(): array
    {
        $catalogo = $this->core->obtenerCatalogo();

        return $this->transaccion->ejecutar(function () use ($catalogo) {
            $resumen = ['creados' => 0, 'actualizados' => 0, 'proveedores' => 0];
            foreach ($catalogo as $item) {
                $familiaId = $this->productos->familiaPorNombre($item['familia']);
                if ($familiaId === null) {
                    continue;
                }
                $proveedorId = null;
                if (! empty($item['proveedor_nit'])) {
                    $proveedor = $this->proveedores->buscarPorNit($item['proveedor_nit']);
                    if (! $proveedor) {
                        $proveedor = $this->proveedores->guardar(new Proveedor(null, $item['proveedor_nit'], $item['proveedor_nombre'] ?? $item['proveedor_nit']));
                        $resumen['proveedores']++;
                    }
                    $proveedorId = $proveedor->id;
                }

                $actual = $this->productos->buscarPorCodigo($item['codigo']);
                $this->productos->guardar(new Producto(
                    $actual?->id, $item['codigo'], $item['nombre'], $familiaId, $proveedorId ?? $actual?->proveedorId,
                    (float) $item['precio'], $item['precio_mayoreo'] !== null ? (float) $item['precio_mayoreo'] : null,
                    $actual?->existencia ?? 0, true,
                ));
                $actual ? $resumen['actualizados']++ : $resumen['creados']++;
            }

            return $resumen;
        });
    }
}
