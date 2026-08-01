<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SanitizesCsvExports;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Extracurricular;
use App\Models\NotificationPreference;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Student;
use App\Support\NotificationCenter;
use App\Support\RegistrationCancellationManager;
use App\Support\RegistrationStatusPresenter;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    use SanitizesCsvExports;

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);

        $registrations = $this->filteredRegistrationsQuery($filters)
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();

        return view('admin.registrations.index', [
            'registrations' => $registrations,
            'statistics' => RegistrationStatusPresenter::statistics(
                Registration::query()->where('status', '!=', Registration::STATUS_CANCELLED)
            ),
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'extracurricularId' => $filters['extracurricular_id'] ?? '',
            'className' => $filters['class_name'] ?? '',
            'gender' => $filters['gender'] ?? '',
            'category' => $filters['category'] ?? 'all',
            'dateFrom' => $filters['date_from'] ?? '',
            'dateTo' => $filters['date_to'] ?? '',
            'perPage' => $filters['per_page'] ?? 20,
            'extracurriculars' => Extracurricular::orderBy('name')->get(),
            'classOptions' => collect(array_keys(Student::registrationClassOptions())),
            'categories' => collect(Extracurricular::categoryDefinitions())
                ->map(fn (array $definition) => ['key' => $definition['key'], 'label' => $definition['label']])
                ->values(),
            'statusMap' => RegistrationStatusPresenter::managementLabels(),
            'talentTestScheduleOptions' => $this->reusableTalentTestSchedules(),
        ]);
    }

    public function show(Registration $registration): View
    {
        $registration->load([
            'student.user',
            'extracurricular.coaches.user',
            'talentTestParticipants.schedule',
            'talentTestResults.schedule',
        ]);

        return view('admin.registrations.show', compact('registration'));
    }

    public function redirectStatus(): RedirectResponse
    {
        return redirect()->route('admin.registrations.index');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validateFilters($request, true);
        $format = $filters['format'] ?? 'xls';
        $registrations = $this->filteredRegistrationsQuery($filters)->get();
        $timestamp = Carbon::now()->format('YmdHis');
        $filterSummary = $this->filterSummary($filters);
        $filenameBase = $this->exportFilenameBase($filters, $filterSummary);

        if ($format === 'pdf') {
            $html = view('admin.registrations.export-pdf', [
                'registrations' => $registrations,
                'filters' => $filters,
                'filterSummary' => $filterSummary,
                'statusMap' => $this->statusLabels(),
            ])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A3', 'landscape');
            $dompdf->render();

            return response()->streamDownload(
                static function () use ($dompdf): void {
                    echo $dompdf->output();
                },
                $filenameBase.'-'.$timestamp.'.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }

        $filename = $filenameBase.'-'.$timestamp.'.xls';
        $html = view('admin.registrations.export-xls', [
            'registrations' => $registrations,
            'filters' => $filters,
            'filterSummary' => $filterSummary,
            'statusMap' => $this->statusLabels(),
        ])->render();

        return response()->streamDownload(function () use ($html): void {
            echo "\xEF\xBB\xBF";
            echo $html;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function updateStatus(Request $request, Registration $registration): RedirectResponse
    {
        if ($registration->isCancellationRequested()) {
            return back()->with('error', 'Selesaikan permintaan pembatalan sebelum mengubah keputusan pendaftaran.');
        }

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

        $validator->after(function ($validator) use ($registration): void {
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

        DB::transaction(function () use ($registration, $validated): void {
            $decision = $validated['decision'];
            $previousDisplayStatus = $registration->displayStatus();
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
                    $extracurricular = $registration->extracurricular()->with('coaches:id')->firstOrFail();
                    $schedule = Schedule::create([
                        'extracurricular_id' => $registration->extracurricular_id,
                        'coach_id' => $extracurricular->coaches->first()?->id ?? $extracurricular->coach_id,
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
                'approve' => "Status pendaftaran Anda untuk {$registration->extracurricular->name} telah diterima.",
                'reject' => "Status pendaftaran Anda untuk {$registration->extracurricular->name} ditolak. Buka detail untuk melihat catatan verifikasi.",
                default => "Jadwal tes bakat untuk {$registration->extracurricular->name} telah disiapkan. Silakan cek jadwal terbaru Anda.",
            };

            app(NotificationCenter::class)->notifyUser($registration->student->user, [
                'title' => 'Status pendaftaran diperbarui',
                'message' => $message,
                'url' => route('student.registrations.index'),
                'category' => NotificationPreference::CATEGORY_REGISTRATION_STATUS,
                'icon' => $decision === 'reject' ? 'bi-x-circle' : 'bi-check2-circle',
                'tag' => 'registration-status-'.$registration->id,
            ]);

            $this->recordRegistrationAudit($registration, $decision, $previousDisplayStatus);
        });

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    public function reviewCancellation(
        Request $request,
        Registration $registration,
        RegistrationCancellationManager $cancellationManager
    ): RedirectResponse {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
        ]);

        if (! $registration->isCancellationRequested()) {
            return back()->with('error', 'Permintaan pembatalan sudah tidak tersedia.');
        }

        $registration->loadMissing(['student.user', 'extracurricular']);
        $approved = $validated['decision'] === 'approve';
        $note = $approved
            ? 'Permintaan pembatalan disetujui Admin pada '.now()->format('d-m-Y H:i')
            : 'Permintaan pembatalan ditolak Admin pada '.now()->format('d-m-Y H:i');

        $registration = $approved
            ? $cancellationManager->approve($registration, $request->user(), $note)
            : $cancellationManager->reject($registration, $request->user(), $note);

        $registration->loadMissing(['student.user', 'extracurricular']);
        app(NotificationCenter::class)->notifyUser($registration->student->user, [
            'title' => $approved ? 'Pembatalan disetujui' : 'Pembatalan ditolak',
            'message' => $approved
                ? "Pembatalan keikutsertaan Anda di {$registration->extracurricular->name} telah disetujui."
                : "Permintaan pembatalan Anda di {$registration->extracurricular->name} ditolak. Keikutsertaan Anda tetap aktif.",
            'url' => route('student.registrations.index'),
            'category' => NotificationPreference::CATEGORY_REGISTRATION_STATUS,
            'icon' => $approved ? 'bi-check-circle' : 'bi-x-circle',
            'tag' => 'registration-cancellation-reviewed-'.$registration->id,
        ]);

        $this->recordCancellationAudit($registration, $approved);

        return back()->with(
            'success',
            $approved ? 'Pembatalan keikutsertaan berhasil disetujui.' : 'Permintaan pembatalan berhasil ditolak.'
        );
    }

    private function recordRegistrationAudit(Registration $registration, string $decision, string $previousDisplayStatus): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $registration->refresh();
        $registration->loadMissing(['student.user', 'extracurricular']);

        $decisionLabel = match ($decision) {
            'approve' => 'menerima pendaftaran',
            'reject' => 'menolak pendaftaran',
            default => 'menjadwalkan tes pendaftaran',
        };

        AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'registration.verified',
            'subject_type' => Registration::class,
            'subject_id' => $registration->id,
            'description' => "Admin {$user->name} {$decisionLabel} untuk {$registration->student->user->name} di {$registration->extracurricular->name}.",
            'metadata' => [
                'actor_name' => $user->name,
                'actor_role' => $user->roleLabel(),
                'student_name' => $registration->student->user->name,
                'extracurricular_name' => $registration->extracurricular->name,
                'decision' => $decision,
                'previous_status' => $previousDisplayStatus,
                'current_status' => $registration->displayStatus(),
                'verified_at' => optional($registration->verified_at)->format('d-m-Y H:i:s'),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function recordCancellationAudit(Registration $registration, bool $approved): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'registration.cancellation_reviewed',
            'subject_type' => Registration::class,
            'subject_id' => $registration->id,
            'description' => "Admin {$user->name} ".($approved ? 'menyetujui' : 'menolak')." pembatalan pendaftaran {$registration->student->user->name} di {$registration->extracurricular->name}.",
            'metadata' => [
                'actor_name' => $user->name,
                'actor_role' => $user->roleLabel(),
                'student_name' => $registration->student->user->name,
                'extracurricular_name' => $registration->extracurricular->name,
                'decision' => $approved ? 'approve' : 'reject',
                'current_status' => $registration->displayStatus(),
                'verified_at' => optional($registration->verified_at)->format('d-m-Y H:i:s'),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function validateFilters(Request $request, bool $includeFormat = false): array
    {
        $rules = [
            'search' => ['nullable', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'status' => ['nullable', Rule::in([
                Registration::STATUS_PENDING,
                'waiting_test',
                'scheduled_test',
                Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED,
                Registration::STATUS_APPROVED,
                Registration::STATUS_REJECTED,
            ])],
            'extracurricular_id' => ['nullable', 'exists:extracurriculars,id'],
            'category' => ['nullable', 'string', Rule::in(['all', ...array_keys(Extracurricular::categoryDefinitions())])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50])],
        ];

        if ($includeFormat) {
            $rules['format'] = ['nullable', Rule::in(['pdf', 'xls'])];
        }

        $validated = $request->validate($rules);
        $validated['class_name'] = Student::normalizeClassName($validated['class_name'] ?? null);
        $validated['category'] = $validated['category'] ?? 'all';

        return $validated;
    }

    private function filteredRegistrationsQuery(array $filters)
    {
        return Registration::with([
            'student.user',
            'student.registrations:id,student_id,status',
            'extracurricular',
            'verifier',
            'talentTestResults',
        ])
            ->where('status', '!=', Registration::STATUS_CANCELLED)
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($searchQuery) use ($searchValue): void {
                    $searchQuery->whereHas('student.user', function ($userQuery) use ($searchValue): void {
                        $userQuery->where('name', 'like', "%{$searchValue}%")
                            ->orWhere('email', 'like', "%{$searchValue}%");
                    })->orWhereHas('student', function ($studentQuery) use ($searchValue): void {
                        $studentQuery->where('nis', 'like', "%{$searchValue}%")
                            ->orWhere('class_name', 'like', "%{$searchValue}%");
                    })->orWhereHas('extracurricular', function ($activityQuery) use ($searchValue): void {
                        $activityQuery->where('name', 'like', "%{$searchValue}%");
                    })->orWhere('selected_branch', 'like', "%{$searchValue}%");
                });
            })
            ->when($filters['class_name'] ?? null, function ($query, $className): void {
                $query->whereHas('student', function ($studentQuery) use ($className): void {
                    $studentQuery->whereRaw(
                        Student::normalizedClassExpression('class_name').' = ?',
                        [Student::normalizedClassComparable($className)]
                    );
                });
            })
            ->when($filters['gender'] ?? null, function ($query, $gender): void {
                $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('gender', $gender));
            })
            ->with(['talentTestParticipants.schedule'])
            ->when(
                filled($filters['status'] ?? null)
                    && $filters['status'] !== Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED,
                fn ($query) => $query->whereNull('cancellation_requested_at')
            )
            ->when($filters['status'] ?? null, function ($query, $statusValue): void {
                if ($statusValue === Registration::DISPLAY_STATUS_CANCELLATION_REQUESTED) {
                    $query->whereNotNull('cancellation_requested_at');

                    return;
                }

                if ($statusValue === Registration::STATUS_PENDING) {
                    $query->where('status', Registration::STATUS_PENDING)
                        ->where(function ($pendingQuery): void {
                            $pendingQuery->where('willing_to_take_test', false)
                                ->orWhereNull('willing_to_take_test')
                                ->orWhereHas('talentTestResults', fn ($resultQuery) => $resultQuery->where('status', 'published'));
                        });

                    return;
                }

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
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('registration_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('registration_date', '<=', $date))
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

    private function filteredStudentsQuery(array $filters)
    {
        return Student::with([
            'user',
            'registrations' => function ($query) use ($filters): void {
                $this->applyRegistrationFilters(
                    $query->with(['extracurricular', 'verifier', 'talentTestResults', 'talentTestParticipants.schedule']),
                    $filters
                );
            },
        ])
            ->whereHas('registrations', function ($query) use ($filters): void {
                $this->applyRegistrationFilters($query, $filters);
            })
            ->when($filters['search'] ?? null, function ($query, $searchValue) {
                $query->where(function ($studentQuery) use ($searchValue): void {
                    $studentQuery->where('nis', 'like', "%{$searchValue}%")
                        ->orWhere('class_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchValue): void {
                            $userQuery->where('name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
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
            ->when($filters['gender'] ?? null, fn ($query, $gender) => $query->where('gender', $gender))
            ->latest();
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

    private function statusLabels(): array
    {
        return RegistrationStatusPresenter::labels();
    }

    private function filterSummary(array $filters): array
    {
        $extracurricular = null;
        if (! empty($filters['extracurricular_id'])) {
            $extracurricular = Extracurricular::query()->find($filters['extracurricular_id']);
        }

        $categoryDefinition = ($filters['category'] ?? 'all') !== 'all'
            ? collect(Extracurricular::categoryDefinitions())->firstWhere('key', $filters['category'])
            : null;

        return [
            'search' => filled($filters['search'] ?? null) ? $filters['search'] : 'Semua siswa',
            'status' => $this->statusLabels()[$filters['status'] ?? ''] ?? 'Semua status',
            'extracurricular' => $extracurricular?->name ?? 'Semua kegiatan',
            'class_name' => $filters['class_name'] ?? 'Semua kelas',
            'date_from' => $filters['date_from'] ?? 'Semua tanggal',
            'date_to' => $filters['date_to'] ?? 'Semua tanggal',
            'gender' => match ($filters['gender'] ?? null) {
                'L' => 'Laki-laki',
                'P' => 'Perempuan',
                default => 'Semua jenis kelamin',
            },
            'category' => $categoryDefinition['label'] ?? 'Semua kategori',
        ];
    }

    private function exportFilenameBase(array $filters, array $summary): string
    {
        $segments = ['pendaftar'];

        if (($filters['category'] ?? 'all') !== 'all') {
            $segments[] = $summary['category'];
        }

        if (! empty($filters['extracurricular_id']) && $summary['extracurricular'] !== 'Semua kegiatan') {
            $segments[] = $summary['extracurricular'];
        }

        if (! empty($filters['class_name']) && $summary['class_name'] !== 'Semua kelas') {
            $segments[] = 'kelas-'.$summary['class_name'];
        }

        if (! empty($filters['status']) && $summary['status'] !== 'Semua status') {
            $segments[] = $summary['status'];
        }

        return Str::slug(implode('-', $segments));
    }

    private function reusableTalentTestSchedules(): array
    {
        return Schedule::query()
            ->where('schedule_type', 'talent_test')
            ->where('status', 'scheduled')
            ->whereDate('activity_date', '>=', Carbon::today())
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
