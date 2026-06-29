@extends('layouts.app')

@section('page_title', 'Laporan Prestasi/Penilaian')
@section('page_subtitle', 'Lihat catatan prestasi dan penilaian siswa berdasarkan filter yang dipilih')

@section('content')
    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Filter Prestasi/Penilaian</h2>
                    <p class="toolbar-hint mb-0">Saring data berdasarkan ekskul, pembina, jenis, dan periode penilaian.</p>
                </div>
                <div class="quick-actions">
                    <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'assessments', 'format' => 'csv'])) }}" class="btn btn-outline-success"><i class="bi bi-download"></i>Unduh CSV</a>
                    <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'assessments', 'format' => 'xls'])) }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-excel"></i>Unduh Excel</a>
                </div>
            </div>
            <form class="toolbar-grid">
                <div class="toolbar-col-3">
                    <label class="form-label" for="extracurricular_id">Ekstrakurikuler</label>
                    <select id="extracurricular_id" name="extracurricular_id" class="form-select">
                        <option value="">Semua ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-3">
                    <label class="form-label" for="coach_id">Pembina</label>
                    <select id="coach_id" name="coach_id" class="form-select">
                        <option value="">Semua pembina</option>
                        @foreach($coaches as $item)
                            <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="assessment_type">Jenis</label>
                    <select id="assessment_type" name="assessment_type" class="form-select">
                        <option value="">Semua jenis</option>
                        <option value="achievement" @selected($assessmentType === 'achievement')>Prestasi</option>
                        <option value="assessment" @selected($assessmentType === 'assessment')>Penilaian</option>
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="date_from">Dari</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="date_to">Sampai</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="toolbar-col-12">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Prestasi/Penilaian</div>
        <div class="card-body p-0">
            <div class="table-responsive">
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
                            <td>{{ $assessment->student->user->name ?? '-' }}</td>
                            <td>{{ $assessment->extracurricular->name ?? '-' }}</td>
                            <td>{{ $assessment->assessment_type }}</td>
                            <td>{{ $assessment->title }}</td>
                            <td>{{ $assessment->score ?? '-' }}</td>
                            <td>{{ optional($assessment->assessment_date)->format('d-m-Y') }}</td>
                            <td>{{ $assessment->coach->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><div class="empty-state"><div class="icon"><i class="bi bi-award"></i></div><p class="mb-0">Belum ada data prestasi atau penilaian.</p></div></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body">{{ $assessments->links() }}</div>
    </div>
@endsection
