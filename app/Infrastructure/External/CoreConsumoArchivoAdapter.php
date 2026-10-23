<?php

namespace App\Infrastructure\External;

use App\Application\Catalogos\Ports\Out\CatalogoCorePort;
use App\Domain\Shared\ReglaNegocioException;

/**
 * Lee el catálogo exportado por el Core de Empresas de Consumo Masivo desde un archivo JSON.
 * Útil cuando el Core entrega su catálogo como archivo de intercambio.
 */
class CoreConsumoArchivoAdapter implements CatalogoCorePort
{
    public function __construct(private string $archivo)
    {
    }

    public function descripcion(): string
    {
        return 'Archivo de intercambio del Core: '.basename($this->archivo);
    }

    public function obtenerCatalogo(): array
    {
        if (! is_file($this->archivo)) {
            throw new ReglaNegocioException('No se encontró el archivo del Core: '.$this->archivo);
        }
        $datos = json_decode(file_get_contents($this->archivo), true);
        if (! is_array($datos)) {
            throw new ReglaNegocioException('El archivo del Core no tiene un formato JSON válido.');
        }

        return CatalogoCoreNormalizador::normalizar($datos['productos'] ?? $datos);
    }
}
