<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincronización automática de especies cada domingo a las 3:00 AM
Schedule::command('species:sync --source=all')
    ->weeklyOn(0, '03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/species-sync.log'));

// Alternativa: Sincronización mensual (primer día del mes)
// Schedule::command('species:sync --source=all --force')
//     ->monthlyOn(1, '04:00');

// Sincronización de áreas protegidas cada mes (día 15 a las 4:00 AM)
Schedule::command('protected-areas:sync --source=wdpa')
    ->monthlyOn(15, '04:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/protected-areas-sync.log'));