<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('qontak:auto-assign')
//     ->everyFiveSeconds()
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->appendOutputTo(storage_path('logs/auto-assign.log'));

// Schedule::command('qontak:auto-reply')
//     ->everyFiveSeconds()
//     ->withoutOverlapping()
//     ->runInBackground()
//     ->appendOutputTo(storage_path('logs/auto-reply.log'));

Schedule::command('qontak:auto-reply')
    ->everyFiveSeconds();