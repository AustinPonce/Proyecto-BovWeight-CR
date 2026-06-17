<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * RF22 — Procesar recordatorios de re-pesaje cada día a las 8:00 am.
 *
 * Para que el scheduler corra en producción, agregar al crontab del servidor:
 *   * * * * * cd /ruta-del-proyecto && php artisan schedule:run >> /dev/null 2>&1
 *
 * En desarrollo se puede disparar manualmente con:
 *   php artisan recordatorios:procesar
 */
Schedule::command('recordatorios:procesar')->dailyAt('08:00');
