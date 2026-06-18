<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tareas programadas del CRM (requieren el cron del sistema:
| * * * * * php /ruta/artisan schedule:run >> /dev/null 2>&1)
|--------------------------------------------------------------------------
*/

// Recordatorio KYC pendiente (E-02) — cada día a las 9:00.
Schedule::command('crm:send-kyc-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer();

// Aviso interno de cuotas vencidas / mora (E-10) — cada día a las 8:00.
Schedule::command('crm:send-overdue-alerts')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

// Rotación de "HIGH DEMAND" (híbrido vistas reales + fake) — cada hora.
Schedule::command('units:refresh-demand')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
