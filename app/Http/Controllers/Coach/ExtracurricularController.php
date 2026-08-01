<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Models\Student;
use App\Support\NotificationCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $extracurriculars = Extracurricular::with('coaches.user')
            ->whereHas('coaches', fn ($query) => $query->whereKey($coach->id))
            ->withCount([
                'registrations as participants_count' => fn ($query) => $query->where('status', Registration::STATUS_APPROVED),
            ])
            ->orderBy('name')
            ->get();

        return view('coach.extracurriculars.index', compact('extracurriculars'));
    }

    public function participants(Request $request, Extracurricular $extracurricular): View
    {
        $this->authorize('viewByCoach', $extracurricular);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort' => ['nullable', 'in:name,nis,class_name,registration_date'],
            'direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50,100'],
        ]);
        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);
        $filters['sort'] = $filters['sort'] ?? 'registration_date';
        $filters['direction'] = $filters['direction'] ?? 'desc';
        $filters['per_page'] = (int) ($filters['per_page'] ?? 20);

        $participantQuery = $extracurricular->registrations()
            ->with(['student.user', 'talentTestResults'])
            ->where('status', Registration::STATUS_APPROVED)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->whereHas('student', function ($studentQuery) use ($search): void {
                    $studentQuery->where('nis', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['class_name'] ?? null, function ($query, string $className): void {
                $comparable = Student::normalizedClassComparable($className);
                $query->whereHas('student', fn ($studentQuery) => $studentQuery
                    ->whereRaw(Student::normalizedClassExpression('class_name').' = ?', [$comparable]));
            })
            ->when(($filters['status'] ?? '') !== '', function ($query) use ($filters): void {
                $active = ($filters['status'] ?? '') === 'active';
                $query->whereHas('student.user', fn ($userQuery) => $userQuery->where('is_active', $active));
            });

        $direction = $filters['direction'];
        $participants = match ($filters['sort']) {
            'name' => $participantQuery->orderByRaw(
                "(SELECT users.name FROM users INNER JOIN students ON students.user_id = users.id WHERE students.id = registrations.student_id LIMIT 1) {$direction}"
            ),
            'nis' => $participantQuery->orderByRaw(
                "(SELECT students.nis FROM students WHERE students.id = registrations.student_id LIMIT 1) {$direction}"
            ),
            'class_name' => $participantQuery->orderByRaw(
                "(SELECT students.class_name FROM students WHERE students.id = registrations.student_id LIMIT 1) {$direction}"
            ),
            default => $participantQuery->orderBy('registration_date', $direction),
        };
        $participants = $participants
            ->orderBy('registrations.id', $direction)
            ->paginate($filters['per_page'])
            ->withQueryString();

        $summaryBase = $extracurricular->registrations()
            ->where('status', Registration::STATUS_APPROVED);
        $participantSummary = [
            'total' => (clone $summaryBase)->distinct()->count('student_id'),
            'active' => (clone $summaryBase)
                ->whereHas('student.user', fn ($query) => $query->where('is_active', true))
                ->distinct()
                ->count('student_id'),
            'inactive' => (clone $summaryBase)
                ->whereHas('student.user', fn ($query) => $query->where('is_active', false))
                ->distinct()
                ->count('student_id'),
            'incomplete' => (clone $summaryBase)
                ->whereHas('student', function ($query): void {
                    $query->where(function ($profileQuery): void {
                        $profileQuery->whereNull('nis')
                            ->orWhere('nis', '')
                            ->orWhereNull('class_name')
                            ->orWhere('class_name', '')
                            ->orWhereNull('gender');
                    });
                })
                ->distinct()
                ->count('student_id'),
        ];

        return view('coach.extracurriculars.participants', [
            'extracurricular' => $extracurricular,
            'participants' => $participants,
            'participantSummary' => $participantSummary,
            'search' => $filters['search'] ?? '',
            'className' => $filters['class_name'] ?? '',
            'status' => $filters['status'] ?? '',
            'sort' => $filters['sort'],
            'direction' => $filters['direction'],
            'perPage' => $filters['per_page'],
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
        ]);
    }

    public function destroyParticipant(Extracurricular $extracurricular, Registration $registration): RedirectResponse
    {
        $this->authorize('viewByCoach', $extracurricular);
        abort_unless((int) $registration->extracurricular_id === (int) $extracurricular->id, 404);

        if (! $registration->canCoachRemoveParticipant()) {
            return redirect()
                ->route('coach.extracurriculars.participants', $extracurricular)
                ->with('error', 'Peserta ini sudah tidak aktif di ekstrakurikuler.');
        }

        DB::transaction(function () use ($registration, $extracurricular): void {
            $registration->loadMissing(['student.user']);

            $registration->scheduleParticipants()->delete();
            $registration->talentTestParticipants()->delete();
            $registration->update([
                'status' => Registration::STATUS_CANCELLED,
                'notes' => $this->appendCancellationNote(
                    $registration->notes,
                    'Dikeluarkan oleh pembina pada '.Carbon::now()->format('d-m-Y H:i')
                ),
                'willing_to_take_test' => false,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            app(NotificationCenter::class)->notifyUser($registration->student->user, [
                'title' => 'Status keikutsertaan diperbarui',
                'message' => "Pembina mengeluarkan Anda dari {$extracurricular->name}.",
                'url' => route('student.registrations.index'),
                'category' => NotificationPreference::CATEGORY_REGISTRATION_STATUS,
                'icon' => 'bi-person-x',
                'tag' => 'registration-removed-'.$registration->id,
            ]);
        });

        return redirect()
            ->route('coach.extracurriculars.participants', $extracurricular)
            ->with('success', 'Peserta berhasil dikeluarkan dari ekstrakurikuler.');
    }

    private function appendCancellationNote(?string $existingNotes, string $newNote): string
    {
        $existing = trim((string) $existingNotes);

        return $existing !== ''
            ? $existing."\n\n".$newNote
            : $newNote;
    }
}
