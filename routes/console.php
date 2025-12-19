<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule reminder sending every minute
Schedule::command('reminders:send')->everyMinute();

// Schedule flow cleanup daily at 2 AM
Schedule::command('flows:cleanup')->dailyAt('02:00');
