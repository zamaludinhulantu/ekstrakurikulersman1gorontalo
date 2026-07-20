<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $approvedExtracurricularIds = Registration::where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id');

        $date = $request->string('date')->toString();
        $tab = $request->string('tab')->toString() ?: 'upcoming';
        $historyExtracurricular = $request->string('history_extracurricular')->toString();
        $historyType = $request->string('history_type')->toString();
        $historyMonth = $request->string('history_month')->toString();
        $historyYear = $request->string('history_year')->toString();
        $now = now();
        $today = $now->toDateString();
        $timeNow = $now->format('H:i:s');

        $baseQuery = Schedule::with(['extracurricular', 'coach.user'])
            ->whereIn('extracurricular_id', $approvedExtracurricularIds)
            ->when($date, fn ($query, $dateValue) => $query->whereDate('activity_date', $dateValue));

        $upcomingSchedules = (clone $baseQuery)
            ->where(function ($query) use ($today, $timeNow): void {
                $query->where(function ($upcomingQuery) use ($today, $timeNow): void {
                    $upcomingQuery->whereNull('cancelled_at')
                        ->where('status', '!=', 'cancelled')
                        ->where(function ($timeQuery) use ($today, $timeNow): void {
                            $timeQuery->whereDate('activity_date', '>', $today)
                                ->orWhere(function ($todayQuery) use ($today, $timeNow): void {
                                    $todayQuery->whereDate('activity_date', $today)
                                        ->where(function ($endQuery) use ($timeNow): void {
                                            $endQuery->whereNull('end_time')
                                                ->orWhere('end_time', '>=', $timeNow);
                                        });
                                });
                        });
                });
            })
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->paginate(10, ['*'], 'upcoming_page')
            ->withQueryString();

        $upcomingSchedules->setCollection(
            $upcomingSchedules->getCollection()->map(fn (Schedule $schedule): Schedule => $this->decorateSchedule($schedule, $now))
        );

        $historyBaseQuery = (clone $baseQuery)
            ->where(function ($query) use ($today, $timeNow): void {
                $query->whereNotNull('cancelled_at')
                    ->orWhere('status', 'cancelled')
                    ->orWhere(function ($completedQuery) use ($today, $timeNow): void {
                        $completedQuery->whereNull('cancelled_at')
                            ->where('status', '!=', 'cancelled')
                            ->where(function ($timeQuery) use ($today, $timeNow): void {
                                $timeQuery->whereDate('activity_date', '<', $today)
                                    ->orWhere(function ($todayQuery) use ($today, $timeNow): void {
                                        $todayQuery->whereDate('activity_date', $today)
                                            ->whereNotNull('end_time')
                                            ->where('end_time', '<', $timeNow);
                                    });
                            });
                    });
            })
            ->when($historyExtracurricular, fn ($query, $value) => $query->where('extracurricular_id', (int) $value))
            ->when($historyType, function ($query, $value): void {
                $query->where('schedule_type', $value === 'talent_test' ? 'talent_test' : 'activity');
            })
            ->when($historyMonth, fn ($query, $value) => $query->whereMonth('activity_date', (int) $value))
            ->when($historyYear, fn ($query, $value) => $query->whereYear('activity_date', (int) $value));

        $historySchedules = (clone $historyBaseQuery)
            ->orderByDesc('activity_date')
            ->orderByDesc('end_time')
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();

        $historySchedules->setCollection(
            $historySchedules->getCollection()->map(fn (Schedule $schedule): Schedule => $this->decorateSchedule($schedule, $now))
        );

        $todayCount = (clone $baseQuery)
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->whereDate('activity_date', $today)
            ->where(function ($query) use ($timeNow): void {
                $query->whereNull('end_time')
                    ->orWhere('end_time', '>=', $timeNow);
            })
            ->count();

        $weekCount = (clone $baseQuery)
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('activity_date', [$now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()])
            ->count();

        $completedCount = (clone $baseQuery)
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($today, $timeNow): void {
                $query->whereDate('activity_date', '<', $today)
                    ->orWhere(function ($todayQuery) use ($today, $timeNow): void {
                        $todayQuery->whereDate('activity_date', $today)
                            ->whereNotNull('end_time')
                            ->where('end_time', '<', $timeNow);
                    });
            })
            ->count();

        $nextSchedule = (clone $baseQuery)
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($today, $timeNow): void {
                $query->whereDate('activity_date', '>', $today)
                    ->orWhere(function ($todayQuery) use ($today, $timeNow): void {
                        $todayQuery->whereDate('activity_date', $today)
                            ->where(function ($endQuery) use ($timeNow): void {
                                $endQuery->whereNull('end_time')
                                    ->orWhere('end_time', '>=', $timeNow);
                            });
                    });
            })
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->first();

        if ($nextSchedule) {
            $nextSchedule = $this->decorateSchedule($nextSchedule, $now);
        }

        $nearestTalentTest = (clone $baseQuery)
            ->where('schedule_type', 'talent_test')
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($today, $timeNow): void {
                $query->whereDate('activity_date', '>', $today)
                    ->orWhere(function ($todayQuery) use ($today, $timeNow): void {
                        $todayQuery->whereDate('activity_date', $today)
                            ->where(function ($endQuery) use ($timeNow): void {
                                $endQuery->whereNull('end_time')
                                    ->orWhere('end_time', '>=', $timeNow);
                            });
                    });
            })
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->first();

        if ($nearestTalentTest) {
            $nearestTalentTest = $this->decorateSchedule($nearestTalentTest, $now);
        }

        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'sqlite' => "strftime('%m', activity_date)",
            'pgsql' => "to_char(activity_date, 'MM')",
            default => 'LPAD(MONTH(activity_date), 2, \'0\')',
        };
        $yearExpression = match ($driver) {
            'sqlite' => "strftime('%Y', activity_date)",
            'pgsql' => "to_char(activity_date, 'YYYY')",
            default => 'YEAR(activity_date)',
        };

        $historyMonths = (clone $historyBaseQuery)
            ->selectRaw($monthExpression.' as activity_month')
            ->distinct()
            ->orderBy('activity_month')
            ->pluck('activity_month')
            ->map(fn ($month) => str_pad((string) $month, 2, '0', STR_PAD_LEFT))
            ->values();

        $historyYears = (clone $historyBaseQuery)
            ->selectRaw($yearExpression.' as activity_year')
            ->distinct()
            ->orderByDesc('activity_year')
            ->pluck('activity_year')
            ->map(fn ($year) => (string) $year)
            ->values();

        return view('student.schedules.index', [
            'date' => $date,
            'tab' => in_array($tab, ['upcoming', 'history'], true) ? $tab : 'upcoming',
            'extracurriculars' => Extracurricular::whereIn('id', $approvedExtracurricularIds)->orderBy('name')->get(),
            'upcomingSchedules' => $upcomingSchedules,
            'historySchedules' => $historySchedules,
            'nextSchedule' => $nextSchedule,
            'todayCount' => $todayCount,
            'weekCount' => $weekCount,
            'completedCount' => $completedCount,
            'nearestTalentTest' => $nearestTalentTest,
            'historyExtracurricular' => $historyExtracurricular,
            'historyType' => $historyType,
            'historyMonth' => $historyMonth,
            'historyYear' => $historyYear,
            'historyMonths' => $historyMonths,
            'historyYears' => $historyYears,
        ]);
    }

    private function decorateSchedule(Schedule $schedule, Carbon $now): Schedule
    {
        $isCancelled = $schedule->cancelled_at !== null || $schedule->status === 'cancelled';
        $endAt = $schedule->activity_date
            ? Carbon::parse($schedule->activity_date->format('Y-m-d').' '.($schedule->end_time ?: '23:59:59'))
            : null;
        $startAt = $schedule->activity_date
            ? Carbon::parse($schedule->activity_date->format('Y-m-d').' '.($schedule->start_time ?: '00:00:00'))
            : null;

        $displayStatus = 'upcoming';
        $displayLabel = 'Mendatang';

        if ($isCancelled) {
            $displayStatus = 'cancelled';
            $displayLabel = 'Dibatalkan';
        } elseif ($endAt && $endAt->lt($now)) {
            $displayStatus = 'completed';
            $displayLabel = 'Selesai';
        } elseif ($schedule->activity_date?->isToday()) {
            $displayStatus = 'today';
            $displayLabel = 'Hari Ini';
        } elseif ($schedule->activity_date?->isTomorrow()) {
            $displayStatus = 'tomorrow';
            $displayLabel = 'Besok';
        }

        $schedule->setAttribute('student_display_status', $displayStatus);
        $schedule->setAttribute('student_display_label', $displayLabel);
        $schedule->setAttribute('student_end_at', $endAt);
        $schedule->setAttribute('student_start_at', $startAt);
        $schedule->setAttribute('student_type_label', $schedule->schedule_type === 'talent_test' ? 'Tes Bakat' : 'Kegiatan Ekskul');
        $schedule->setAttribute('student_type_key', $schedule->schedule_type === 'talent_test' ? 'talent_test' : 'activity');

        return $schedule;
    }
}
