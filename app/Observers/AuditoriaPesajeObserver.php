<?php

namespace App\Observers;

use App\Models\Auditoria;
use App\Models\Pesaje;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\Log;

/**
 * AuditoriaPesajeObserver — persiste eventos de Pesaje en la tabla Auditoria (RF21).
 *
 * Anteriormente solo escribía en Log::info(). Ahora usa AuditoriaService para
 * guardar el registro en BD, manteniendo el log como información complementaria.
 */
class AuditoriaPesajeObserver
{
    public function created(Pesaje $pesaje): void
    {
        $pesoInfo = $pesaje->peso_corregido !== null
            ? "{$pesaje->peso_original} kg → corregido {$pesaje->peso_corregido} kg (factor {$pesaje->factor_raza})"
            : "{$pesaje->peso} kg";

        Log::info("AUDITORÍA SISTEMA: Pesaje ID {$pesaje->id_pesaje} registrado — {$pesoInfo}.");

        AuditoriaService::registrar(
            accion:       Auditoria::ACCION_CREAR,
            modulo:       Auditoria::MODULO_PESAJES,
            descripcion:  "Pesaje #{$pesaje->id_pesaje} registrado para arete '{$pesaje->arete}' — {$pesoInfo}.",
            datosDespues: $pesaje->toArray(),
        );
    }

    public function deleted(Pesaje $pesaje): void
    {
        Log::info("AUDITORÍA SISTEMA: Pesaje ID {$pesaje->id_pesaje} eliminado.");

        AuditoriaService::registrar(
            accion:      Auditoria::ACCION_ELIMINAR,
            modulo:      Auditoria::MODULO_PESAJES,
            descripcion: "Pesaje #{$pesaje->id_pesaje} de arete '{$pesaje->arete}' eliminado.",
            datosAntes:  $pesaje->toArray(),
        );
    }
}