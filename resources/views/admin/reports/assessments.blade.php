@extends('layouts.app')

@section('page_title', 'Laporan Prestasi dan Penilaian')
@section('page_subtitle', 'Lihat prestasi kegiatan ekstrakurikuler dan penilaian siswa berdasarkan filter yang dipilih')

@section('content')
    @php
        $activeFilters = [
            ['label' => 'Ekskul', 'value' => data_get($extracurriculars->firstWhere('id', $extracurricularId), 'name')],
            ['label' => 'Pembina', 'value' => data_get($coaches->firstWhere('id', $coachId), 'user.name')],
            ['label' => 'Jenis', 'value' => $assessmentType === 'achievement' ? 'Prestasi kegiatan' : ($assessmentType === 'assessment' ? 'Penilaian siswa' : null)],
            ['label' => 'Mulai', 'value' => $dateFrom ?: null],
            ['label' => 'Selesai', 'value' => $dateTo ?: null],
        ];
    @endphp

    <x-filter.card class="mb-3" title="Filter Prestasi dan Penilaian" description="Saring data berdasarkan ekskul, pembina, jenis, dan periode.">
        <x-slot:actions>
            <x-filter.export-dropdown
                :items="[
                    ['label' => 'Unduh CSV', 'href' => route('admin.reports.export', array_merge(request()->query(), ['type' => 'assessments', 'format' => 'csv'])), 'icon' => 'bi-download'],
                    ['label' => 'Unduh Excel', 'href' => route('admin.reports.export', array_merge(request()->query(), ['type' => 'assessments', 'format' => 'xls'])), 'icon' => 'bi-file-earmark-excel'],
                ]"
            />
        </x-slot:actions>
        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route('admin.assessments.index')" />
        </x-slot:active>
        <form class="toolbar-grid" method="get">
            <x-filter.field label="Ekstrakurikuler" for="assessment_report_extracurricular" col="toolbar-col-3">
                <select id="assessment_report_extracurricular" name="extracurricular_id" class="form-select">
                    <option value="">Semua ekskul</option>
                    @foreach($extracurriculars as $item)
                        <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Pembina" for="assessment_report_coach" col="toolbar-col-3">
                <select id="assessment_report_coach" name="coach_id" class="form-select">
                    <option value="">Semua pembina</option>
                    @foreach($coaches as $item)
                        <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Jenis" for="assessment_report_type" col="toolbar-col-2">
                <select id="assessment_report_type" name="assessment_type" class="form-select">
                    <option value="">Semua jenis</option>
                    <option value="achievement" @selected($assessmentType === 'achievement')>Prestasi Kegiatan</option>
                    <option value="assessment" @selected($assessmentType === 'assessment')>Penilaian Siswa</option>
                </select>
            </x-filter.field>
            <x-filter.field label="Tanggal mulai" for="assessment_report_date_from" col="toolbar-col-2">
                <input id="assessment_report_date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
            </x-filter.field>
            <x-filter.field label="Tanggal selesai" for="assessment_report_date_to" col="toolbar-col-2">
                <input id="assessment_report_date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
            </x-filter.field>
            <x-filter.actions col="toolbar-col-12">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                <a href="{{ route('admin.assessments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>
        </form>
    </x-filter.card>

    <div class="card">
        <div class="card-header">Daftar Prestasi Kegiatan / Penilaian Siswa</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Ekstrakurikuler</th>
                        <th>Jenis</th>
                        <th>Judul</th>
                        <th>Nilai</th>
                        <th>Tanggal</th>
                        <th>Pembina</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($assessments as $assessment)
                        <tr>
                            <td>{{ $assessment->student->user->name ?? ($assessment->assessment_type === 'achievement' ? 'Prestasi kegiatan' : '-') }}</td>
                            <td>{{ $assessment->extracurricular->name ?? '-' }}</td>
                            <td>{{ $assessment->assessment_type === 'achievement' ? 'Prestasi Kegiatan' : 'Penilaian Siswa' }}</td>
                            <td>{{ $assessment->title }}</td>
                            <td>{{ $assessment->score ?? '-' }}</td>
                            <td>{{ optional($assessment->assessment_date)->format('d-m-Y') }}</td>
                            <td>{{ $assessment->coach->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><div class="empty-state"><div class="icon"><i class="bi bi-award"></i></div><p class="mb-0">Belum ada data prestasi kegiatan atau penilaian siswa.</p></div></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($assessments as $assessment)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <div>
                                <h3 class="mobile-data-card-title">{{ $assessment->student->user->name ?? '-' }}</h3>
                                <div class="small text-muted">{{ $assessment->extracurricular->name ?? '-' }}</div>
                            </div>
                            <span class="badge" data-status="{{ $assessment->assessment_type }}">{{ $assessment->assessment_type_label ?? $assessment->assessment_type }}</span>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Judul</span><p class="mobile-data-item-value">{{ $assessment->title }}</p></div>
                            <div><span class="mobile-data-item-label">Nilai</span><p class="mobile-data-item-value">{{ $assessment->score ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($assessment->assessment_date)->format('d-m-Y') }}</p></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state"><div class="icon"><i class="bi bi-award"></i></div><p class="mb-0">Belum ada data penilaian.</p></div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $assessments->links() }}</div>
    </div>
@endsection
