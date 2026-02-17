<?php

use App\Jobs\RecalculateTrustScores;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Batch recalculate trust scores daily at 2am
Schedule::job(new RecalculateTrustScores)->dailyAt('02:00');
