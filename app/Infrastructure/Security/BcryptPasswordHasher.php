<?php

namespace App\Infrastructure\Security;

use App\Application\Seguridad\Ports\Out\PasswordHasher;
use Illuminate\Support\Facades\Hash;

class BcryptPasswordHasher implements PasswordHasher
{
    public function hash(string $plano): string
    {
        return Hash::make($plano);
    }

    public function verificar(string $plano, string $hash): bool
    {
        return Hash::check($plano, $hash);
    }
}
