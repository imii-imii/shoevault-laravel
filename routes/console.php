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
