<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\TalentTestParticipant;
use App\Models\TalentTestResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TalentTestController extends Controller
{
    public function index(Request $request): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $requestedTab = $request->string('tab')->toString();
        $now = now();
        $today = $now->toDateString();
        $timeNow = $now->format('H:i:s');

        $baseQuery = TalentTestParticipant::with([
            'schedule.extracurricular',
        ])
            ->where('student_id', $student->id)
            ->whereHas('schedule', fn ($query) => $query->where('schedule_type', 'talent_test'));

        $publishedResults = TalentTestResult::with(['schedule.extracurricular', 'items.aspect'])
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->get()
            ->keyBy(fn (TalentTestResult $result) => $this->resultMapKey((int) $result->schedule_id, (int) $result->student_id));

        $allResults = TalentTestResult::with(['schedule.extracurricular'])
            ->where('student_id', $student->id)
            ->latest('published_at')
            ->get()
            ->keyBy(fn (TalentTestResult $result) => $this->resultMapKey((int) $result->schedule_id, (int) $result->student_id));

        $upcomingCount = (clone $baseQuery)
            ->where(function ($query) use ($today, $timeNow): void {
                $query->whereHas('schedule', function ($scheduleQuery) use ($today, $timeNow): void {
                    $scheduleQuery->where(function ($statusQuery) use ($today, $timeNow): void {
                        $statusQuery->whereNotNull('cancelled_at')
                            ->orWhere('status', 'cancelled')
                            ->orWhere(function ($futureQuery) use ($today, $timeNow): void {
                                $futureQuery->whereNull('cancelled_at')
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
                    });
                });
            })
            ->whereDoesntHave('schedule.talentTestResults', fn ($query) => $query->whereColumn('talent_test_results.student_id', 'talent_test_participants.student_id')->where('status', 'published'))
            ->count();

        $pendingCount = (clone $baseQuery)
            ->whereHas('schedule', function ($query) use ($today, $timeNow): void {
                $query->whereNull('cancelled_at')
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($timeQuery) use ($today, $timeNow): void {
                        $timeQuery->whereDate('activity_date', '<', $today)
                            ->orWhere(function ($todayQuery) use ($today, $timeNow): void {
                                $todayQuery->whereDate('activity_date', $today)
                                    ->whereNotNull('end_time')
                                    ->where('end_time', '<', $timeNow);
                            });
                    });
            })
            ->whereDoesntHave('schedule.talentTestResults', fn ($query) => $query->whereColumn('talent_test_results.student_id', 'talent_test_participants.student_id')->where('status', 'published'))
            ->count();

        $historyCount = (clone $baseQuery)
            ->whereHas('schedule.talentTestResults', fn ($query) => $query->whereColumn('talent_test_results.student_id', 'talent_test_participants.student_id')->where('status', 'published'))
            ->count();

        $completedCount = (clone $baseQuery)
            ->whereHas('schedule', function ($query) use ($today, $timeNow): void {
                $query->whereNull('cancelled_at')
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($timeQuery) use ($today, $timeNow): void {
                        $timeQuery->whereDate('activity_date', '<', $today)
                            ->orWhere(function ($todayQuery) use ($today, $timeNow): void {
                                $todayQuery->whereDate('activity_date', $today)
                                    ->whereNotNull('end_time')
                                    ->where('end_time', '<', $timeNow);
                            });
                    });
            })
            ->count();

        $defaultTab = 'upcoming';
        if ($upcomingCount === 0 && $pendingCount > 0) {
            $defaultTab = 'pending';
        }
        if ($upcomingCount === 0 && $pendingCount === 0 && $historyCount > 0) {
            $defaultTab = 'history';
        }
        $tab = in_array($requestedTab, ['upcoming', 'pending', 'history'], true) ? $requestedTab : $defaultTab;

        $upcomingTests = (clone $baseQuery)
            ->when($tab === 'upcoming', function ($query) use ($today, $timeNow): void {
                $query->where(function ($scope) use ($today, $timeNow): void {
                    $scope->whereHas('schedule', function ($scheduleQuery) use ($today, $timeNow): void {
                        $scheduleQuery->where(function ($statusQuery) use ($today, $timeNow): void {
                            $statusQuery->whereNotNull('cancelled_at')
                                ->orWhere('status', 'cancelled')
                                ->orWhere(function ($futureQuery) use ($today, $timeNow): void {
                                    $futureQuery->whereNull('cancelled_at')
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
                        });
                    });
                })->whereDoesntHave('schedule.talentTestResults', fn ($resultQuery) => $resultQuery->whereColumn('talent_test_results.student_id', 'talent_test_participants.student_id')->where('status', 'published'));
                $query->orderBy(
                    Schedule::select('activity_date')
                        ->whereColumn('schedules.id', 'talent_test_participants.schedule_id')
                        ->limit(1)
                )->orderBy(
                    Schedule::select('start_time')
                        ->whereColumn('schedules.id', 'talent_test_participants.schedule_id')
                        ->limit(1)
                );
            }, fn ($query) => $query->whereRaw('1 = 0'))
            ->paginate(10, ['*'], 'upcoming_page')
            ->withQueryString();

        $pendingTests = (clone $baseQuery)
            ->when($tab === 'pending', function ($query) use ($today, $timeNow): void {
                $query->whereHas('schedule', function ($scheduleQuery) use ($today, $timeNow): void {
                    $scheduleQuery->whereNull('cancelled_at')
                        ->where('status', '!=', 'cancelled')
                        ->where(function ($timeQuery) use ($today, $timeNow): void {
                            $timeQuery->whereDate('activity_date', '<', $today)
                                ->orWhere(function ($todayQuery) use ($today, $timeNow): void {
                                    $todayQuery->whereDate('activity_date', $today)
                                        ->whereNotNull('end_time')
                                        ->where('end_time', '<', $timeNow);
                                });
                        });
                })->whereDoesntHave('schedule.talentTestResults', fn ($resultQuery) => $resultQuery->whereColumn('talent_test_results.student_id', 'talent_test_participants.student_id')->where('status', 'published'));
                $query->orderByDesc(
                    Schedule::select('activity_date')
                        ->whereColumn('schedules.id', 'talent_test_participants.schedule_id')
                        ->limit(1)
                )->orderByDesc(
                    Schedule::select('end_time')
                        ->whereColumn('schedules.id', 'talent_test_participants.schedule_id')
                        ->limit(1)
                );
            }, fn ($query) => $query->whereRaw('1 = 0'))
            ->paginate(10, ['*'], 'pending_page')
            ->withQueryString();

        $historyTests = (clone $baseQuery)
            ->when($tab === 'history', function ($query): void {
                $query->whereHas('schedule.talentTestResults', fn ($resultQuery) => $resultQuery->whereColumn('talent_test_results.student_id', 'talent_test_participants.student_id')->where('status', 'published'));
                $query->orderByDesc(
                    TalentTestResult::select('published_at')
                        ->whereColumn('talent_test_results.schedule_id', 'talent_test_participants.schedule_id')
                        ->whereColumn('talent_test_results.student_id', 'talent_test_participants.student_id')
                        ->where('status', 'published')
                        ->limit(1)
                );
            }, fn ($query) => $query->whereRaw('1 = 0'))
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();

        $upcomingTests->setCollection($this->decorateParticipantsPage($upcomingTests->getCollection(), $publishedResults, $allResults, $now));
        $pendingTests->setCollection($this->decorateParticipantsPage($pendingTests->getCollection(), $publishedResults, $allResults, $now));
        $historyTests->setCollection($this->decorateParticipantsPage($historyTests->getCollection(), $publishedResults, $allResults, $now));

        return view('student.talent-tests.index', [
            'tab' => $tab,
            'upcomingTests' => $upcomingTests,
            'pendingTests' => $pendingTests,
            'historyTests' => $historyTests,
            'summary' => [
                'upcoming' => $upcomingCount,
                'pending' => $pendingCount,
                'available' => $historyCount,
                'completed' => $completedCount,
            ],
        ]);
    }

    private function decorateParticipantsPage(
        Collection $participants,
        Collection $publishedResults,
        Collection $allResults,
        Carbon $now
    ): Collection {
        return $participants->map(
            fn (TalentTestParticipant $test): TalentTestParticipant => $this->decorateParticipant($test, $publishedResults, $allResults, $now)
        );
    }

    private function decorateParticipant(
        TalentTestParticipant $participant,
        Collection $publishedResults,
        Collection $allResults,
        Carbon $now
    ): TalentTestParticipant {
        $schedule = $participant->schedule;
        $mapKey = $this->resultMapKey((int) $participant->schedule_id, (int) $participant->student_id);
        $publishedResult = $publishedResults->get($mapKey);
        $result = $allResults->get($mapKey);
        $isCancelled = $schedule?->cancelled_at !== null || $schedule?->status === 'cancelled';
        $startAt = $schedule?->activity_date ? Carbon::parse($schedule->activity_date->format('Y-m-d').' '.($schedule->start_time ?: '00:00:00')) : null;
        $endAt = $schedule?->activity_date ? Carbon::parse($schedule->activity_date->format('Y-m-d').' '.($schedule->end_time ?: '23:59:59')) : null;
        $attendanceStatus = $participant->attendance_status ?: 'pending';

        $tabKey = 'upcoming';
        $statusKey = 'scheduled';
        $statusLabel = 'Tes Dijadwalkan';

        if ($isCancelled) {
            $tabKey = 'upcoming';
            $statusKey = 'cancelled';
            $statusLabel = 'Dibatalkan';
        } elseif ($publishedResult) {
            $tabKey = 'history';
            $statusKey = $publishedResult->needs_retest ? 'retest' : 'result_available';
            $statusLabel = $publishedResult->needs_retest ? 'Tes Ulang' : 'Hasil Tersedia';
        } elseif ($endAt && $endAt->lt($now)) {
            $tabKey = 'pending';
            $statusKey = 'being_assessed';
            $statusLabel = 'Sedang Dinilai';
        } elseif ($startAt?->isToday()) {
            $tabKey = 'upcoming';
            $statusKey = 'today';
            $statusLabel = 'Hari Ini';
        } elseif ($startAt?->isTomorrow()) {
            $tabKey = 'upcoming';
            $statusKey = 'tomorrow';
            $statusLabel = 'Besok';
        }

        $attendanceLabel = match ($attendanceStatus) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => $startAt && $startAt->isFuture() ? 'Tes Dijadwalkan' : 'Menunggu Jadwal',
        };

        if ($startAt && $startAt->isFuture() && $attendanceStatus === 'pending') {
            $attendanceLabel = 'Menunggu Jadwal';
        }

        $participant->setAttribute('student_tab_key', $tabKey);
        $participant->setAttribute('student_status_key', $statusKey);
        $participant->setAttribute('student_status_label', $statusLabel);
        $participant->setAttribute('student_attendance_label', $attendanceLabel);
        $participant->setAttribute('student_result_status_label', $publishedResult ? 'Hasil Tersedia' : ($result ? 'Sedang Dinilai' : 'Sedang Dinilai'));
        $participant->setAttribute('student_result', $publishedResult);
        $participant->setAttribute('student_result_category', $publishedResult?->ability_category ?: 'Belum ada kategori');
        $participant->setAttribute('student_score', $publishedResult?->overall_score);
        $participant->setAttribute('student_recommendation', $publishedResult?->recommendation ?: 'Belum ada rekomendasi yang dipublikasikan.');
        $participant->setAttribute('student_schedule_badge', $statusKey === 'today' ? 'Hari Ini' : ($statusKey === 'tomorrow' ? 'Besok' : ($statusKey === 'scheduled' ? 'Mendatang' : $statusLabel)));
        $participant->setAttribute('student_completed', !$isCancelled && $endAt && $endAt->lt($now));

        return $participant;
    }

    private function resultMapKey(int $scheduleId, int $studentId): string
    {
        return $scheduleId.'-'.$studentId;
    }
}
