<?php

namespace App\Infrastructure\External;

use App\Application\Catalogos\Ports\Out\CatalogoCorePort;
use App\Domain\Shared\ReglaNegocioException;
use Illuminate\Support\Facades\Http;

/**
 * Consume la API REST del Core de Empresas de Consumo Masivo (sistema existente).
 * Se espera un arreglo JSON de productos con: codigo, nombre, familia, precio,
 * precio_mayoreo, proveedor_nit y proveedor_nombre.
 */
class CoreConsumoHttpAdapter implements CatalogoCorePort
{
    public function __construct(private ?string $url, private ?string $token)
    {
    }

    public function descripcion(): string
    {
        return 'API del Core: '.($this->url ?: '(sin configurar)');
    }

    public function obtenerCatalogo(): array
    {
        if (! $this->url) {
            throw new ReglaNegocioException('Configure CORE_API_URL para conectarse al Core.');
        }
        $respuesta = Http::acceptJson()->timeout(15)->when($this->token, fn ($h) => $h->withToken($this->token))->get($this->url);
        if ($respuesta->failed()) {
            throw new ReglaNegocioException('El Core respondió con error HTTP '.$respuesta->status().'.');
        }

        return CatalogoCoreNormalizador::normalizar($respuesta->json('productos') ?? $respuesta->json() ?? []);
    }
}
