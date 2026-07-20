@extends('layouts.app')

@section('page_title', 'Kelola Presensi Peserta')
@section('page_subtitle', 'Kelola kehadiran banyak peserta dengan alur yang lebih cepat dan ringkas.')

@php
    $statusOptions = [
        'present' => 'Hadir',
        'late' => 'Terlambat',
        'permission' => 'Izin',
        'sick' => 'Sakit',
        'absent' => 'Tidak Hadir',
    ];
    $sortedParticipants = $participants
        ->sortBy(fn ($participant) => strtolower($participant->student->user->name ?? ''))
        ->values();
    $classOptions = $sortedParticipants
        ->map(fn ($participant) => $participant->student->class_name ?: 'Belum diatur')
        ->filter()
        ->unique()
        ->values();
@endphp

@push('styles')
    <style>
        .attendance-page {
            display: grid;
            gap: 1rem;
        }

        .attendance-schedule-panel,
        .attendance-filter-panel,
        .attendance-list-panel,
        .attendance-bulk-panel {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.05);
        }

        .attendance-schedule-panel,
        .attendance-filter-panel,
        .attendance-bulk-panel,
        .attendance-list-panel__header,
        .attendance-list-panel__footer {
            padding: 1rem 1.15rem;
        }

        .attendance-filter-panel,
        .attendance-list-panel {
            overflow: hidden;
        }

        .attendance-schedule-grid,
        .attendance-summary-grid {
            display: grid;
            gap: 0.85rem;
        }

        .attendance-schedule-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .attendance-summary-grid {
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }

        .attendance-schedule-item,
        .attendance-summary-card {
            padding: 0.9rem 0.95rem;
            border-radius: 20px;
            border: 1px solid #e0e9f3;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .attendance-schedule-item span,
        .attendance-summary-card .label {
            display: block;
            margin-bottom: 0.3rem;
            color: #677b93;
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .attendance-schedule-item strong,
        .attendance-summary-card .value {
            color: #18334f;
            font-size: 1rem;
            font-weight: 800;
        }

        .attendance-summary-card .value {
            font-size: 1.2rem;
            line-height: 1;
        }

        .attendance-summary-card .meta {
            margin-top: 0.35rem;
            color: #60748c;
            font-size: 0.78rem;
        }

        .attendance-filter-panel__header,
        .attendance-list-panel__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.9rem;
            padding: 1rem 1.15rem 0.85rem;
            border-bottom: 1px solid #e8eef5;
        }

        .attendance-filter-panel__header h2,
        .attendance-list-panel__header h2 {
            margin: 0 0 0.3rem;
            font-size: 1.02rem;
            font-weight: 800;
            color: #18334f;
        }

        .attendance-filter-panel__header p,
        .attendance-list-panel__header p {
            margin: 0;
            color: #667b93;
            font-size: 0.83rem;
        }

        .attendance-filter-panel__body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .attendance-quick-form,
        .attendance-bulk-tools {
            display: grid;
            gap: 0.75rem;
        }

        .attendance-quick-form {
            grid-template-columns: minmax(0, 1.8fr) repeat(2, minmax(0, 1fr)) auto;
            align-items: end;
        }

        .attendance-bulk-tools {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            align-items: end;
        }

        .attendance-bulk-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .attendance-bulk-actions .btn,
        .attendance-list-panel__header .btn,
        .attendance-list-panel__footer .btn {
            min-height: 38px;
        }

        .attendance-list-panel__body {
            padding: 0.85rem 1.15rem 1rem;
        }

        .attendance-list-head,
        .attendance-participant-row {
            display: grid;
            grid-template-columns: minmax(230px, 1.35fr) 110px minmax(300px, 1.7fr) minmax(220px, 1fr) 130px 70px;
            gap: 0.8rem;
            align-items: start;
        }

        .attendance-list-head {
            padding: 0 0.15rem 0.65rem;
            color: #667b93;
            font-size: 0.73rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .attendance-participant-row {
            padding: 0.95rem;
            border-radius: 22px;
            border: 1px solid #e1eaf4;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }

        .attendance-participant-row.is-selected {
            border-color: #8fb2f7;
            background: linear-gradient(180deg, #fafdff 0%, #edf4ff 100%);
            box-shadow: 0 12px 28px rgba(31, 94, 255, 0.08);
        }

        .attendance-participant-row + .attendance-participant-row {
            margin-top: 0.8rem;
        }

        .attendance-student {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .attendance-avatar {
            width: 2.6rem;
            height: 2.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #1f5eff 0%, #74b6ff 100%);
            color: #fff;
            font-size: 0.82rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .attendance-student strong,
        .attendance-student small {
            display: block;
        }

        .attendance-student small,
        .attendance-note-help {
            color: #667b93;
            font-size: 0.78rem;
        }

        .attendance-mobile-label {
            display: none;
            margin-bottom: 0.35rem;
            color: #667b93;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .attendance-status-picker {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .attendance-status-pill {
            border: 1px solid #d7e3f1;
            border-radius: 999px;
            padding: 0.42rem 0.72rem;
            background: #fff;
            color: #54708f;
            font-size: 0.76rem;
            font-weight: 800;
            line-height: 1;
            transition: border-color 0.18s ease, background-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        }

        .attendance-status-pill.is-active {
            border-color: #9ab9f7;
            background: linear-gradient(180deg, #fafdff 0%, #eef5ff 100%);
            color: #1f4ec4;
            box-shadow: 0 10px 20px rgba(31, 94, 255, 0.08);
        }

        .attendance-note-toggle {
            min-height: 34px;
        }

        .attendance-note-wrap[hidden] {
            display: none !important;
        }

        .attendance-note-wrap textarea {
            min-height: 72px;
            resize: vertical;
        }

        .attendance-fill-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .attendance-fill-badge[data-fill-state="pending"] {
            color: #8b5b00;
            border-color: #f6ddb1;
            background: #fff8e7;
        }

        .attendance-fill-badge[data-fill-state="draft"] {
            color: #8f5a00;
            border-color: #f2d08b;
            background: #fff5d8;
        }

        .attendance-fill-badge[data-fill-state="saved"] {
            color: #177245;
            border-color: #bde4cf;
            background: #eaf8f0;
        }

        .attendance-list-panel__footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.8rem;
            border-top: 1px solid #e8eef5;
        }

        .attendance-footer-actions {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        @media (max-width: 1199.98px) {
            .attendance-summary-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .attendance-schedule-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .attendance-bulk-tools {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .attendance-quick-form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .attendance-list-head {
                display: none;
            }

            .attendance-participant-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .attendance-cell--student,
            .attendance-cell--status,
            .attendance-cell--note {
                grid-column: 1 / -1;
            }

            .attendance-mobile-label {
                display: block;
            }
        }

        @media (max-width: 767.98px) {
            .attendance-schedule-grid,
            .attendance-summary-grid,
            .attendance-quick-form,
            .attendance-bulk-tools,
            .attendance-participant-row,
            .attendance-list-panel__footer {
                grid-template-columns: 1fr;
            }

            .attendance-schedule-panel,
            .attendance-filter-panel__body,
            .attendance-bulk-panel,
            .attendance-list-panel__body,
            .attendance-list-panel__footer {
                padding-inline: 0.95rem;
            }

            .attendance-filter-panel__header,
            .attendance-list-panel__header {
                flex-direction: column;
            }

            .attendance-schedule-panel,
            .attendance-filter-panel,
            .attendance-list-panel,
            .attendance-bulk-panel,
            .attendance-schedule-item,
            .attendance-summary-card,
            .attendance-participant-row,
            .attendance-cell,
            .attendance-mobile-label,
            .attendance-note-help,
            .attendance-list-panel__footer,
            .attendance-filter-panel__header,
            .attendance-list-panel__header {
                text-align: center;
            }

            .attendance-participant-row {
                justify-items: center;
                gap: 0.75rem;
                padding: 1rem 0.9rem;
            }

            .attendance-cell {
                width: 100%;
                max-width: 320px;
            }

            .attendance-student {
                justify-content: center;
                text-align: center;
            }

            .attendance-status-picker,
            .attendance-note-wrap,
            .attendance-note-toggle,
            .attendance-fill-badge,
            .attendance-cell--select .form-check {
                margin-inline: auto;
            }

            .attendance-cell--select .form-check {
                display: flex;
                justify-content: center;
            }

            .attendance-note-wrap {
                width: 100%;
                max-width: 320px;
            }

            .attendance-mobile-label {
                margin-bottom: 0.25rem;
                font-size: 0.68rem;
            }

            .attendance-cell--class,
            .attendance-cell--status,
            .attendance-cell--note,
            .attendance-cell--meta,
            .attendance-cell--select {
                padding-top: 0.1rem;
            }

            .attendance-cell--class > div:last-child,
            .attendance-cell--meta > span:last-child,
            .attendance-cell--select .form-check,
            .attendance-note-help {
                max-width: 220px;
                margin-inline: auto;
            }

            .attendance-cell--status .form-select,
            .attendance-note-toggle {
                max-width: 240px;
                margin-inline: auto;
            }

            .attendance-note-help {
                margin-top: 0.35rem !important;
                font-size: 0.74rem;
                line-height: 1.45;
            }

            .attendance-fill-badge {
                min-width: 92px;
            }

            .attendance-note-toggle {
                justify-content: center;
            }

            .attendance-status-picker {
                display: none;
            }

            .attendance-footer-actions,
            .attendance-bulk-actions {
                width: 100%;
            }

            .attendance-footer-actions .btn,
            .attendance-bulk-actions .btn {
                flex: 1 1 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="attendance-page">
        <div class="card">
            <div class="card-body toolbar-card">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Pilih Jadwal Presensi</h2>
                        <p class="toolbar-hint mb-0">Pilih jadwal, lalu data peserta akan dimuat otomatis tanpa perlu membuka halaman lain.</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>Ekspor
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('coach.attendances.export', array_merge(request()->query(), ['format' => 'csv'])) }}">Unduh CSV</a></li>
                            <li><a class="dropdown-item" href="{{ route('coach.attendances.export', array_merge(request()->query(), ['format' => 'xls'])) }}">Unduh Excel</a></li>
                        </ul>
                    </div>
                </div>

                <form class="toolbar-grid" method="get" action="{{ route('coach.attendances.index') }}" id="attendanceSchedulePicker">
                    <div class="toolbar-col-8">
                        <label class="form-label" for="schedule_id">Jadwal kegiatan</label>
                        <select name="schedule_id" id="schedule_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Pilih jadwal kegiatan</option>
                            @foreach($schedules as $schedule)
                                <option value="{{ $schedule->id }}" @selected((string) request('schedule_id') === (string) $schedule->id)>
                                    {{ optional($schedule->activity_date)->format('d-m-Y') }} | {{ $schedule->extracurricular->name ?? '-' }} | {{ $schedule->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="toolbar-col-2">
                        <a href="{{ route('coach.attendances.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
                    </div>
                    <div class="toolbar-col-2">
                        <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i>Muat</button>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedSchedule)
            <div class="attendance-schedule-panel">
                <div class="section-header-inline">
                    <div>
                        <h2>Ringkasan Jadwal</h2>
                        <p>Data kegiatan yang sedang dikelola untuk pengisian presensi peserta.</p>
                    </div>
                </div>
                <div class="attendance-schedule-grid">
                    <div class="attendance-schedule-item">
                        <span>Kegiatan</span>
                        <strong>{{ $selectedSchedule->title }}</strong>
                    </div>
                    <div class="attendance-schedule-item">
                        <span>Ekstrakurikuler</span>
                        <strong>{{ $selectedSchedule->extracurricular->name ?? '-' }}</strong>
                    </div>
                    <div class="attendance-schedule-item">
                        <span>Tanggal</span>
                        <strong>{{ optional($selectedSchedule->activity_date)->translatedFormat('d F Y') }}</strong>
                    </div>
                    <div class="attendance-schedule-item">
                        <span>Waktu</span>
                        <strong>{{ substr((string) $selectedSchedule->start_time, 0, 5) }} - {{ substr((string) $selectedSchedule->end_time, 0, 5) }}</strong>
                    </div>
                    <div class="attendance-schedule-item">
                        <span>Lokasi</span>
                        <strong>{{ $selectedSchedule->location ?: 'Belum ditentukan' }}</strong>
                    </div>
                </div>
            </div>

            <form method="post" action="{{ route('coach.attendances.save', $selectedSchedule) }}" id="coachAttendanceForm">
                @csrf
                <input type="hidden" name="submit_action" id="attendanceSubmitAction" value="draft">

                <div class="attendance-page">
                    <div class="attendance-summary-grid" id="attendanceSummaryGrid">
                        <div class="attendance-summary-card">
                            <div class="label">Total Peserta</div>
                            <div class="value" data-summary-total>{{ $sortedParticipants->count() }}</div>
                            <div class="meta">Seluruh anggota aktif pada jadwal ini.</div>
                        </div>
                        <div class="attendance-summary-card">
                            <div class="label">Hadir</div>
                            <div class="value" data-summary-present>0</div>
                            <div class="meta">Peserta hadir tepat waktu.</div>
                        </div>
                        <div class="attendance-summary-card">
                            <div class="label">Terlambat</div>
                            <div class="value" data-summary-late>0</div>
                            <div class="meta">Tetap tercatat hadir.</div>
                        </div>
                        <div class="attendance-summary-card">
                            <div class="label">Izin</div>
                            <div class="value" data-summary-permission>0</div>
                            <div class="meta">Tidak hadir dengan izin.</div>
                        </div>
                        <div class="attendance-summary-card">
                            <div class="label">Sakit</div>
                            <div class="value" data-summary-sick>0</div>
                            <div class="meta">Tidak hadir karena sakit.</div>
                        </div>
                        <div class="attendance-summary-card">
                            <div class="label">Tidak Hadir</div>
                            <div class="value" data-summary-absent>0</div>
                            <div class="meta">Tidak hadir tanpa status lain.</div>
                        </div>
                        <div class="attendance-summary-card">
                            <div class="label">Belum Diisi</div>
                            <div class="value" data-summary-pending>0</div>
                            <div class="meta">Masih perlu diisi pembina.</div>
                        </div>
                    </div>

                    <div class="attendance-filter-panel">
                        <div class="attendance-filter-panel__header">
                            <div>
                                <h2>Filter dan Aksi Massal</h2>
                                <p>Cari peserta lebih cepat, lalu isi presensi serentak tanpa membuka catatan satu per satu.</p>
                            </div>
                        </div>
                        <div class="attendance-filter-panel__body">
                            <div class="attendance-quick-form mb-3">
                                <div>
                                    <label class="form-label" for="attendanceSearch">Cari siswa</label>
                                    <input type="search" id="attendanceSearch" class="form-control" placeholder="Masukkan nama siswa atau NIS">
                                </div>
                                <div>
                                    <label class="form-label" for="attendanceClassFilter">Kelas</label>
                                    <select id="attendanceClassFilter" class="form-select">
                                        <option value="">Semua kelas</option>
                                        @foreach($classOptions as $className)
                                            <option value="{{ $className }}">{{ $className }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="attendanceStatusFilter">Status</label>
                                    <select id="attendanceStatusFilter" class="form-select">
                                        <option value="">Semua status</option>
                                        <option value="unfilled">Belum diisi</option>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="attendanceOnlyUnfilled">
                                        <label class="form-check-label" for="attendanceOnlyUnfilled">Hanya belum diisi</label>
                                    </div>
                                </div>
                            </div>

                            <div class="attendance-bulk-tools">
                                <div>
                                    <label class="form-label" for="attendanceBulkStatus">Status massal</label>
                                    <select id="attendanceBulkStatus" class="form-select">
                                        <option value="">Pilih status</option>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-primary w-100" id="attendanceApplySelected">
                                        <i class="bi bi-people"></i>Terapkan ke Terpilih
                                    </button>
                                </div>
                                <div class="d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-success w-100" id="attendanceMarkAllPresent">
                                        <i class="bi bi-check2-all"></i>Tandai Semua Hadir
                                    </button>
                                </div>
                                <div class="d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-secondary w-100" id="attendanceClearAll">
                                        <i class="bi bi-eraser"></i>Kosongkan Semua
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="attendance-list-panel">
                        <div class="attendance-list-panel__header">
                            <div>
                                <h2>Daftar Peserta</h2>
                                <p>Gunakan status ringkas, buka catatan hanya saat diperlukan, lalu simpan draft atau finalisasi saat sudah siap.</p>
                            </div>
                            <div class="attendance-bulk-actions">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="attendanceSelectAllVisible">
                                    <i class="bi bi-check2-square"></i>Pilih Tampil
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="attendanceClearSelection">
                                    <i class="bi bi-square"></i>Reset Pilihan
                                </button>
                                <span class="small text-muted d-inline-flex align-items-center" id="attendanceSelectionSummary">0 peserta dipilih</span>
                            </div>
                        </div>
                        <div class="attendance-list-panel__body">
                            @if($errors->has('rows'))
                                <div class="alert alert-danger app-alert mb-3">
                                    <div class="app-alert__icon"><i class="bi bi-exclamation-triangle"></i></div>
                                    <div>{{ $errors->first('rows') }}</div>
                                </div>
                            @endif

                            <div class="attendance-list-head">
                                <div>Siswa</div>
                                <div>Kelas</div>
                                <div>Status</div>
                                <div>Catatan</div>
                                <div>Pengisian</div>
                                <div>Pilih</div>
                            </div>

                            <div id="attendanceParticipantList">
                                @forelse($sortedParticipants as $index => $participant)
                                    @php
                                        $attendance = $attendanceMap[$participant->student_id] ?? null;
                                        $currentStatus = old("rows.$index.status", $attendance?->display_status ?? '');
                                        $currentNotes = old("rows.$index.notes", $attendance->notes ?? '');
                                        $className = $participant->student->class_name ?: 'Belum diatur';
                                        $hasNote = $currentNotes !== '';
                                        $fillState = 'pending';
                                        $fillStateLabel = 'Belum Diisi';
                                        if ($currentStatus !== '') {
                                            $fillState = ($attendance?->save_state === \App\Models\Attendance::SAVE_STATE_FINALIZED && old('_token') === null)
                                                ? 'saved'
                                                : 'draft';
                                            $fillStateLabel = $fillState === 'saved' ? 'Sudah Disimpan' : 'Draft';
                                        }
                                        $initials = collect(explode(' ', trim((string) ($participant->student->user->name ?? 'S'))))
                                            ->filter()
                                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                            ->take(2)
                                            ->implode('');
                                    @endphp
                                    <div
                                        class="attendance-participant-row"
                                        data-attendance-row
                                        data-student-id="{{ $participant->student_id }}"
                                        data-student-name="{{ strtolower($participant->student->user->name ?? '') }}"
                                        data-student-nis="{{ strtolower($participant->student->nis ?? '') }}"
                                        data-class-name="{{ strtolower($className) }}"
                                        data-original-status="{{ $attendance?->display_status ?? '' }}"
                                        data-original-notes="{{ $attendance->notes ?? '' }}"
                                        data-original-save-state="{{ $attendance?->save_state ?? '' }}"
                                    >
                                        <input type="hidden" name="rows[{{ $index }}][student_id]" value="{{ $participant->student_id }}">
                                        <input type="hidden" name="rows[{{ $index }}][status]" value="{{ $currentStatus }}" data-status-input>

                                        <div class="attendance-cell attendance-cell--student">
                                            <span class="attendance-mobile-label">Siswa</span>
                                            <div class="attendance-student">
                                                <span class="attendance-avatar">{{ $initials ?: 'S' }}</span>
                                                <div>
                                                    <strong>{{ $participant->student->user->name ?? '-' }}</strong>
                                                    <small>NIS: {{ $participant->student->nis ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="attendance-cell attendance-cell--class">
                                            <span class="attendance-mobile-label">Kelas</span>
                                            <div>{{ $className }}</div>
                                        </div>

                                        <div class="attendance-cell attendance-cell--status">
                                            <span class="attendance-mobile-label">Status</span>
                                            <div class="attendance-status-picker" data-status-picker>
                                                @foreach($statusOptions as $value => $label)
                                                    <button type="button" class="attendance-status-pill {{ $currentStatus === $value ? 'is-active' : '' }}" data-status-option="{{ $value }}">{{ $label }}</button>
                                                @endforeach
                                            </div>
                                            <select class="form-select form-select-sm d-md-none mt-2" data-mobile-status>
                                                <option value="">Pilih status</option>
                                                @foreach($statusOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="attendance-cell attendance-cell--note">
                                            <span class="attendance-mobile-label">Catatan</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary attendance-note-toggle" data-note-toggle>
                                                <i class="bi bi-journal-text"></i>{{ $hasNote ? 'Ubah Catatan' : 'Tambah Catatan' }}
                                            </button>
                                            <div class="attendance-note-wrap mt-2" data-note-wrap @if(!$hasNote) hidden @endif>
                                                <textarea name="rows[{{ $index }}][notes]" class="form-control form-control-sm" data-note-input placeholder="Catatan opsional untuk peserta ini">{{ $currentNotes }}</textarea>
                                            </div>
                                            <div class="attendance-note-help mt-2">Catatan hanya dibuka bila diperlukan.</div>
                                        </div>

                                        <div class="attendance-cell attendance-cell--meta">
                                            <span class="attendance-mobile-label">Status Pengisian</span>
                                            <span class="attendance-fill-badge" data-fill-state="{{ $fillState }}" data-fill-state-label>{{ $fillStateLabel }}</span>
                                        </div>

                                        <div class="attendance-cell attendance-cell--select">
                                            <span class="attendance-mobile-label">Pilih</span>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $participant->student_id }}" data-selection-input>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state py-4">
                                        <div class="icon"><i class="bi bi-people"></i></div>
                                        <p class="mb-0">Belum ada peserta aktif pada ekstrakurikuler ini.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if($sortedParticipants->isNotEmpty())
                            <div class="attendance-list-panel__footer">
                                <div class="small text-muted">
                                    Presensi kosong tidak otomatis dianggap tidak hadir. Isi hanya peserta yang memang sudah diputuskan statusnya.
                                </div>
                                <div class="attendance-footer-actions">
                                    <button type="button" class="btn btn-outline-secondary" data-submit-action="draft">
                                        <i class="bi bi-save"></i>Simpan Draft
                                    </button>
                                    <button type="button" class="btn btn-primary" data-submit-action="finalize">
                                        <i class="bi bi-check2-circle"></i>Finalisasi Presensi
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        @else
            <div class="card">
                <div class="card-body">
                    <div class="empty-state py-4">
                        <div class="icon"><i class="bi bi-calendar-check"></i></div>
                        <p class="mb-0">Pilih jadwal kegiatan terlebih dahulu untuk mulai mengelola presensi peserta.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const participantRows = Array.from(document.querySelectorAll('[data-attendance-row]'));
            if (participantRows.length === 0) {
                return;
            }

            const statusFilter = document.getElementById('attendanceStatusFilter');
            const classFilter = document.getElementById('attendanceClassFilter');
            const searchInput = document.getElementById('attendanceSearch');
            const onlyUnfilledInput = document.getElementById('attendanceOnlyUnfilled');
            const bulkStatus = document.getElementById('attendanceBulkStatus');
            const form = document.getElementById('coachAttendanceForm');
            const submitActionInput = document.getElementById('attendanceSubmitAction');
            const selectionSummary = document.getElementById('attendanceSelectionSummary');
            const selectVisibleButton = document.getElementById('attendanceSelectAllVisible');
            const clearSelectionButton = document.getElementById('attendanceClearSelection');

            const statusLabels = {
                present: 'Hadir',
                late: 'Terlambat',
                permission: 'Izin',
                sick: 'Sakit',
                absent: 'Tidak Hadir',
            };

            const fillLabels = {
                pending: 'Belum Diisi',
                draft: 'Draft',
                saved: 'Sudah Disimpan',
            };

            const summaryNodes = {
                total: document.querySelector('[data-summary-total]'),
                present: document.querySelector('[data-summary-present]'),
                late: document.querySelector('[data-summary-late]'),
                permission: document.querySelector('[data-summary-permission]'),
                sick: document.querySelector('[data-summary-sick]'),
                absent: document.querySelector('[data-summary-absent]'),
                pending: document.querySelector('[data-summary-pending]'),
            };

            const readStatus = (row) => row.querySelector('[data-status-input]').value;
            const readNotes = (row) => row.querySelector('[data-note-input]')?.value.trim() ?? '';

            const setFillState = (row) => {
                const status = readStatus(row);
                const notes = readNotes(row);
                const originalStatus = row.dataset.originalStatus ?? '';
                const originalNotes = row.dataset.originalNotes ?? '';
                const originalSaveState = row.dataset.originalSaveState ?? '';
                let fillState = 'pending';

                if (status !== '') {
                    const unchanged = status === originalStatus && notes === originalNotes;
                    fillState = unchanged && originalSaveState === 'finalized' ? 'saved' : 'draft';
                }

                const badge = row.querySelector('[data-fill-state-label]');
                badge.dataset.fillState = fillState;
                badge.textContent = fillLabels[fillState];
                row.dataset.currentStatus = status;
                row.dataset.fillState = fillState;
            };

            const setRowStatus = (row, status) => {
                row.querySelector('[data-status-input]').value = status;
                row.querySelectorAll('[data-status-option]').forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.statusOption === status);
                });

                const mobileSelect = row.querySelector('[data-mobile-status]');
                if (mobileSelect) {
                    mobileSelect.value = status;
                }

                setFillState(row);
                updateSummary();
                applyFilters();
            };

            const updateSummary = () => {
                const summary = {
                    total: participantRows.length,
                    present: 0,
                    late: 0,
                    permission: 0,
                    sick: 0,
                    absent: 0,
                    pending: 0,
                };

                participantRows.forEach((row) => {
                    const status = readStatus(row);
                    if (status === '') {
                        summary.pending += 1;
                        return;
                    }

                    if (summary[status] !== undefined) {
                        summary[status] += 1;
                    }
                });

                Object.entries(summaryNodes).forEach(([key, node]) => {
                    if (node) {
                        node.textContent = String(summary[key]);
                    }
                });
            };

            const applyFilters = () => {
                const search = (searchInput?.value ?? '').trim().toLowerCase();
                const classValue = (classFilter?.value ?? '').trim().toLowerCase();
                const statusValue = statusFilter?.value ?? '';
                const onlyUnfilled = Boolean(onlyUnfilledInput?.checked);

                participantRows.forEach((row) => {
                    const name = row.dataset.studentName ?? '';
                    const nis = row.dataset.studentNis ?? '';
                    const className = row.dataset.className ?? '';
                    const currentStatus = readStatus(row);
                    const matchesSearch = search === '' || name.includes(search) || nis.includes(search);
                    const matchesClass = classValue === '' || className === classValue;
                    const matchesStatus = statusValue === ''
                        || (statusValue === 'unfilled' ? currentStatus === '' : currentStatus === statusValue);
                    const matchesUnfilled = !onlyUnfilled || currentStatus === '';
                    const visible = matchesSearch && matchesClass && matchesStatus && matchesUnfilled;
                    row.hidden = !visible;
                });

                syncSelectionSummary();
            };

            const selectedRows = () => participantRows.filter((row) => row.querySelector('[data-selection-input]')?.checked);
            const visibleRows = () => participantRows.filter((row) => !row.hidden);

            const syncSelectionSummary = () => {
                const selectedCount = selectedRows().length;
                const visibleCount = visibleRows().length;

                participantRows.forEach((row) => {
                    const checkbox = row.querySelector('[data-selection-input]');
                    row.classList.toggle('is-selected', Boolean(checkbox?.checked));
                });

                if (selectionSummary) {
                    selectionSummary.textContent = `${selectedCount} peserta dipilih`;
                }

                if (selectVisibleButton) {
                    selectVisibleButton.disabled = visibleCount === 0;
                }

                if (clearSelectionButton) {
                    clearSelectionButton.disabled = selectedCount === 0;
                }
            };

            participantRows.forEach((row) => {
                row.querySelectorAll('[data-status-option]').forEach((button) => {
                    button.addEventListener('click', () => setRowStatus(row, button.dataset.statusOption));
                });

                const mobileSelect = row.querySelector('[data-mobile-status]');
                mobileSelect?.addEventListener('change', () => setRowStatus(row, mobileSelect.value));

                const noteToggle = row.querySelector('[data-note-toggle]');
                const noteWrap = row.querySelector('[data-note-wrap]');
                noteToggle?.addEventListener('click', () => {
                    const isHidden = noteWrap.hasAttribute('hidden');
                    if (isHidden) {
                        noteWrap.removeAttribute('hidden');
                        noteToggle.innerHTML = '<i class="bi bi-journal-text"></i>Sembunyikan Catatan';
                        noteWrap.querySelector('textarea')?.focus();
                    } else {
                        noteWrap.setAttribute('hidden', 'hidden');
                        noteToggle.innerHTML = '<i class="bi bi-journal-text"></i>' + (readNotes(row) !== '' ? 'Ubah Catatan' : 'Tambah Catatan');
                    }
                });

                row.querySelector('[data-note-input]')?.addEventListener('input', () => {
                    setFillState(row);
                });

                row.querySelector('[data-selection-input]')?.addEventListener('change', syncSelectionSummary);

                setFillState(row);
            });

            searchInput?.addEventListener('input', applyFilters);
            classFilter?.addEventListener('change', applyFilters);
            statusFilter?.addEventListener('change', applyFilters);
            onlyUnfilledInput?.addEventListener('change', applyFilters);

            selectVisibleButton?.addEventListener('click', () => {
                visibleRows().forEach((row) => {
                    const checkbox = row.querySelector('[data-selection-input]');
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                syncSelectionSummary();
            });

            clearSelectionButton?.addEventListener('click', () => {
                participantRows.forEach((row) => {
                    const checkbox = row.querySelector('[data-selection-input]');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                });

                syncSelectionSummary();
            });

            document.getElementById('attendanceMarkAllPresent')?.addEventListener('click', () => {
                if (!window.confirm('Tandai semua peserta sebagai hadir?')) {
                    return;
                }

                participantRows.forEach((row) => setRowStatus(row, 'present'));
            });

            document.getElementById('attendanceClearAll')?.addEventListener('click', () => {
                if (!window.confirm('Kosongkan seluruh status presensi yang sedang diisi?')) {
                    return;
                }

                participantRows.forEach((row) => setRowStatus(row, ''));
            });

            document.getElementById('attendanceApplySelected')?.addEventListener('click', () => {
                const rows = selectedRows();
                if (rows.length === 0) {
                    window.alert('Pilih minimal satu peserta terlebih dahulu.');
                    return;
                }

                if (!bulkStatus?.value) {
                    window.alert('Pilih status massal terlebih dahulu.');
                    return;
                }

                if (!window.confirm(`Terapkan status ${statusLabels[bulkStatus.value]} ke ${rows.length} peserta terpilih?`)) {
                    return;
                }

                rows.forEach((row) => setRowStatus(row, bulkStatus.value));
            });

            form?.querySelectorAll('[data-submit-action]').forEach((button) => {
                button.addEventListener('click', () => {
                    const action = button.dataset.submitAction;
                    const pendingCount = participantRows.filter((row) => readStatus(row) === '').length;

                    if (action === 'finalize') {
                        const warning = pendingCount > 0
                            ? `Masih ada ${pendingCount} peserta belum diisi. Finalisasi tetap dilanjutkan?`
                            : 'Finalisasi presensi sekarang? Setelah disimpan, data tetap masih bisa diperbarui bila diperlukan.';

                        if (!window.confirm(warning)) {
                            return;
                        }
                    } else if (!window.confirm('Simpan perubahan sebagai draft presensi?')) {
                        return;
                    }

                    submitActionInput.value = action;
                    form.querySelectorAll('button, input, select, textarea').forEach((element) => {
                        if (element === submitActionInput) {
                            return;
                        }

                        if (element instanceof HTMLButtonElement) {
                            element.disabled = true;
                        }
                    });

                    button.innerHTML = action === 'finalize'
                        ? '<i class="bi bi-hourglass-split"></i>Memfinalisasi...'
                        : '<i class="bi bi-hourglass-split"></i>Menyimpan Draft...';
                    form.submit();
                });
            });

            updateSummary();
            applyFilters();
            syncSelectionSummary();
        });
    </script>
@endpush
