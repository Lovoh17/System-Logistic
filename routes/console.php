<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Alerta diaria a las 8:00 AM para órdenes de compra sin recibir > 7 días
Schedule::command('compras:alertar-ordenes-vencidas')->dailyAt('08:00');
