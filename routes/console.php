<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule notification checks every 15 minutes
Schedule::command('notifications:check')->everyFifteenMinutes()->name('notification-checks');

// Additional scheduling for peak hours (every 5 minutes during business hours)
Schedule::command('notifications:check')
    ->everyFiveMinutes()
    ->between('8:00', '20:00')
    ->name('notification-checks-frequent');

// Schedule reservation cancellation processing every 30 minutes during business hours
Schedule::command('reservations:process-cancellations')
    ->everyThirtyMinutes()
    ->between('8:00', '21:00')
    ->name('reservation-cancellation-checks');

// Schedule reservation cancellation processing once at 7 PM (19:00) daily to handle end-of-day cancellations
Schedule::command('reservations:process-cancellations')
    ->dailyAt('19:00')
    ->name('reservation-daily-cancellation');
