<?php

namespace App\Support;

use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

class ScheduleReminderDispatcher
{
    public function dispatch(): int
    {
        $count = 0;

        $schedules = Schedule::query()
            ->with(['extracurricular', 'scheduleParticipants.student.user'])
            ->where('status', 'scheduled')
            ->whereDate('activity_date', '>=', Carbon::today())
            ->get();

        foreach ($schedules as $schedule) {
            if ($schedule->status !== 'scheduled' || $schedule->cancelled_at !== null) {
                continue;
            }

            $scheduledAt = Carbon::parse($schedule->activity_date->toDateString().' '.($schedule->start_time ?: '00:00:00'));
            $hoursUntil = now()->diffInHours($scheduledAt, false);
            $shouldSendDayBefore = $hoursUntil <= 24 && $hoursUntil > 12 && $schedule->reminder_sent_day_before_at === null;
            $shouldSendHoursBefore = $hoursUntil <= 6 && $hoursUntil >= 0 && $schedule->reminder_sent_hours_before_at === null;

            if (! $shouldSendDayBefore && ! $shouldSendHoursBefore) {
                continue;
            }

            $studentUsers = Registration::query()
                ->with('student.user')
                ->where('extracurricular_id', $schedule->extracurricular_id)
                ->where('status', Registration::STATUS_APPROVED)
                ->get()
                ->pluck('student.user')
                ->filter();

            app(NotificationCenter::class)->notifyUsers($studentUsers, [
                'title' => 'Pengingat jadwal ekstrakurikuler',
                'message' => sprintf(
                    '%s untuk %s akan berlangsung pada %s.',
                    $schedule->title ?: 'Kegiatan ekstrakurikuler',
                    $schedule->extracurricular->name ?? 'ekstrakurikuler Anda',
                    $scheduledAt->translatedFormat('d M Y H:i')
                ),
                'url' => route('student.schedules.index'),
                'category' => NotificationPreference::CATEGORY_SCHEDULE_ACTIVITY,
                'icon' => 'bi-calendar-event',
                'tag' => 'schedule-reminder-'.$schedule->id,
            ]);

            $schedule->forceFill([
                'reminder_sent_day_before_at' => $shouldSendDayBefore ? now() : $schedule->reminder_sent_day_before_at,
                'reminder_sent_hours_before_at' => $shouldSendHoursBefore ? now() : $schedule->reminder_sent_hours_before_at,
            ])->save();

            $count++;
        }

        return $count;
    }
}
