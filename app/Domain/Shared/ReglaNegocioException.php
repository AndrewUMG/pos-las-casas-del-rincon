<?php

namespace App\Domain\Shared;

/**
 * Se lanza cuando una operación viola una regla de negocio del dominio.
 */
class ReglaNegocioException extends \DomainException
{
}
