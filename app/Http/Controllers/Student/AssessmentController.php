<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Extracurricular;
use App\Models\Registration;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 404, 'Data siswa tidak ditemukan.');

        $extracurricularId = $request->string('extracurricular_id')->toString();
        $title = $request->string('title')->toString();
        $month = $request->string('month')->toString();
        $year = $request->string('year')->toString();
        $period = $request->string('period')->toString() ?: 'all';
        $allowedExtracurricularIds = Registration::where('student_id', $student->id)
            ->where('status', Registration::STATUS_APPROVED)
            ->pluck('extracurricular_id');

        $baseQuery = Assessment::with(['extracurricular', 'coach.user'])
            ->where('student_id', $student->id)
            ->where('assessment_type', 'assessment')
            ->where('status', Assessment::STATUS_PUBLISHED)
            ->when($extracurricularId, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->when($title, fn ($query, $titleValue) => $query->where('title', $titleValue))
            ->when($period === 'month', fn ($query) => $query->whereMonth('assessment_date', now()->month)->whereYear('assessment_date', now()->year))
            ->when($period === 'semester', fn ($query) => $query->whereYear('assessment_date', now()->year)->whereIn(DB::raw('MONTH(assessment_date)'), now()->month <= 6 ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12]))
            ->when($month, fn ($query, $monthValue) => $query->whereMonth('assessment_date', (int) $monthValue))
            ->when($year, fn ($query, $yearValue) => $query->whereYear('assessment_date', (int) $yearValue));

        $allAssessments = (clone $baseQuery)
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Assessment $assessment): Assessment => $this->decorateAssessment($assessment));

        $assessments = $this->paginateCollection($allAssessments, 10, 'page', $request);

        $latestAssessment = $allAssessments->first();
        $averageScore = $allAssessments->whereNotNull('score')->avg('score');
        $highestScore = $allAssessments->whereNotNull('score')->max('score');

        $trend = null;
        if ($title) {
            $sameType = $allAssessments->where('title', $title)->whereNotNull('score')->values();
            if ($sameType->count() >= 2) {
                $latest = $sameType->first();
                $previous = $sameType->skip(1)->first();
                if ($latest && $previous) {
                    $difference = round((float) $latest->score - (float) $previous->score, 2);
                    $trend = [
                        'previous' => $previous->score,
                        'latest' => $latest->score,
                        'difference' => $difference,
                        'status' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'steady'),
                    ];
                }
            }
        }

        $titleOptions = Assessment::where('student_id', $student->id)
            ->where('assessment_type', 'assessment')
            ->where('status', Assessment::STATUS_PUBLISHED)
            ->orderBy('title')
            ->pluck('title')
            ->filter()
            ->unique()
            ->values();

        $monthOptions = Assessment::where('student_id', $student->id)
            ->where('assessment_type', 'assessment')
            ->where('status', Assessment::STATUS_PUBLISHED)
            ->orderBy('assessment_date')
            ->get()
            ->map(fn (Assessment $assessment) => optional($assessment->assessment_date)->format('m'))
            ->filter()
            ->unique()
            ->values();

        $yearOptions = Assessment::where('student_id', $student->id)
            ->where('assessment_type', 'assessment')
            ->where('status', Assessment::STATUS_PUBLISHED)
            ->orderByDesc('assessment_date')
            ->get()
            ->map(fn (Assessment $assessment) => optional($assessment->assessment_date)->format('Y'))
            ->filter()
            ->unique()
            ->values();

        return view('student.assessments.index', [
            'assessments' => $assessments,
            'extracurricularId' => $extracurricularId,
            'title' => $title,
            'month' => $month,
            'year' => $year,
            'period' => in_array($period, ['latest', 'month', 'semester', 'all'], true) ? $period : 'all',
            'extracurriculars' => Extracurricular::whereIn('id', $allowedExtracurricularIds)->orderBy('name')->get(),
            'titleOptions' => $titleOptions,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'assessmentSummary' => [
                'count' => $allAssessments->count(),
                'average' => $averageScore,
                'highest' => $highestScore,
                'latest' => $latestAssessment,
            ],
            'assessmentTrend' => $trend,
        ]);
    }

    private function decorateAssessment(Assessment $assessment): Assessment
    {
        $score = $assessment->score !== null ? (float) $assessment->score : null;
        $category = 'Belum dinilai';
        $categoryClass = 'badge-status-secondary';

        if ($score !== null) {
            if ($score < 60) {
                $category = 'Perlu Pembinaan';
                $categoryClass = 'badge-status-danger';
            } elseif ($score < 70) {
                $category = 'Cukup';
                $categoryClass = 'badge-status-warning';
            } elseif ($score < 80) {
                $category = 'Baik';
                $categoryClass = 'badge-status-success';
            } elseif ($score < 90) {
                $category = 'Sangat Baik';
                $categoryClass = 'badge-status-success';
            } else {
                $category = 'Unggul';
                $categoryClass = 'badge-status-success';
            }
        }

        $assessment->setAttribute('student_score_value', $score);
        $assessment->setAttribute('student_category', $category);
        $assessment->setAttribute('student_category_class', $categoryClass);
        $assessment->setAttribute('student_recommendation', $this->buildRecommendation($score));

        return $assessment;
    }

    private function buildRecommendation(?float $score): string
    {
        if ($score === null) {
            return 'Belum ada rekomendasi tambahan.';
        }

        return match (true) {
            $score < 60 => 'Perlu latihan tambahan dan pendampingan lebih intens.',
            $score < 70 => 'Teruskan latihan dasar agar hasil lebih stabil.',
            $score < 80 => 'Performa cukup baik, pertahankan konsistensi latihan.',
            $score < 90 => 'Performa sangat baik, tingkatkan detail dan ketepatan.',
            default => 'Hasil sangat unggul, pertahankan dan jadikan kekuatan utama.',
        };
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return tap(new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            ['pageName' => $pageName, 'path' => $request->url(), 'query' => $request->query()]
        ), function (LengthAwarePaginator $paginator): void {
            $paginator->withQueryString();
        });
    }
}
