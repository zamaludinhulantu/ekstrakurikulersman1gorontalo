<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Student;
use App\Support\NotificationCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                Registration::STATUS_PENDING,
                'waiting_test',
                'scheduled_test',
                Registration::STATUS_APPROVED,
                Registration::STATUS_REJECTED,
            ])],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(Extracurricular::categoryDefinitions())])],
        ]);

        $filters['class_name'] = Student::normalizeClassName($filters['class_name'] ?? null);
        $filters['category'] = $filters['category'] ?? 'all';

        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? '';
        $extracurricularId = (string) ($filters['extracurricular_id'] ?? '');
        $ownedExtracurricularIds = $coach->extracurriculars()->pluck('extracurriculars.id');

        $registrations = $this->filteredStudentsQuery($filters, $ownedExtracurricularIds)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('coach.registrations.index', [
            'registrations' => $registrations,
            'search' => $search,
            'status' => $status,
            'extracurricularId' => $extracurricularId,
            'className' => $filters['class_name'] ?? '',
            'gender' => $filters['gender'] ?? '',
            'category' => $filters['category'] ?? 'all',
            'extracurriculars' => Extracurricular::whereIn('id', $ownedExtracurricularIds)->orderBy('name')->get(),
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
            'categories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->values(),
            'talentTestScheduleOptions' => $this->reusableTalentTestSchedules($ownedExtracurricularIds->all()),
        ]);
    }

    private function filteredStudentsQuery(array $filters, $ownedExtracurricularIds)
    {
        return Student::with([
            'user',
            'registrations' => function ($query) use ($filters, $ownedExtracurricularIds): void {
                $this->applyRegistrationFilters(
                    $query->with(['extracurricular', 'verifier', 'talentTestResults', 'talentTestParticipants.schedule'])
                        ->whereIn('extracurricular_id', $ownedExtracurricularIds),
                    $filters
                );
            },
        ])
            ->whereHas('registrations', function ($query) use ($filters, $ownedExtracurricularIds): void {
                $this->applyRegistrationFilters(
                    $query->whereIn('extracurricular_id', $ownedExtracurricularIds),
                    $filters
                );
            })
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($studentQuery) use ($searchValue): void {
                    $studentQuery->where('nis', 'like', "%{$searchValue}%")
                        ->orWhere('class_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('registrations.extracurricular', function ($activityQuery) use ($searchValue): void {
                            $activityQuery->where('name', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->when($filters['class_name'] ?? null, function ($query, $className): void {
                $query->whereRaw(
                    Student::normalizedClassExpression('class_name').' = ?',
                    [Student::normalizedClassComparable($className)]
                );
            })
            ->when($filters['gender'] ?? null, fn ($query, $gender) => $query->where('gender', $gender));
    }

    private function applyRegistrationFilters($query, array $filters): void
    {
        $query
            ->when($filters['status'] ?? null, function ($query, $statusValue): void {
                if ($statusValue === 'waiting_test') {
                    $query->where('status', Registration::STATUS_PENDING)
                        ->where('willing_to_take_test', true)
                        ->whereDoesntHave('talentTestParticipants')
                        ->whereDoesntHave('talentTestResults', fn ($resultQuery) => $resultQuery->where('status', 'published'));

                    return;
                }

                if ($statusValue === 'scheduled_test') {
                    $query->where('status', Registration::STATUS_PENDING)
                        ->where('willing_to_take_test', true)
                        ->whereHas('talentTestParticipants')
                        ->whereDoesntHave('talentTestResults', fn ($resultQuery) => $resultQuery->where('status', 'published'));

                    return;
                }

                if ($statusValue === Registration::STATUS_APPROVED) {
                    $query->where('status', Registration::STATUS_APPROVED)
                        ->where(function ($approvedQuery): void {
                            $approvedQuery->where('willing_to_take_test', false)
                                ->orWhereHas('talentTestResults', fn ($resultQuery) => $resultQuery->where('status', 'published'));
                        });

                    return;
                }

                $query->where('status', $statusValue);
            })
            ->when($filters['extracurricular_id'] ?? null, fn ($query, $idValue) => $query->where('extracurricular_id', $idValue))
            ->when(($filters['category'] ?? 'all') !== 'all', function ($query) use ($filters): void {
                $ids = Extracurricular::idsForCategory($filters['category']);

                if ($ids === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereIn('extracurricular_id', $ids);
            })
            ->latest('registration_date')
            ->latest('id');
    }

    public function show(Registration $registration): View
    {
        $this->authorize('manageByCoach', $registration);

        $registration->load([
            'student.user',
            'extracurricular.coaches.user',
            'talentTestParticipants.schedule',
            'talentTestResults.schedule',
        ]);

        return view('coach.registrations.show', compact('registration'));
    }

    public function redirectStatus(): RedirectResponse
    {
        return redirect()->route('coach.registrations.index');
    }

    public function updateStatus(Request $request, Registration $registration): RedirectResponse
    {
        $this->authorize('manageByCoach', $registration);
        $coach = auth()->user()->coach;
        abort_unless($coach, 404, 'Data pembina tidak ditemukan.');

        $validator = Validator::make($request->all(), [
            'decision' => ['required', Rule::in(['approve', 'schedule_test', 'reject'])],
            'notes' => ['nullable', 'string'],
            'existing_schedule_id' => ['nullable', 'integer'],
            'schedule_title' => ['nullable', 'string', 'max:255'],
            'schedule_date' => ['nullable', 'date'],
            'schedule_start_time' => ['nullable'],
            'schedule_end_time' => ['nullable', 'after:schedule_start_time'],
            'schedule_location' => ['nullable', 'string', 'max:255'],
            'schedule_description' => ['nullable', 'string'],
        ], [
            'decision.required' => 'Keputusan verifikasi wajib dipilih.',
            'decision.in' => 'Keputusan verifikasi tidak valid.',
            'schedule_date.date' => 'Tanggal tes tidak valid.',
            'schedule_end_time.after' => 'Jam selesai tes harus setelah jam mulai.',
        ]);

        $validator->after(function ($validator) use ($registration, $coach): void {
            $data = $validator->getData();
            if (($data['decision'] ?? null) !== 'schedule_test') {
                return;
            }

            if (filled($data['existing_schedule_id'] ?? null)) {
                $schedule = Schedule::query()->find($data['existing_schedule_id']);

                if (! $schedule || ! $schedule->isTalentTest()) {
                    $validator->errors()->add('existing_schedule_id', 'Jadwal tes yang dipilih tidak valid.');

                    return;
                }

                if ((int) $schedule->extracurricular_id !== (int) $registration->extracurricular_id) {
                    $validator->errors()->add('existing_schedule_id', 'Jadwal tes harus berasal dari ekstrakurikuler yang sama.');

                    return;
                }

                if ($schedule->status !== 'scheduled') {
                    $validator->errors()->add('existing_schedule_id', 'Hanya jadwal tes yang masih aktif yang bisa dipakai ulang.');

                    return;
                }

                if ((int) $schedule->coach_id !== (int) $coach->id) {
                    $validator->errors()->add('existing_schedule_id', 'Anda hanya bisa memakai jadwal tes milik binaan Anda.');
                }

                return;
            }

            $requiredFields = [
                'schedule_title' => 'Judul tes wajib diisi saat memilih jadwalkan tes.',
                'schedule_date' => 'Tanggal tes wajib diisi saat memilih jadwalkan tes.',
                'schedule_start_time' => 'Jam mulai tes wajib diisi saat memilih jadwalkan tes.',
                'schedule_end_time' => 'Jam selesai tes wajib diisi saat memilih jadwalkan tes.',
                'schedule_location' => 'Lokasi tes wajib diisi saat memilih jadwalkan tes.',
            ];

            foreach ($requiredFields as $field => $message) {
                if (! filled($data[$field] ?? null)) {
                    $validator->errors()->add($field, $message);
                }
            }
        });

        $validated = $validator->validate();

        DB::transaction(function () use ($registration, $validated, $coach): void {
            $decision = $validated['decision'];
            $status = match ($decision) {
                'approve' => Registration::STATUS_APPROVED,
                'reject' => Registration::STATUS_REJECTED,
                default => Registration::STATUS_PENDING,
            };

            $registration->update([
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
                'willing_to_take_test' => $decision === 'schedule_test',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            if ($decision === 'schedule_test') {
                if (! empty($validated['existing_schedule_id'])) {
                    $schedule = Schedule::query()->findOrFail($validated['existing_schedule_id']);
                } else {
                    $schedule = Schedule::create([
                        'extracurricular_id' => $registration->extracurricular_id,
                        'coach_id' => $coach->id,
                        'schedule_type' => 'talent_test',
                        'title' => $validated['schedule_title'],
                        'activity_date' => $validated['schedule_date'],
                        'start_time' => $validated['schedule_start_time'],
                        'end_time' => $validated['schedule_end_time'],
                        'location' => $validated['schedule_location'],
                        'description' => $validated['schedule_description'] ?? $validated['notes'] ?? null,
                        'status' => 'scheduled',
                    ]);
                }

                $schedule->talentTestParticipants()->firstOrCreate(
                    [
                        'registration_id' => $registration->id,
                        'student_id' => $registration->student_id,
                    ],
                    [
                        'assigned_by' => auth()->id(),
                        'attendance_status' => 'pending',
                        'attendance_notes' => null,
                    ]
                );
            }

            $registration->loadMissing(['student.user', 'extracurricular']);

            $message = match ($decision) {
                'approve' => "Pendaftaran Anda untuk {$registration->extracurricular->name} telah diterima oleh pembina.",
                'reject' => "Pendaftaran Anda untuk {$registration->extracurricular->name} ditolak. Buka detail untuk melihat catatan pembina.",
                default => "Tes bakat untuk {$registration->extracurricular->name} telah dijadwalkan. Silakan cek jadwal terbaru Anda.",
            };

            app(NotificationCenter::class)->notifyUser($registration->student->user, [
                'title' => 'Status pendaftaran diperbarui',
                'message' => $message,
                'url' => route('student.registrations.index'),
                'category' => NotificationPreference::CATEGORY_REGISTRATION_STATUS,
                'icon' => $decision === 'reject' ? 'bi-x-circle' : 'bi-check2-circle',
                'tag' => 'registration-status-'.$registration->id,
            ]);
        });

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    private function reusableTalentTestSchedules(array $extracurricularIds): array
    {
        return Schedule::query()
            ->where('schedule_type', 'talent_test')
            ->where('status', 'scheduled')
            ->whereIn('extracurricular_id', $extracurricularIds)
            ->whereDate('activity_date', '>=', now()->toDateString())
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy('extracurricular_id')
            ->map(fn (Collection $items) => $items->map(function (Schedule $schedule): array {
                $dateLabel = optional($schedule->activity_date)->format('d-m-Y') ?? '-';
                $timeLabel = trim(($schedule->start_time ?: '-') . ' - ' . ($schedule->end_time ?: '-'));

                return [
                    'id' => (string) $schedule->id,
                    'label' => "{$schedule->title} | {$dateLabel} | {$timeLabel} | {$schedule->location}",
                ];
            })->values()->all())
            ->all();
    }
}
