<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:generate-daily-tips')->dailyAt('02:00');

// Recordatorios IA: 3 veces al día (mañana, tarde y noche).
Schedule::command('app:generate-reminders')->dailyAt('09:00');
Schedule::command('app:generate-reminders')->dailyAt('14:00');
Schedule::command('app:generate-reminders')->dailyAt('20:00');

