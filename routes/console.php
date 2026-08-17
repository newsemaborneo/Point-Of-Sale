<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup harian pada jam 12 malam
Illuminate\Support\Facades\Schedule::command('backup:clean')->daily()->at('01:00');
Illuminate\Support\Facades\Schedule::command('backup:run --only-db')->daily()->at('01:30');
