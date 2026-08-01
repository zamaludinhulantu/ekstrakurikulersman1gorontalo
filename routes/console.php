<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Support\ScheduleReminderDispatcher;
use App\Support\ScheduledAnnouncementPublisher;
use App\Support\ScheduledArticlePublisher;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:send-schedule-reminders', function (ScheduleReminderDispatcher $dispatcher) {
    $count = $dispatcher->dispatch();

    $this->info("Pengingat jadwal terkirim untuk {$count} jadwal.");
})->purpose('Kirim pengingat jadwal ekstrakurikuler yang mendekati waktu kegiatan.');

Artisan::command('announcements:publish-scheduled', function (ScheduledAnnouncementPublisher $publisher) {
    $count = $publisher->publishDue();

    $this->info("Sebanyak {$count} pengumuman terjadwal dipublikasikan.");
})->purpose('Publikasikan pengumuman yang sudah mencapai waktu tayang.');

Artisan::command('articles:publish-scheduled', function (ScheduledArticlePublisher $publisher) {
    $count = $publisher->publishDue();

    $this->info("Sebanyak {$count} artikel terjadwal dipublikasikan.");
})->purpose('Publikasikan artikel yang sudah mencapai waktu tayang.');

Schedule::command('queue:work --stop-when-empty --tries=1 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('notifications:send-schedule-reminders')->hourly();

Schedule::command('announcements:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('articles:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping();
