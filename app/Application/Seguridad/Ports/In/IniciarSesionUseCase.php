<?php

namespace App\Application\Seguridad\Ports\In;

/**
 * Valida las credenciales contra la base de datos e inicia la sesión.
 */
interface IniciarSesionUseCase
{
    public function iniciar(string $usuario, string $password): void;
}
