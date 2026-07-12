@extends('layouts.app')

@section('page_title', 'Kelola Prestasi dan Penilaian')
@section('page_subtitle', 'Prestasi kegiatan ekstrakurikuler dan penilaian siswa dikelola dari panel admin')

@section('content')
    <div class="card mb-3">
        <div class="card-header">Tambah Prestasi Kegiatan / Penilaian Siswa</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.assessments.store') }}" class="row g-3">
                @csrf
                @include('admin.assessments._form')
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Filter Daftar Prestasi dan Penilaian</h2>
                    <p class="toolbar-hint mb-0">Saring data berdasarkan ekskul, pembina, jenis, dan periode.</p>
                </div>
                <div class="quick-actions">
                    <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'assessments', 'format' => 'csv'])) }}" class="btn btn-outline-success"><i class="bi bi-download"></i>Unduh CSV</a>
                    <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'assessments', 'format' => 'xls'])) }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-excel"></i>Unduh Excel</a>
                    <a href="{{ route('admin.assessments.report', request()->query()) }}" class="btn btn-outline-secondary"><i class="bi bi-table"></i>Lihat Versi Laporan</a>
                </div>
            </div>

            <form class="toolbar-grid">
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
                        <option value="achievement" @selected($assessmentType === 'achievement')>Prestasi Kegiatan</option>
                        <option value="assessment" @selected($assessmentType === 'assessment')>Penilaian Siswa</option>
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="filter_date_from">Dari</label>
                    <input id="filter_date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="filter_date_to">Sampai</label>
                    <input id="filter_date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="toolbar-col-12 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                    <a href="{{ route('admin.assessments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
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
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($assessments as $row)
                    <tr>
                        <td>{{ $row->student->user->name ?? ($row->assessment_type === 'achievement' ? 'Prestasi kegiatan' : '-') }}</td>
                        <td>{{ $row->extracurricular->name ?? '-' }}</td>
                        <td>{{ $row->assessment_type === 'achievement' ? 'Prestasi Kegiatan' : 'Penilaian Siswa' }}</td>
                        <td>{{ $row->title }}</td>
                        <td>{{ $row->score ?? '-' }}</td>
                        <td>{{ optional($row->assessment_date)->format('d-m-Y') }}</td>
                        <td>{{ $row->coach->user->name ?? '-' }}</td>
                        <td class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.assessments.edit', $row) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                            <form method="post" action="{{ route('admin.assessments.destroy', $row) }}" onsubmit="return confirm('Hapus data ini?')">
                                @csrf
                                @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-award"></i></div>
                                <p class="mb-0">Belum ada data prestasi kegiatan atau penilaian siswa.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $assessments->links() }}</div>
    </div>
@endsection
