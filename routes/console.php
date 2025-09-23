<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Este comando se ejecuta manualmente o desde la consola si está registrado
Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Programar comandos recurrentes (esto lo usa schedule:run cada minuto)
Schedule::command('facturas:generar-recurrentes')
    ->hourly()
    ->before(function () {
        Log::info('Comando facturas:generar-recurrentes está por ejecutarse a las ' . now());
    })
    ->after(function () {
        Log::info('Comando facturas:generar-recurrentes finalizó a las ' . now());
    });

// Fichajes estados cada día a las 23:55
Schedule::command('fichajes:estados')
    ->dailyAt('23:55')
    ->before(function () {
        Log::info('Comando fichajes:estados está por ejecutarse a las ' . now());
    })
    ->after(function () {
        Log::info('Comando fichajes:estados finalizó a las ' . now());
    });
