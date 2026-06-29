@extends('layouts.app')

@section('page_title', 'Laporan Peserta Ekstrakurikuler')
@section('page_subtitle', 'Rekap peserta yang sudah disetujui berdasarkan filter yang dipilih')

@section('content')
    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Filter Laporan Peserta</h2>
                    <p class="toolbar-hint mb-0">Saring peserta berdasarkan ekstrakurikuler, pembina, dan periode pendaftaran.</p>
                </div>
                <div class="quick-actions">
                    <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'participants', 'format' => 'csv'])) }}" class="btn btn-outline-success"><i class="bi bi-download"></i>Unduh CSV</a>
                    <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'participants', 'format' => 'xls'])) }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-excel"></i>Unduh Excel</a>
                </div>
            </div>
            <form class="toolbar-grid">
                <div class="toolbar-col-4">
                    <label class="form-label" for="extracurricular_id">Ekstrakurikuler</label>
                    <select id="extracurricular_id" name="extracurricular_id" class="form-select">
                        <option value="">Semua ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-4">
                    <label class="form-label" for="coach_id">Pembina</label>
                    <select id="coach_id" name="coach_id" class="form-select">
                        <option value="">Semua pembina</option>
                        @foreach($coaches as $item)
                            <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                        @endforeach
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
        <div class="card-header">Daftar Peserta</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>NIS</th>
                        <th>Ekstrakurikuler</th>
                        <th>Pembina</th>
                        <th>Tanggal Daftar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($participants as $participant)
                        <tr>
                            <td>{{ $participant->student->user->name ?? '-' }}</td>
                            <td>{{ $participant->student->nis ?? '-' }}</td>
                            <td>{{ $participant->extracurricular->name ?? '-' }}</td>
                            <td>{{ $participant->extracurricular->coach_names }}</td>
                            <td>{{ optional($participant->registration_date)->format('d-m-Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><div class="empty-state"><div class="icon"><i class="bi bi-card-checklist"></i></div><p class="mb-0">Belum ada data peserta.</p></div></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body">{{ $participants->links() }}</div>
    </div>
@endsection
