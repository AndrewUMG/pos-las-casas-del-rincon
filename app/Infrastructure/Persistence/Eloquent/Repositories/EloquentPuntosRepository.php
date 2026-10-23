<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Promociones\Ports\Out\PuntosRepository;
use App\Domain\Shared\ReglaNegocioException;
use App\Infrastructure\Persistence\Eloquent\Models\ClienteModel;

class EloquentPuntosRepository implements PuntosRepository
{
    public function saldo(int $clienteId): int
    {
        return (int) ClienteModel::whereKey($clienteId)->value('puntos');
    }

    public function ajustar(int $clienteId, int $delta): void
    {
        $actualizados = ClienteModel::whereKey($clienteId)->where('puntos', '>=', max(0, -$delta))
            ->increment('puntos', $delta);
        if ($actualizados === 0) {
            throw new ReglaNegocioException('El cliente no tiene puntos suficientes.');
        }
    }
}
