<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Deletes tokens that expired more than 24 hours ago (tokens last 30 days, see config/sanctum.php)
Schedule::command('sanctum:prune-expired --hours=24')->daily();