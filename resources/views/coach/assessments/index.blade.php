@extends('layouts.app')

@section('page_title', 'Kelola Prestasi dan Penilaian')
@section('page_subtitle', 'Atur prestasi ekstrakurikuler, penilaian siswa, dan riwayat data dengan alur yang lebih ringkas.')

@php
    $titleOptions = ['Kedisiplinan', 'Kehadiran', 'Kerja sama', 'Keaktifan', 'Kemampuan teknis', 'Perkembangan', 'Sikap', 'Penilaian lain'];
    $activeMode = old('entry_mode', 'mass');
    $selectedMassExtracurricularId = (string) old('extracurricular_id', '');
    $selectedTitleOption = old('title_option', 'Kedisiplinan');
    $selectedIndividualExtracurricularId = (string) old('extracurricular_id', '');
    $selectedIndividualStudentId = (string) old('student_id', '');
    $studentDirectory = $approvedRegistrations
        ->map(function ($registration) {
            $student = $registration->student;
            $user = $student?->user;

            return [
                'registration_id' => $registration->id,
                'extracurricular_id' => $registration->extracurricular_id,
                'student_id' => $student?->id,
                'name' => $user?->name ?? 'Siswa',
                'nis' => $student?->nis ?? '-',
                'class_name' => $student?->class_name ?? '-',
                'initial' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user?->name ?? 'S', 0, 1)),
            ];
        })
        ->filter(fn ($row) => $row['student_id'])
        ->values();
    $massState = [
        'oldRows' => old('rows', []),
        'lookup' => $assessmentLookup,
        'students' => $studentDirectory,
    ];
    $historyHasFilters = filled($historyExtracurricularId) || filled($historyType) || filled($historyStatus) || filled($historyMonth) || filled($historyPeriod) || request()->has('page');
@endphp

@push('styles')
    <style>
        .coach-assessment-page {
            display: grid;
            gap: 1rem;
        }

        .coach-assessment-panel,
        .coach-assessment-history,
        .coach-assessment-sticky,
        .coach-assessment-empty {
            border: 1px solid #dbe5f0;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.05);
        }

        .coach-assessment-panel {
            padding: 1rem 1.15rem;
        }

        .coach-assessment-panel + .coach-assessment-panel {
            margin-top: 1rem;
        }

        .coach-assessment-panel--subtle {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .coach-assessment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.9rem;
            margin-bottom: 0.9rem;
        }

        .coach-assessment-header h2,
        .coach-assessment-header h3 {
            margin: 0 0 0.25rem;
            font-size: 1rem;
            font-weight: 800;
        }

        .coach-assessment-header p {
            margin: 0;
            color: #667b93;
            font-size: 0.82rem;
        }

        .coach-assessment-steps {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-bottom: 0.9rem;
        }

        .coach-assessment-step {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 0.75rem;
            border-radius: 999px;
            border: 1px solid #dbe5f0;
            background: #fbfdff;
            color: #5c728d;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .coach-assessment-step span {
            width: 1.45rem;
            height: 1.45rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #edf4ff;
            color: #1f5eff;
        }

        .coach-assessment-recent-list {
            display: grid;
            gap: 0.75rem;
        }

        .coach-assessment-recent-item {
            padding: 0.9rem 1rem;
            border-radius: 18px;
            border: 1px solid #e1eaf4;
            background: #fbfdff;
        }

        .coach-assessment-recent-item strong,
        .coach-assessment-recent-item small {
            display: block;
        }

        .coach-assessment-recent-item small {
            color: #667b93;
        }

        .coach-assessment-mass-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .coach-assessment-member-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .coach-assessment-member-card {
            padding: 0.9rem;
            border: 1px solid #e1eaf4;
            border-radius: 18px;
            background: #fbfdff;
        }

        .coach-assessment-member-card + .coach-assessment-member-card {
            margin-top: 0.75rem;
        }

        .coach-assessment-member-card .mobile-data-item-label {
            margin-bottom: 0.2rem;
        }

        .coach-assessment-sticky {
            position: sticky;
            bottom: 1rem;
            z-index: 5;
            padding: 0.9rem 1rem;
            margin-top: 1rem;
        }

        .coach-assessment-sticky__inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .coach-assessment-sticky__stats {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            color: #5f738c;
            font-size: 0.82rem;
        }

        .coach-assessment-sticky__stats strong {
            color: #18334f;
        }

        .coach-assessment-sticky__actions {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .coach-assessment-history {
            overflow: hidden;
        }

        .coach-assessment-history__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1rem 1.15rem 0.9rem;
            border-bottom: 1px solid #e8eef5;
        }

        .coach-assessment-history__header h2 {
            margin: 0 0 0.25rem;
            font-size: 1rem;
            font-weight: 800;
        }

        .coach-assessment-history__header p {
            margin: 0;
            color: #667b93;
            font-size: 0.82rem;
        }

        .coach-assessment-history__body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .coach-assessment-status {
            display: inline-flex;
            align-items: center;
            padding: 0.42rem 0.7rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .coach-assessment-status[data-status="draft"] {
            color: #936000;
            background: #fff3d6;
            border: 1px solid #f0d18f;
        }

        .coach-assessment-status[data-status="published"] {
            color: #177245;
            background: #e9f8ef;
            border: 1px solid #bbe2cb;
        }

        .coach-assessment-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.42rem 0.72rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .coach-assessment-type-badge[data-type="achievement"] {
            color: #177245;
            background: #eaf8f0;
            border: 1px solid #bde4cf;
        }

        .coach-assessment-type-badge[data-type="assessment"] {
            color: #8d6200;
            background: #fff5d8;
            border: 1px solid #efd08d;
        }

        .coach-assessment-empty {
            padding: 1.1rem;
            text-align: center;
            color: #667b93;
        }

        @media (max-width: 991.98px) {
            .coach-assessment-header,
            .coach-assessment-history__header {
                flex-direction: column;
            }
        }

        @media (max-width: 767.98px) {
            .coach-assessment-panel,
            .coach-assessment-history__body,
            .coach-assessment-history__header {
                padding-inline: 0.95rem;
            }

            .coach-assessment-sticky {
                bottom: 0.75rem;
                padding: 0.85rem 0.9rem;
            }

            .coach-assessment-sticky__inner,
            .coach-assessment-sticky__actions {
                flex-direction: column;
                align-items: stretch;
            }

            .coach-assessment-sticky__actions .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="coach-assessment-page">
        <div class="card">
            <div class="card-body toolbar-card">
                <div class="section-header-inline mb-3">
                    <div>
                        <h2>Kelola Data</h2>
                        <p>Pilih area kerja yang ingin dibuka agar halaman tidak terlalu panjang.</p>
                    </div>
                </div>

                <div class="tab-scroll-nav" role="tablist" aria-label="Kelola prestasi dan penilaian">
                    <button class="tab-scroll-nav__item @if($activeTab === 'achievement') is-active @endif" data-bs-toggle="tab" data-bs-target="#coach-achievement-tab" type="button" role="tab" aria-selected="{{ $activeTab === 'achievement' ? 'true' : 'false' }}">Prestasi</button>
                    <button class="tab-scroll-nav__item @if($activeTab === 'assessment') is-active @endif" data-bs-toggle="tab" data-bs-target="#coach-assessment-tab" type="button" role="tab" aria-selected="{{ $activeTab === 'assessment' ? 'true' : 'false' }}">Penilaian</button>
                    <button class="tab-scroll-nav__item @if($activeTab === 'history') is-active @endif" data-bs-toggle="tab" data-bs-target="#coach-history-tab" type="button" role="tab" aria-selected="{{ $activeTab === 'history' ? 'true' : 'false' }}">Riwayat</button>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade @if($activeTab === 'achievement') show active @endif" id="coach-achievement-tab" role="tabpanel" tabindex="0">
                <div class="coach-assessment-panel coach-assessment-panel--subtle">
                    <div class="coach-assessment-header">
                        <div>
                            <h2>Prestasi Ekstrakurikuler</h2>
                            <p>Catat prestasi kegiatan saja. Form disembunyikan agar halaman tetap ringkas saat hanya ingin meninjau data terbaru.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#coachAchievementFormPanel" aria-expanded="{{ old('active_tab', $activeTab) === 'achievement' ? 'true' : 'false' }}">
                            <i class="bi bi-plus-circle"></i>Tambah Prestasi
                        </button>
                    </div>

                    <div class="collapse @if(old('active_tab', $activeTab) === 'achievement') show @endif" id="coachAchievementFormPanel">
                        <form method="post" action="{{ route('coach.assessments.store') }}" class="row g-3">
                            @csrf
                            <input type="hidden" name="assessment_type" value="achievement">
                            <input type="hidden" name="active_tab" value="achievement">
                            <div class="col-md-4">
                                <label class="form-label" for="coach_achievement_extracurricular_id">Ekstrakurikuler</label>
                                <select id="coach_achievement_extracurricular_id" name="extracurricular_id" class="form-select" required>
                                    <option value="">Pilih Ekstrakurikuler</option>
                                    @foreach($extracurriculars as $item)
                                        <option value="{{ $item->id }}" @selected(old('active_tab') === 'achievement' && old('extracurricular_id') == $item->id)>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="coach_achievement_title">Judul prestasi</label>
                                <input id="coach_achievement_title" type="text" name="title" value="{{ old('active_tab') === 'achievement' ? old('title') : '' }}" class="form-control" placeholder="Contoh: Juara Umum Kejurda" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="coach_achievement_date">Tanggal</label>
                                <input id="coach_achievement_date" type="date" name="assessment_date" value="{{ old('active_tab') === 'achievement' ? old('assessment_date') : '' }}" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="coach_achievement_description">Deskripsi</label>
                                <textarea id="coach_achievement_description" name="description" class="form-control" rows="3" placeholder="Jelaskan tingkat prestasi atau catatan pendukung">{{ old('active_tab') === 'achievement' ? old('description') : '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-actions justify-content-end">
                                    <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan prestasi..."><i class="bi bi-save"></i>Simpan Prestasi</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="coach-assessment-panel">
                    <div class="coach-assessment-header">
                        <div>
                            <h3>Prestasi Terbaru</h3>
                            <p>Ringkasan cepat prestasi terakhir yang sudah dicatat pembina.</p>
                        </div>
                    </div>
                    @if($recentAchievements->isEmpty())
                        <div class="coach-assessment-empty">Belum ada prestasi ekstrakurikuler yang dicatat.</div>
                    @else
                        <div class="coach-assessment-recent-list">
                            @foreach($recentAchievements as $item)
                                <div class="coach-assessment-recent-item">
                                    <strong>{{ $item->title }}</strong>
                                    <small>{{ $item->extracurricular->name ?? '-' }} • {{ optional($item->assessment_date)->translatedFormat('d F Y') ?: '-' }}</small>
                                    <div class="small text-muted mt-2">{{ $item->description ?: 'Belum ada deskripsi tambahan.' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="tab-pane fade @if($activeTab === 'assessment') show active @endif" id="coach-assessment-tab" role="tabpanel" tabindex="0">
                <div class="coach-assessment-panel">
                    <div class="coach-assessment-header">
                        <div>
                            <h2>Penilaian Siswa</h2>
                            <p>Gunakan penilaian massal sebagai alur utama. Penilaian individual tetap tersedia untuk evaluasi khusus.</p>
                        </div>
                    </div>

                    <div class="tab-scroll-nav mb-3" role="tablist" aria-label="Mode penilaian siswa">
                        <button class="tab-scroll-nav__item @if($activeMode === 'mass') is-active @endif" data-bs-toggle="tab" data-bs-target="#coach-mass-assessment-mode" type="button" role="tab" aria-selected="{{ $activeMode === 'mass' ? 'true' : 'false' }}">Penilaian Massal</button>
                        <button class="tab-scroll-nav__item @if($activeMode === 'individual') is-active @endif" data-bs-toggle="tab" data-bs-target="#coach-individual-assessment-mode" type="button" role="tab" aria-selected="{{ $activeMode === 'individual' ? 'true' : 'false' }}">Penilaian Individual</button>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade @if($activeMode === 'mass') show active @endif" id="coach-mass-assessment-mode" role="tabpanel" tabindex="0">
                            <form method="post" action="{{ route('coach.assessments.store') }}" data-mass-assessment-root data-mass-assessment='@json($massState)'>
                                @csrf
                                <input type="hidden" name="entry_mode" value="mass">
                                <input type="hidden" name="assessment_type" value="assessment">
                                <input type="hidden" name="active_tab" value="assessment">

                                <div class="coach-assessment-steps">
                                    <div class="coach-assessment-step"><span>1</span>Atur Penilaian</div>
                                    <div class="coach-assessment-step"><span>2</span>Isi Nilai Siswa</div>
                                    <div class="coach-assessment-step"><span>3</span>Simpan</div>
                                </div>

                                <div class="coach-assessment-panel coach-assessment-panel--subtle">
                                    <div class="coach-assessment-header">
                                        <div>
                                            <h3>Atur Penilaian</h3>
                                            <p>Pilih ekstrakurikuler, jenis penilaian, dan tanggal. Pembina terisi otomatis.</p>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label" for="mass_extracurricular_id">Ekstrakurikuler</label>
                                            <select id="mass_extracurricular_id" name="extracurricular_id" class="form-select" required data-mass-extracurricular-select>
                                                <option value="">Pilih Ekstrakurikuler</option>
                                                @foreach($extracurriculars as $item)
                                                    <option value="{{ $item->id }}" @selected($selectedMassExtracurricularId === (string) $item->id)>{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="mass_title_option">Jenis penilaian</label>
                                            <select id="mass_title_option" name="title_option" class="form-select" required data-assessment-title-option>
                                                @foreach($titleOptions as $option)
                                                    <option value="{{ $option }}" @selected($selectedTitleOption === $option)>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 @if($selectedTitleOption !== 'Penilaian lain') d-none @endif" data-custom-title-wrapper>
                                            <label class="form-label" for="mass_custom_title">Penilaian lain</label>
                                            <input id="mass_custom_title" type="text" name="custom_title" value="{{ old('custom_title') }}" class="form-control" placeholder="Contoh: Kepemimpinan lapangan">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="mass_assessment_date">Tanggal</label>
                                            <input id="mass_assessment_date" type="date" name="assessment_date" value="{{ old('assessment_date', now()->toDateString()) }}" class="form-control" required data-mass-assessment-date>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Pembina</label>
                                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="coach-assessment-panel">
                                    <div class="coach-assessment-header">
                                        <div>
                                            <h3>Isi Nilai Siswa</h3>
                                            <p>Gunakan pencarian, filter kelas, dan aksi massal tanpa menampilkan tabel kosong besar.</p>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-lightning-charge"></i>Aksi Massal
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                                <li><button type="button" class="dropdown-item" data-bulk-score>Isi nilai yang sama</button></li>
                                                <li><button type="button" class="dropdown-item" data-bulk-note>Gunakan catatan yang sama</button></li>
                                                <li><button type="button" class="dropdown-item text-danger" data-bulk-clear>Kosongkan semua nilai</button></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-8">
                                            <label class="form-label" for="mass_student_search">Cari siswa</label>
                                            <input id="mass_student_search" type="text" class="form-control" placeholder="Cari nama, NIS, atau kelas" data-mass-student-search>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="mass_class_filter">Filter kelas</label>
                                            <select id="mass_class_filter" class="form-select" data-mass-class-filter>
                                                <option value="">Semua kelas</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="desktop-table table-responsive assessment-mass-table coach-assessment-member-table">
                                        <table class="table table-striped table-compact mb-0">
                                            <thead>
                                            <tr>
                                                <th>Siswa</th>
                                                <th>Kelas</th>
                                                <th>Nilai</th>
                                                <th>Catatan</th>
                                                <th>Status</th>
                                            </tr>
                                            </thead>
                                            <tbody data-mass-table-body>
                                            <tr>
                                                <td colspan="5">
                                                    <div class="coach-assessment-empty">Pilih ekstrakurikuler terlebih dahulu.</div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mobile-stack-table d-md-none mt-3" data-mass-card-body>
                                        <div class="coach-assessment-empty">Pilih ekstrakurikuler terlebih dahulu.</div>
                                    </div>

                                    <div class="coach-assessment-sticky">
                                        <div class="coach-assessment-sticky__inner">
                                            <div class="coach-assessment-sticky__stats">
                                                <span>Terisi: <strong data-mass-filled-count>0 siswa</strong></span>
                                                <span>Belum diisi: <strong data-mass-empty-count>0 siswa</strong></span>
                                            </div>
                                            <div class="coach-assessment-sticky__actions">
                                                <button class="btn btn-outline-primary" type="submit" name="submit_action" value="draft" data-loading-text="Menyimpan draft..."><i class="bi bi-save"></i>Simpan Draft</button>
                                                <button class="btn btn-primary" type="submit" name="submit_action" value="publish" data-loading-text="Menyimpan semua penilaian..."><i class="bi bi-check2-circle"></i>Simpan Semua Penilaian</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade @if($activeMode === 'individual') show active @endif" id="coach-individual-assessment-mode" role="tabpanel" tabindex="0">
                            <form method="post" action="{{ route('coach.assessments.store') }}" class="row g-3">
                                @csrf
                                <input type="hidden" name="entry_mode" value="individual">
                                <input type="hidden" name="assessment_type" value="assessment">
                                <input type="hidden" name="active_tab" value="assessment">
                                <div class="col-md-3">
                                    <label class="form-label" for="individual_extracurricular_id">Ekstrakurikuler</label>
                                    <select id="individual_extracurricular_id" name="extracurricular_id" class="form-select" required data-individual-extracurricular-select>
                                        <option value="">Pilih Ekstrakurikuler</option>
                                        @foreach($extracurriculars as $item)
                                            <option value="{{ $item->id }}" @selected($selectedIndividualExtracurricularId === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="individual_student_id">Siswa</label>
                                    <select id="individual_student_id" name="student_id" class="form-select" required data-individual-student-select>
                                        <option value="">Pilih Siswa</option>
                                        @foreach($approvedRegistrations as $registration)
                                            <option value="{{ $registration->student_id }}" data-extracurricular-id="{{ $registration->extracurricular_id }}" data-name="{{ $registration->student->user->name ?? 'Siswa' }}" data-meta="NIS: {{ $registration->student->nis ?? '-' }} • {{ $registration->student->class_name ?? '-' }}" @selected($selectedIndividualStudentId === (string) $registration->student_id)>{{ $registration->student->user->name ?? 'Siswa' }} ({{ $registration->student->nis ?? '-' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="individual_title_option">Jenis penilaian</label>
                                    <select id="individual_title_option" name="title_option" class="form-select" required data-assessment-title-option>
                                        @foreach($titleOptions as $option)
                                            <option value="{{ $option }}" @selected($selectedTitleOption === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 @if($selectedTitleOption !== 'Penilaian lain') d-none @endif" data-custom-title-wrapper>
                                    <label class="form-label" for="individual_custom_title">Penilaian lain</label>
                                    <input id="individual_custom_title" type="text" name="custom_title" value="{{ old('custom_title') }}" class="form-control" placeholder="Contoh: Kepemimpinan lapangan">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="individual_assessment_date">Tanggal</label>
                                    <input id="individual_assessment_date" type="date" name="assessment_date" value="{{ old('assessment_date', now()->toDateString()) }}" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="individual_score">Nilai</label>
                                    <input id="individual_score" type="number" step="0.01" min="0" max="100" name="score" value="{{ old('score') }}" class="form-control" placeholder="0 - 100" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pembina</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                </div>
                                <div class="col-12">
                                    <div class="data-point">
                                        <div class="data-point-label">Siswa Terpilih</div>
                                        <p class="data-point-value mb-0" data-individual-student-name>Pilih siswa untuk melihat ringkasan singkat.</p>
                                        <div class="helper-text mb-0" data-individual-student-meta>NIS dan kelas akan muncul di sini.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="individual_description">Catatan</label>
                                    <textarea id="individual_description" name="description" class="form-control" rows="4" placeholder="Catatan evaluasi khusus atau tindak lanjut">{{ old('description') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-actions justify-content-end">
                                        <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan penilaian..."><i class="bi bi-save"></i>Simpan Penilaian Individual</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade @if($activeTab === 'history') show active @endif" id="coach-history-tab" role="tabpanel" tabindex="0">
                <div class="coach-assessment-history">
                    <div class="coach-assessment-history__header">
                        <div>
                            <h2>Riwayat Data</h2>
                            <p>Filter, ekspor, dan kelola riwayat prestasi maupun penilaian tanpa membuka form input.</p>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-download"></i>Ekspor
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                <li><a class="dropdown-item" href="{{ route('coach.assessments.export', array_merge(request()->query(), ['format' => 'xls'])) }}"><i class="bi bi-file-earmark-excel me-2"></i>Unduh Excel</a></li>
                                <li><a class="dropdown-item" href="{{ route('coach.assessments.export', array_merge(request()->query(), ['format' => 'csv'])) }}"><i class="bi bi-download me-2"></i>Unduh CSV</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="coach-assessment-history__body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                            <div class="tab-scroll-nav">
                                <a href="{{ route('coach.assessments.index', array_merge(request()->except(['history_type', 'page']), ['tab' => 'history'])) }}" class="tab-scroll-nav__item @if($historyType === '') is-active @endif">Semua</a>
                                <a href="{{ route('coach.assessments.index', array_merge(request()->except('page'), ['tab' => 'history', 'history_type' => 'achievement'])) }}" class="tab-scroll-nav__item @if($historyType === 'achievement') is-active @endif">Prestasi</a>
                                <a href="{{ route('coach.assessments.index', array_merge(request()->except('page'), ['tab' => 'history', 'history_type' => 'assessment'])) }}" class="tab-scroll-nav__item @if($historyType === 'assessment') is-active @endif">Penilaian</a>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#coachAssessmentHistoryFilter" aria-expanded="{{ $historyHasFilters ? 'true' : 'false' }}">
                                <i class="bi bi-funnel"></i>Filter Riwayat
                            </button>
                        </div>

                        <div class="collapse @if($historyHasFilters) show @endif" id="coachAssessmentHistoryFilter">
                            <form class="toolbar-grid mb-3" method="get" action="{{ route('coach.assessments.index') }}">
                                <input type="hidden" name="tab" value="history">
                                <div class="toolbar-col-3">
                                    <label class="form-label" for="history_extracurricular_id">Ekstrakurikuler</label>
                                    <select id="history_extracurricular_id" name="history_extracurricular_id" class="form-select">
                                        <option value="">Semua ekstrakurikuler</option>
                                        @foreach($extracurriculars as $item)
                                            <option value="{{ $item->id }}" @selected((string) $historyExtracurricularId === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="toolbar-col-3">
                                    <label class="form-label" for="history_type">Jenis data</label>
                                    <select id="history_type" name="history_type" class="form-select">
                                        <option value="">Semua jenis</option>
                                        <option value="achievement" @selected($historyType === 'achievement')>Prestasi</option>
                                        <option value="assessment" @selected($historyType === 'assessment')>Penilaian</option>
                                    </select>
                                </div>
                                <div class="toolbar-col-2">
                                    <label class="form-label" for="history_month">Bulan</label>
                                    <select id="history_month" name="history_month" class="form-select">
                                        <option value="">Semua bulan</option>
                                        @foreach($historyMonthOptions as $monthOption)
                                            <option value="{{ $monthOption }}" @selected($historyMonth === $monthOption)>{{ \Carbon\Carbon::create()->month((int) $monthOption)->translatedFormat('F') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="toolbar-col-2">
                                    <label class="form-label" for="history_period">Periode</label>
                                    <select id="history_period" name="history_period" class="form-select">
                                        <option value="">Semua periode</option>
                                        <option value="recent" @selected($historyPeriod === 'recent')>30 hari terakhir</option>
                                        <option value="semester" @selected($historyPeriod === 'semester')>Semester ini</option>
                                    </select>
                                </div>
                                <div class="toolbar-col-2">
                                    <label class="form-label" for="history_status">Status</label>
                                    <select id="history_status" name="history_status" class="form-select">
                                        <option value="">Semua status</option>
                                        <option value="draft" @selected($historyStatus === 'draft')>Draft</option>
                                        <option value="published" @selected($historyStatus === 'published')>Dipublikasikan</option>
                                    </select>
                                </div>
                                <div class="toolbar-col-2">
                                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i>Terapkan</button>
                                </div>
                                <div class="toolbar-col-2">
                                    <a href="{{ route('coach.assessments.index', ['tab' => 'history']) }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
                                </div>
                            </form>
                        </div>

                        <div class="desktop-table table-responsive">
                            <table class="table table-striped table-compact mb-0">
                                <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Judul</th>
                                    <th>Subjek</th>
                                    <th>Ekstrakurikuler</th>
                                    <th>Hasil</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($assessments as $row)
                                    @php
                                        $isAchievement = $row->assessment_type === 'achievement';
                                        $typeLabel = $isAchievement ? 'Prestasi' : 'Penilaian';
                                        $subjectLabel = $isAchievement ? ($row->extracurricular->name ?? 'Tim Ekstrakurikuler') : ($row->student->user->name ?? '-');
                                        $resultLabel = $isAchievement ? 'Prestasi tercatat' : ($row->score !== null ? rtrim(rtrim(number_format((float) $row->score, 2, ',', '.'), '0'), ',') : 'Belum dinilai');
                                    @endphp
                                    <tr>
                                        <td><span class="coach-assessment-type-badge" data-type="{{ $row->assessment_type }}">{{ $typeLabel }}</span></td>
                                        <td>{{ $row->title }}</td>
                                        <td>{{ $subjectLabel }}</td>
                                        <td>{{ $row->extracurricular->name ?? '-' }}</td>
                                        <td>{{ $resultLabel }}</td>
                                        <td><span class="coach-assessment-status" data-status="{{ $row->status }}">{{ $row->status === 'draft' ? 'Draft' : 'Dipublikasikan' }}</span></td>
                                        <td>{{ optional($row->assessment_date)->format('d-m-Y') }}</td>
                                        <td class="text-end table-action-col">
                                            <div class="table-inline-actions table-inline-actions--compact justify-content-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary action-button-compact coach-assessment-detail-trigger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#coachAssessmentDetailModal"
                                                    data-type="{{ $typeLabel }}"
                                                    data-title="{{ $row->title }}"
                                                    data-subject="{{ $subjectLabel }}"
                                                    data-extracurricular="{{ $row->extracurricular->name ?? '-' }}"
                                                    data-result="{{ $resultLabel }}"
                                                    data-status="{{ $row->status === 'draft' ? 'Draft' : 'Dipublikasikan' }}"
                                                    data-date="{{ optional($row->assessment_date)->translatedFormat('d F Y') ?: '-' }}"
                                                    data-description="{{ $row->description ?: 'Belum ada catatan tambahan.' }}"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                    <span class="d-none d-md-inline">Detail</span>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                                        <li><a href="{{ route('coach.assessments.edit', $row) }}" class="dropdown-item"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                                        <li>
                                                            <form method="post" action="{{ route('coach.assessments.destroy', $row) }}" onsubmit="return confirm('Hapus data ini?')">
                                                                @csrf
                                                                @method('delete')
                                                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Hapus</button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <div class="icon"><i class="bi bi-award"></i></div>
                                                <p class="mb-0">Belum ada data prestasi atau penilaian pada filter ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body px-0 pt-3 pb-0">{{ $assessments->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="coachAssessmentDetailModal" tabindex="-1" aria-labelledby="coachAssessmentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content verification-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="h5 mb-1" id="coachAssessmentDetailModalLabel">Detail Data</h2>
                        <p class="text-muted mb-0" id="coachAssessmentDetailMeta">Ringkasan prestasi atau penilaian</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="verification-modal__summary mb-3">
                        <div class="data-point"><div class="data-point-label">Jenis</div><p class="data-point-value mb-0" id="coachAssessmentDetailType">-</p></div>
                        <div class="data-point"><div class="data-point-label">Status</div><p class="data-point-value mb-0" id="coachAssessmentDetailStatus">-</p></div>
                        <div class="data-point"><div class="data-point-label">Subjek</div><p class="data-point-value mb-0" id="coachAssessmentDetailSubject">-</p></div>
                        <div class="data-point"><div class="data-point-label">Ekstrakurikuler</div><p class="data-point-value mb-0" id="coachAssessmentDetailExtracurricular">-</p></div>
                        <div class="data-point"><div class="data-point-label">Hasil</div><p class="data-point-value mb-0" id="coachAssessmentDetailResult">-</p></div>
                        <div class="data-point"><div class="data-point-label">Tanggal</div><p class="data-point-value mb-0" id="coachAssessmentDetailDate">-</p></div>
                    </div>
                    <div class="info-item">
                        <div class="title mb-2" id="coachAssessmentDetailTitle">-</div>
                        <p class="mb-0" id="coachAssessmentDetailDescription">Belum ada catatan tambahan.</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-assessment-title-option]').forEach((select) => {
                const wrapper = select.closest('form').querySelector('[data-custom-title-wrapper]');
                const sync = () => {
                    if (!wrapper) return;
                    wrapper.classList.toggle('d-none', select.value !== 'Penilaian lain');
                };
                select.addEventListener('change', sync);
                sync();
            });

            const detailModal = document.getElementById('coachAssessmentDetailModal');
            if (detailModal) {
                detailModal.addEventListener('show.bs.modal', (event) => {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const map = {
                        title: 'coachAssessmentDetailTitle',
                        type: 'coachAssessmentDetailType',
                        subject: 'coachAssessmentDetailSubject',
                        extracurricular: 'coachAssessmentDetailExtracurricular',
                        result: 'coachAssessmentDetailResult',
                        status: 'coachAssessmentDetailStatus',
                        date: 'coachAssessmentDetailDate',
                        description: 'coachAssessmentDetailDescription',
                    };

                    Object.entries(map).forEach(([key, id]) => {
                        const node = document.getElementById(id);
                        if (node) node.textContent = trigger.dataset[key] || '-';
                    });
                });
            }

            const individualExtracurricular = document.querySelector('[data-individual-extracurricular-select]');
            const individualStudent = document.querySelector('[data-individual-student-select]');
            const individualName = document.querySelector('[data-individual-student-name]');
            const individualMeta = document.querySelector('[data-individual-student-meta]');
            const syncIndividualStudents = () => {
                if (!individualExtracurricular || !individualStudent) return;
                const selectedExtracurricular = individualExtracurricular.value;
                Array.from(individualStudent.options).forEach((option) => {
                    if (!option.value) return;
                    option.hidden = selectedExtracurricular !== '' && option.dataset.extracurricularId !== selectedExtracurricular;
                });

                if (individualStudent.selectedOptions[0]?.hidden) {
                    individualStudent.value = '';
                }

                const selected = individualStudent.selectedOptions[0];
                individualName.textContent = selected && selected.value ? (selected.dataset.name || selected.textContent) : 'Pilih siswa untuk melihat ringkasan singkat.';
                individualMeta.textContent = selected && selected.value ? (selected.dataset.meta || '-') : 'NIS dan kelas akan muncul di sini.';
            };
            individualExtracurricular?.addEventListener('change', syncIndividualStudents);
            individualStudent?.addEventListener('change', syncIndividualStudents);
            syncIndividualStudents();

            const massRoot = document.querySelector('[data-mass-assessment-root]');
            if (!massRoot) return;

            const state = JSON.parse(massRoot.dataset.massAssessment || '{}');
            const oldRows = state.oldRows || [];
            const lookup = state.lookup || {};
            const students = state.students || [];

            const extracurricularSelect = massRoot.querySelector('[data-mass-extracurricular-select]');
            const searchInput = massRoot.querySelector('[data-mass-student-search]');
            const classFilter = massRoot.querySelector('[data-mass-class-filter]');
            const tableBody = massRoot.querySelector('[data-mass-table-body]');
            const cardBody = massRoot.querySelector('[data-mass-card-body]');
            const filledCountNode = massRoot.querySelector('[data-mass-filled-count]');
            const emptyCountNode = massRoot.querySelector('[data-mass-empty-count]');

            const getSelectedStudents = () => students.filter((student) => String(student.extracurricular_id) === extracurricularSelect.value);

            const getOldRow = (studentId) => oldRows.find((row) => String(row.student_id) === String(studentId)) || null;

            const renderEmpty = (message) => {
                tableBody.innerHTML = `<tr><td colspan="5"><div class="coach-assessment-empty">${message}</div></td></tr>`;
                cardBody.innerHTML = `<div class="coach-assessment-empty">${message}</div>`;
                filledCountNode.textContent = '0 siswa';
                emptyCountNode.textContent = '0 siswa';
            };

            const buildStatusLabel = (student, score) => {
                if (score !== '') {
                    return 'Siap disimpan';
                }

                const key = `${student.extracurricular_id}|${student.student_id}|${massRoot.querySelector('[name="title_option"]').value === 'Penilaian lain' ? (massRoot.querySelector('[name="custom_title"]').value || 'Penilaian lain') : massRoot.querySelector('[name="title_option"]').value}|${massRoot.querySelector('[name="assessment_date"]').value}`;
                return lookup[key] === 'draft' ? 'Draft tersimpan' : 'Belum diisi';
            };

            const syncCounters = () => {
                const scoreInputs = massRoot.querySelectorAll('[data-mass-score-input]');
                let filled = 0;
                scoreInputs.forEach((input) => {
                    if (input.value !== '') filled += 1;
                });
                filledCountNode.textContent = `${filled} siswa`;
                emptyCountNode.textContent = `${scoreInputs.length - filled} siswa`;
            };

            const bindRenderedRows = () => {
                massRoot.querySelectorAll('[data-mass-score-input]').forEach((input) => {
                    input.addEventListener('input', () => {
                        const label = input.closest('[data-mass-row]')?.querySelector('[data-mass-row-status]');
                        if (label) {
                            label.textContent = input.value !== '' ? 'Siap disimpan' : 'Belum diisi';
                        }
                        syncCounters();
                    });
                });
            };

            const renderMassMembers = () => {
                if (!extracurricularSelect.value) {
                    renderEmpty('Pilih ekstrakurikuler terlebih dahulu.');
                    return;
                }

                const selectedStudents = getSelectedStudents();
                if (selectedStudents.length === 0) {
                    renderEmpty('Belum ada anggota aktif pada ekstrakurikuler ini.');
                    return;
                }

                const search = (searchInput.value || '').trim().toLowerCase();
                const classValue = classFilter.value;
                const filtered = selectedStudents.filter((student) => {
                    const matchSearch = search === '' || student.name.toLowerCase().includes(search) || String(student.nis).toLowerCase().includes(search) || String(student.class_name).toLowerCase().includes(search);
                    const matchClass = classValue === '' || student.class_name === classValue;
                    return matchSearch && matchClass;
                });

                if (filtered.length === 0) {
                    renderEmpty('Tidak ada siswa yang cocok dengan pencarian atau filter kelas.');
                    return;
                }

                tableBody.innerHTML = filtered.map((student, index) => {
                    const oldRow = getOldRow(student.student_id);
                    const score = oldRow?.score ?? '';
                    const description = oldRow?.description ?? '';
                    return `
                        <tr data-mass-row>
                            <td>
                                <div class="assessment-student-cell">
                                    <span class="assessment-student-avatar">${student.initial}</span>
                                    <div>
                                        <strong>${student.name}</strong>
                                        <small>NIS: ${student.nis}</small>
                                    </div>
                                </div>
                                <input type="hidden" name="rows[${index}][student_id]" value="${student.student_id}">
                            </td>
                            <td>${student.class_name}</td>
                            <td class="assessment-score-cell"><input type="number" step="0.01" min="0" max="100" name="rows[${index}][score]" class="form-control form-control-sm assessment-score-input" value="${score}" data-mass-score-input></td>
                            <td><textarea name="rows[${index}][description]" class="form-control form-control-sm assessment-note-input" rows="2">${description}</textarea></td>
                            <td><span class="badge badge-status-secondary" data-mass-row-status>${buildStatusLabel(student, score)}</span></td>
                        </tr>
                    `;
                }).join('');

                cardBody.innerHTML = filtered.map((student, index) => {
                    const oldRow = getOldRow(student.student_id);
                    const score = oldRow?.score ?? '';
                    const description = oldRow?.description ?? '';
                    return `
                        <div class="coach-assessment-member-card" data-mass-row>
                            <input type="hidden" name="rows[${index}][student_id]" value="${student.student_id}">
                            <div class="assessment-student-cell mb-3">
                                <span class="assessment-student-avatar">${student.initial}</span>
                                <div>
                                    <strong>${student.name}</strong>
                                    <small>NIS: ${student.nis}</small>
                                </div>
                            </div>
                            <div class="mobile-data-list">
                                <div><span class="mobile-data-item-label">Kelas</span><p class="mobile-data-item-value">${student.class_name}</p></div>
                                <div><span class="mobile-data-item-label">Nilai</span><input type="number" step="0.01" min="0" max="100" name="rows[${index}][score]" class="form-control form-control-sm assessment-score-input" value="${score}" data-mass-score-input></div>
                                <div><span class="mobile-data-item-label">Catatan</span><textarea name="rows[${index}][description]" class="form-control form-control-sm assessment-note-input" rows="2">${description}</textarea></div>
                                <div><span class="mobile-data-item-label">Status</span><p class="mobile-data-item-value"><span class="badge badge-status-secondary" data-mass-row-status>${buildStatusLabel(student, score)}</span></p></div>
                            </div>
                        </div>
                    `;
                }).join('');

                bindRenderedRows();
                syncCounters();
            };

            const syncClassFilter = () => {
                const selectedStudents = getSelectedStudents();
                const classes = [...new Set(selectedStudents.map((student) => student.class_name))].filter(Boolean).sort();
                classFilter.innerHTML = '<option value="">Semua kelas</option>' + classes.map((className) => `<option value="${className}">${className}</option>`).join('');
            };

            extracurricularSelect?.addEventListener('change', () => {
                syncClassFilter();
                renderMassMembers();
            });
            searchInput?.addEventListener('input', renderMassMembers);
            classFilter?.addEventListener('change', renderMassMembers);

            massRoot.querySelector('[data-bulk-score]')?.addEventListener('click', () => {
                const score = window.prompt('Masukkan nilai yang sama untuk seluruh siswa yang sedang tampil:');
                if (score === null || score === '') return;
                massRoot.querySelectorAll('[data-mass-score-input]').forEach((input) => {
                    input.value = score;
                    input.dispatchEvent(new Event('input'));
                });
            });

            massRoot.querySelector('[data-bulk-note]')?.addEventListener('click', () => {
                const note = window.prompt('Masukkan catatan yang sama untuk seluruh siswa yang sedang tampil:');
                if (note === null) return;
                massRoot.querySelectorAll('textarea[name*="[description]"]').forEach((textarea) => {
                    textarea.value = note;
                });
            });

            massRoot.querySelector('[data-bulk-clear]')?.addEventListener('click', () => {
                if (!window.confirm('Kosongkan semua nilai yang sedang tampil?')) return;
                massRoot.querySelectorAll('[data-mass-score-input]').forEach((input) => {
                    input.value = '';
                    input.dispatchEvent(new Event('input'));
                });
            });

            syncClassFilter();
            renderMassMembers();
        });
    </script>
@endpush
