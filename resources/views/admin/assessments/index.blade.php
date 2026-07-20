@extends('layouts.app')

@section('page_title', 'Kelola Prestasi dan Penilaian')
@section('page_subtitle', 'Prestasi kegiatan ekstrakurikuler dan penilaian siswa dikelola dari panel admin')

@section('content')
    @php
        $hasFilters = filled($extracurricularId) || filled($coachId) || filled($assessmentType) || filled($dateFrom) || filled($dateTo) || request()->has('page');
        $activeTab = old('active_tab', request()->string('tab')->toString());
        $listViewType = request()->string('list_view')->toString();
        if (! in_array($listViewType, ['all', 'achievement', 'assessment'], true)) {
            $listViewType = $assessmentType ?: 'all';
        }
        if (! in_array($activeTab, ['achievement', 'assessment', 'list'], true)) {
            $activeTab = 'list';
        }
    @endphp

    <div class="card mb-3">
        <div class="card-body">
            <div class="section-header-inline mb-0">
                <div>
                    <h2>Kelola Data</h2>
                    <p>Pisahkan input prestasi kegiatan, penilaian siswa, dan daftar data agar alur kerja lebih ringkas.</p>
                </div>
            </div>

            <div class="tab-scroll-nav mt-3" role="tablist" aria-label="Tab prestasi dan penilaian">
                <button class="tab-scroll-nav__item @if($activeTab === 'achievement') is-active @endif" data-bs-toggle="tab" data-bs-target="#assessment-achievement-tab" type="button" role="tab" aria-selected="{{ $activeTab === 'achievement' ? 'true' : 'false' }}">
                    Prestasi Ekstrakurikuler
                </button>
                <button class="tab-scroll-nav__item @if($activeTab === 'assessment') is-active @endif" data-bs-toggle="tab" data-bs-target="#assessment-student-tab" type="button" role="tab" aria-selected="{{ $activeTab === 'assessment' ? 'true' : 'false' }}">
                    Penilaian Siswa
                </button>
                <button class="tab-scroll-nav__item @if($activeTab === 'list') is-active @endif" data-bs-toggle="tab" data-bs-target="#assessment-list-tab" type="button" role="tab" aria-selected="{{ $activeTab === 'list' ? 'true' : 'false' }}">
                    Daftar Data
                </button>
            </div>
        </div>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade @if($activeTab === 'achievement') show active @endif" id="assessment-achievement-tab" role="tabpanel" tabindex="0">
            <div class="card">
                <div class="card-header">Form Prestasi Ekstrakurikuler</div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.assessments.store') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="active_tab" value="achievement">
                        <input type="hidden" name="assessment_type" value="achievement">

                        <div class="col-12">
                            <div class="page-summary-banner">
                                <div class="data-point-label">Prestasi Ekstrakurikuler</div>
                                <p class="data-point-value mb-2">Catat prestasi ekstrakurikuler tanpa mencampurkannya dengan nilai siswa.</p>
                                <div class="helper-text mb-0">Fitur unggah bukti atau dokumentasi belum tersedia di modul ini, jadi gunakan deskripsi untuk mencatat detail pendukung.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-section-card">
                                <h3 class="form-section-title">Informasi Prestasi</h3>
                                <p class="form-section-copy">Isi data prestasi pada tingkat ekstrakurikuler.</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="achievement_extracurricular_id">Ekstrakurikuler</label>
                                        <select id="achievement_extracurricular_id" name="extracurricular_id" class="form-select" required>
                                            <option value="">Pilih Ekstrakurikuler</option>
                                            @foreach($extracurriculars as $item)
                                                <option value="{{ $item->id }}" @selected(old('active_tab', $activeTab) === 'achievement' && old('extracurricular_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="achievement_coach_id">Pembina</label>
                                        <select id="achievement_coach_id" name="coach_id" class="form-select" data-assessment-coach-select>
                                            <option value="">Pilih Pembina (Opsional)</option>
                                            @foreach($coaches as $coach)
                                                <option value="{{ $coach->id }}" data-extracurricular-ids="{{ $coach->extracurriculars->pluck('id')->implode(',') }}" @selected(old('active_tab', $activeTab) === 'achievement' && old('coach_id') == $coach->id)>{{ $coach->user->name ?? 'Pembina' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="achievement_date">Tanggal</label>
                                        <input id="achievement_date" type="date" name="assessment_date" value="{{ old('active_tab', $activeTab) === 'achievement' ? old('assessment_date') : '' }}" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="achievement_title">Judul prestasi</label>
                                        <input id="achievement_title" type="text" name="title" value="{{ old('active_tab', $activeTab) === 'achievement' ? old('title') : '' }}" class="form-control" placeholder="Contoh: Juara 1 Lomba Futsal Kota" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="achievement_description">Deskripsi</label>
                                        <textarea id="achievement_description" name="description" class="form-control" rows="4" placeholder="Jelaskan prestasi, tingkat lomba, dan dokumentasi pendukung">{{ old('active_tab', $activeTab) === 'achievement' ? old('description') : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-actions justify-content-end">
                                <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan prestasi..."><i class="bi bi-save"></i>Simpan Prestasi</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="tab-pane fade @if($activeTab === 'assessment') show active @endif" id="assessment-student-tab" role="tabpanel" tabindex="0">
            <div class="card">
                <div class="card-header">Form Penilaian Siswa</div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.assessments.store') }}" class="row g-3">
                        @csrf
                        <input type="hidden" name="active_tab" value="assessment">
                        <input type="hidden" name="assessment_type" value="assessment">

                        <div class="col-12">
                            <div class="page-summary-banner">
                                <div class="data-point-label">Penilaian Siswa</div>
                                <p class="data-point-value mb-2">Catat penilaian siswa secara terpisah agar nilai dan catatan pembina lebih mudah dikelola.</p>
                                <div class="helper-text mb-0">Siswa dan nilai hanya digunakan pada penilaian siswa, bukan prestasi kegiatan.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-section-card">
                                <h3 class="form-section-title">Informasi Penilaian</h3>
                                <p class="form-section-copy">Pilih ekskul, siswa, dan pembina yang sesuai sebelum menyimpan penilaian.</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="student_assessment_extracurricular_id">Ekstrakurikuler</label>
                                        <select id="student_assessment_extracurricular_id" name="extracurricular_id" class="form-select" required data-assessment-extracurricular-select>
                                            <option value="">Pilih Ekstrakurikuler</option>
                                            @foreach($extracurriculars as $item)
                                                <option value="{{ $item->id }}" @selected(old('active_tab', $activeTab) === 'assessment' && old('extracurricular_id') == $item->id)>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="student_assessment_student_id">Siswa</label>
                                        <select id="student_assessment_student_id" name="student_id" class="form-select" required data-assessment-student-select>
                                            <option value="">Pilih Siswa</option>
                                            @foreach($approvedRegistrations as $registration)
                                                <option value="{{ $registration->student_id }}" data-extracurricular-id="{{ $registration->extracurricular_id }}" @selected(old('active_tab', $activeTab) === 'assessment' && old('student_id') == $registration->student_id)>{{ $registration->student->user->name ?? 'Siswa' }} ({{ $registration->student->nis ?? '-' }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="student_assessment_coach_id">Pembina</label>
                                        <select id="student_assessment_coach_id" name="coach_id" class="form-select" data-assessment-coach-select>
                                            <option value="">Pilih Pembina (Opsional)</option>
                                            @foreach($coaches as $coach)
                                                <option value="{{ $coach->id }}" data-extracurricular-ids="{{ $coach->extracurriculars->pluck('id')->implode(',') }}" @selected(old('active_tab', $activeTab) === 'assessment' && old('coach_id') == $coach->id)>{{ $coach->user->name ?? 'Pembina' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="student_assessment_title">Jenis penilaian</label>
                                        <input id="student_assessment_title" type="text" name="title" value="{{ old('active_tab', $activeTab) === 'assessment' ? old('title') : '' }}" class="form-control" placeholder="Contoh: Disiplin latihan" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="student_assessment_score">Nilai</label>
                                        <input id="student_assessment_score" type="number" step="0.01" min="0" max="100" name="score" value="{{ old('active_tab', $activeTab) === 'assessment' ? old('score') : '' }}" class="form-control" placeholder="0 - 100">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="student_assessment_date">Tanggal</label>
                                        <input id="student_assessment_date" type="date" name="assessment_date" value="{{ old('active_tab', $activeTab) === 'assessment' ? old('assessment_date') : '' }}" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="student_assessment_description">Catatan</label>
                                        <textarea id="student_assessment_description" name="description" class="form-control" rows="4" placeholder="Catat hasil penilaian, perilaku, atau tindak lanjut">{{ old('active_tab', $activeTab) === 'assessment' ? old('description') : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-actions justify-content-end">
                                <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan penilaian..."><i class="bi bi-save"></i>Simpan Penilaian</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="tab-pane fade @if($activeTab === 'list') show active @endif" id="assessment-list-tab" role="tabpanel" tabindex="0">
            <div class="card mb-3">
                <div class="card-body toolbar-card">
                    <div class="section-header-inline mb-3">
                        <div>
                            <h2>Daftar Data</h2>
                            <p>Gunakan filter untuk menampilkan data penting, lalu ekspor atau buka versi laporan jika diperlukan.</p>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-download"></i>Ekspor
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                <li><a class="dropdown-item" href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'assessments', 'format' => 'xls'])) }}"><i class="bi bi-file-earmark-excel me-2"></i>Unduh Excel</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'assessments', 'format' => 'csv'])) }}"><i class="bi bi-download me-2"></i>Unduh CSV</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.assessments.report', request()->query()) }}"><i class="bi bi-table me-2"></i>Lihat Versi Laporan</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-scroll-nav mb-3" role="tablist" aria-label="Jenis daftar data">
                        <a href="{{ route('admin.assessments.index', array_merge(request()->except('assessment_type', 'page'), ['tab' => 'list', 'list_view' => 'all'])) }}" class="tab-scroll-nav__item @if($listViewType === 'all') is-active @endif">Semua</a>
                        <a href="{{ route('admin.assessments.index', array_merge(request()->except('page'), ['tab' => 'list', 'list_view' => 'achievement', 'assessment_type' => 'achievement'])) }}" class="tab-scroll-nav__item @if($listViewType === 'achievement') is-active @endif">Prestasi Ekstrakurikuler</a>
                        <a href="{{ route('admin.assessments.index', array_merge(request()->except('page'), ['tab' => 'list', 'list_view' => 'assessment', 'assessment_type' => 'assessment'])) }}" class="tab-scroll-nav__item @if($listViewType === 'assessment') is-active @endif">Penilaian Siswa</a>
                    </div>

                    <p class="mb-2">
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#assessmentFilterPanel" aria-expanded="{{ $hasFilters ? 'true' : 'false' }}" aria-controls="assessmentFilterPanel">
                            <i class="bi bi-funnel"></i>Filter Data
                        </button>
                    </p>

                    <div class="collapse @if($hasFilters) show @endif" id="assessmentFilterPanel">
                        <form class="toolbar-grid">
                            <input type="hidden" name="tab" value="list">
                            <input type="hidden" name="list_view" value="{{ $listViewType }}">
                            <div class="toolbar-col-3">
                                <label class="form-label" for="filter_extracurricular_id">Ekstrakurikuler</label>
                                <select id="filter_extracurricular_id" name="extracurricular_id" class="form-select">
                                    <option value="">Semua ekstrakurikuler</option>
                                    @foreach($extracurriculars as $item)
                                        <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="toolbar-col-3">
                                <label class="form-label" for="filter_coach_id">Pembina</label>
                                <select id="filter_coach_id" name="coach_id" class="form-select">
                                    <option value="">Semua pembina</option>
                                    @foreach($coaches as $item)
                                        <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="toolbar-col-2">
                                <label class="form-label" for="filter_assessment_type">Jenis</label>
                                <select id="filter_assessment_type" name="assessment_type" class="form-select">
                                    <option value="">Semua jenis</option>
                                    <option value="achievement" @selected($assessmentType === 'achievement')>Prestasi Ekstrakurikuler</option>
                                    <option value="assessment" @selected($assessmentType === 'assessment')>Penilaian Siswa</option>
                                </select>
                            </div>
                            <div class="toolbar-col-4">
                                <label class="form-label">Periode</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input id="filter_date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control" placeholder="Dari">
                                    </div>
                                    <div class="col-6">
                                        <input id="filter_date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control" placeholder="Sampai">
                                    </div>
                                </div>
                            </div>
                            <div class="toolbar-col-12">
                                <div class="form-actions">
                                    <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan</button>
                                    <a href="{{ route('admin.assessments.index', ['tab' => 'list', 'list_view' => $listViewType]) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="desktop-table table-responsive">
                    <table class="table table-striped table-compact mb-0">
                        <thead>
                        @if($listViewType === 'achievement')
                            <tr>
                                <th>Judul Prestasi</th>
                                <th>Ekstrakurikuler</th>
                                <th>Tingkat / Peringkat</th>
                                <th>Tanggal</th>
                                <th>Pembina</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        @elseif($listViewType === 'assessment')
                            <tr>
                                <th>Siswa</th>
                                <th>Ekstrakurikuler</th>
                                <th>Jenis Penilaian</th>
                                <th>Nilai</th>
                                <th>Tanggal</th>
                                <th>Pembina</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        @else
                            <tr>
                                <th>Jenis</th>
                                <th>Judul</th>
                                <th>Subjek</th>
                                <th>Ekstrakurikuler</th>
                                <th>Hasil</th>
                                <th>Tanggal</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        @endif
                        </thead>
                        <tbody>
                        @forelse($assessments as $row)
                            @php
                                $isAchievement = $row->assessment_type === 'achievement';
                                $typeLabel = $isAchievement ? 'Prestasi Ekstrakurikuler' : 'Penilaian Siswa';
                                $subjectLabel = $isAchievement ? ($row->student?->user?->name ?: 'Tim Ekstrakurikuler') : ($row->student->user->name ?? 'Siswa');
                                $resultLabel = $isAchievement
                                    ? \Illuminate\Support\Str::limit($row->description ?: 'Prestasi tercatat', 42)
                                    : ($row->score !== null ? (string) $row->score : 'Nilai belum diisi');
                                $coachLabel = $row->coach->user->name ?? 'Belum ditentukan';
                            @endphp
                            <tr>
                                @if($listViewType === 'achievement')
                                    <td>{{ $row->title }}</td>
                                    <td>{{ $row->extracurricular->name ?? 'Ekstrakurikuler belum dipilih' }}</td>
                                    <td>{{ $resultLabel }}</td>
                                    <td>{{ optional($row->assessment_date)->format('d-m-Y') }}</td>
                                    <td>{{ $coachLabel }}</td>
                                @elseif($listViewType === 'assessment')
                                    <td>{{ $subjectLabel }}</td>
                                    <td>{{ $row->extracurricular->name ?? 'Ekstrakurikuler belum dipilih' }}</td>
                                    <td>{{ $row->title }}</td>
                                    <td>{{ $resultLabel }}</td>
                                    <td>{{ optional($row->assessment_date)->format('d-m-Y') }}</td>
                                    <td>{{ $coachLabel }}</td>
                                @else
                                    <td><span class="badge {{ $isAchievement ? 'badge-status-success' : 'badge-status-warning' }}">{{ $typeLabel }}</span></td>
                                    <td>{{ $row->title }}</td>
                                    <td>{{ $subjectLabel }}</td>
                                    <td>{{ $row->extracurricular->name ?? 'Ekstrakurikuler belum dipilih' }}</td>
                                    <td>{{ $resultLabel }}</td>
                                    <td>{{ optional($row->assessment_date)->format('d-m-Y') }}</td>
                                @endif
                                <td class="text-end table-action-col">
                                    <div class="table-inline-actions table-inline-actions--compact justify-content-end">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary action-button-compact assessment-detail-trigger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#assessmentDetailModal"
                                            data-type="{{ $typeLabel }}"
                                            data-title="{{ $row->title }}"
                                            data-student="{{ $subjectLabel }}"
                                            data-extracurricular="{{ $row->extracurricular->name ?? 'Ekstrakurikuler belum dipilih' }}"
                                            data-score="{{ $resultLabel }}"
                                            data-date="{{ optional($row->assessment_date)->format('d-m-Y') }}"
                                            data-coach="{{ $coachLabel }}"
                                            data-description="{{ $row->description ?: 'Belum ada catatan tambahan.' }}"
                                        >
                                            <i class="bi bi-eye"></i>
                                            <span class="d-none d-md-inline">Detail</span>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-ui-tooltip="Tindakan lainnya">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                                <li><a class="dropdown-item" href="{{ route('admin.assessments.edit', $row) }}"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                                <li>
                                                    <form method="post" action="{{ route('admin.assessments.destroy', $row) }}" onsubmit="return confirm('Hapus data ini?')">
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
                                <td colspan="{{ $listViewType === 'all' ? '7' : '6' }}">
                                    <div class="empty-state">
                                        <div class="icon"><i class="bi bi-award"></i></div>
                                        <p class="mb-0">Belum ada data prestasi ekstrakurikuler atau penilaian siswa.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mobile-stack-table p-3">
                    @forelse($assessments as $row)
                        @php
                            $isAchievement = $row->assessment_type === 'achievement';
                            $typeLabel = $isAchievement ? 'Prestasi Ekstrakurikuler' : 'Penilaian Siswa';
                            $subjectLabel = $isAchievement ? ($row->student?->user?->name ?: 'Tim Ekstrakurikuler') : ($row->student->user->name ?? 'Siswa');
                            $resultLabel = $isAchievement
                                ? \Illuminate\Support\Str::limit($row->description ?: 'Prestasi tercatat', 42)
                                : ($row->score !== null ? (string) $row->score : 'Nilai belum diisi');
                            $coachLabel = $row->coach->user->name ?? 'Belum ditentukan';
                        @endphp
                        <div class="mobile-data-card">
                            <div class="mobile-data-card-header">
                                <div>
                                    <h3 class="mobile-data-card-title">{{ $row->title }}</h3>
                                    <div class="small text-muted">{{ $row->extracurricular->name ?? 'Ekstrakurikuler belum dipilih' }}</div>
                                </div>
                                <span class="badge {{ $isAchievement ? 'badge-status-success' : 'badge-status-warning' }}">{{ $typeLabel }}</span>
                            </div>
                            <div class="mobile-data-list mb-3">
                                <div><span class="mobile-data-item-label">{{ $listViewType === 'achievement' ? 'Tingkat / Peringkat' : ($listViewType === 'assessment' ? 'Siswa' : 'Subjek') }}</span><p class="mobile-data-item-value">{{ $listViewType === 'achievement' ? $resultLabel : $subjectLabel }}</p></div>
                                <div><span class="mobile-data-item-label">{{ $listViewType === 'assessment' ? 'Nilai' : 'Hasil' }}</span><p class="mobile-data-item-value">{{ $listViewType === 'assessment' ? $resultLabel : $resultLabel }}</p></div>
                                <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($row->assessment_date)->format('d-m-Y') }}</p></div>
                                <div><span class="mobile-data-item-label">Pembina</span><p class="mobile-data-item-value">{{ $coachLabel }}</p></div>
                            </div>
                            <div class="table-inline-actions table-inline-actions--compact">
                                <button type="button" class="btn btn-sm btn-outline-primary action-button-icon assessment-detail-trigger" data-bs-toggle="modal" data-bs-target="#assessmentDetailModal" data-type="{{ $typeLabel }}" data-title="{{ $row->title }}" data-student="{{ $subjectLabel }}" data-extracurricular="{{ $row->extracurricular->name ?? 'Ekstrakurikuler belum dipilih' }}" data-score="{{ $resultLabel }}" data-date="{{ optional($row->assessment_date)->format('d-m-Y') }}" data-coach="{{ $coachLabel }}" data-description="{{ $row->description ?: 'Belum ada catatan tambahan.' }}"><i class="bi bi-eye"></i></button>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                        <li><a class="dropdown-item" href="{{ route('admin.assessments.edit', $row) }}"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                        <li>
                                            <form method="post" action="{{ route('admin.assessments.destroy', $row) }}" onsubmit="return confirm('Hapus data ini?')">
                                                @csrf
                                                @method('delete')
                                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Hapus</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="icon"><i class="bi bi-award"></i></div>
                            <p class="mb-0">Belum ada data prestasi ekstrakurikuler atau penilaian siswa.</p>
                        </div>
                    @endforelse
                </div>

                <div class="card-body">{{ $assessments->appends(['tab' => 'list', 'list_view' => $listViewType])->links() }}</div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assessmentDetailModal" tabindex="-1" aria-labelledby="assessmentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content verification-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="modal-title h5 mb-1" id="assessmentDetailModalLabel">Detail Data</h2>
                        <p class="text-muted mb-0" id="assessmentDetailMeta">—</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="data-points">
                        <div class="data-point"><div class="data-point-label">Jenis</div><p class="data-point-value mb-0" id="assessmentDetailType">—</p></div>
                        <div class="data-point"><div class="data-point-label">Subjek</div><p class="data-point-value mb-0" id="assessmentDetailStudent">—</p></div>
                        <div class="data-point"><div class="data-point-label">Hasil</div><p class="data-point-value mb-0" id="assessmentDetailScore">—</p></div>
                        <div class="data-point"><div class="data-point-label">Tanggal</div><p class="data-point-value mb-0" id="assessmentDetailDate">—</p></div>
                        <div class="data-point"><div class="data-point-label">Pembina</div><p class="data-point-value mb-0" id="assessmentDetailCoach">—</p></div>
                        <div class="info-item">
                            <div class="title">Catatan / Deskripsi</div>
                            <div class="small mt-2" id="assessmentDetailDescription">Belum ada catatan tambahan.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
