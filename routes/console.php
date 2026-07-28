<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Support\ScheduleReminderDispatcher;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:send-schedule-reminders', function (ScheduleReminderDispatcher $dispatcher) {
    $count = $dispatcher->dispatch();

    $this->info("Pengingat jadwal terkirim untuk {$count} jadwal.");
})->purpose('Kirim pengingat jadwal ekstrakurikuler yang mendekati waktu kegiatan.');

Schedule::command('queue:work --stop-when-empty --tries=1 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('notifications:send-schedule-reminders')->hourly();
