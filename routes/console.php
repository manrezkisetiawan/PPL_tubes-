<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tandai tantangan yang selesai/gagal setiap hari (KT-08 Laravel Scheduler).
Schedule::command('challenges:evaluate')->dailyAt('00:10');
