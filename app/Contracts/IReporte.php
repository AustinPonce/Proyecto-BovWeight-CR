<?php

namespace App\Contracts;

/**
 * Interface del patrón Factory Method.
 *
 * Todos los reportes deben implementar este método.
 */
interface IReporte
{
    public function generar(array $datos);
}
