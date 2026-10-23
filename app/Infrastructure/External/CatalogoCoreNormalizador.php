<?php

namespace App\Infrastructure\External;

/**
 * Traduce el formato del Core al formato que espera el puerto CatalogoCorePort.
 */
final class CatalogoCoreNormalizador
{
    public static function normalizar(array $items): array
    {
        $resultado = [];
        foreach ($items as $i) {
            if (empty($i['codigo']) || empty($i['nombre']) || empty($i['familia']) || ! isset($i['precio'])) {
                continue;
            }
            $resultado[] = [
                'codigo' => (string) $i['codigo'],
                'nombre' => (string) $i['nombre'],
                'familia' => (string) $i['familia'],
                'precio' => (float) $i['precio'],
                'precio_mayoreo' => isset($i['precio_mayoreo']) ? (float) $i['precio_mayoreo'] : null,
                'proveedor_nit' => $i['proveedor_nit'] ?? null,
                'proveedor_nombre' => $i['proveedor_nombre'] ?? null,
            ];
        }

        return $resultado;
    }
}
