@extends('layouts.app')

@section('page_title', 'Presensi Sekolah')
@section('page_subtitle', 'Pantau kehadiran siswa dan unduh rekap presensi')

@section('content')
    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Filter Presensi</h2>
                    <p class="toolbar-hint mb-0">Gunakan filter untuk melihat kehadiran siswa secara lebih spesifik.</p>
                </div>
                <div class="quick-actions">
                    <a href="{{ route('principal.attendances.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn btn-outline-success"><i class="bi bi-download"></i>Unduh CSV</a>
                    <a href="{{ route('principal.attendances.export', array_merge(request()->query(), ['format' => 'xls'])) }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-excel"></i>Unduh Excel</a>
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
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="present" @selected($status === 'present')>Hadir</option>
                        <option value="permission" @selected($status === 'permission')>Izin</option>
                        <option value="sick" @selected($status === 'sick')>Sakit</option>
                        <option value="absent" @selected($status === 'absent')>Alpa</option>
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
        <div class="card-header">Daftar Presensi</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Ekstrakurikuler</th>
                        <th>Pembina</th>
                        <th>Jadwal</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($attendances as $attendance)
                        @php
                            $statusLabel = match ($attendance->status) {
                                'present' => 'Hadir',
                                'permission' => 'Izin',
                                'sick' => 'Sakit',
                                'absent' => 'Alpa',
                                default => $attendance->status,
                            };
                        @endphp
                        <tr>
                            <td>{{ $attendance->student->user->name ?? '-' }}</td>
                            <td>{{ $attendance->extracurricular->name ?? '-' }}</td>
                            <td>{{ $attendance->schedule->coach->user->name ?? $attendance->extracurricular->coach_names }}</td>
                            <td>{{ $attendance->schedule->title ?? '-' }}</td>
                            <td>{{ optional($attendance->schedule->activity_date)->format('d-m-Y') }}</td>
                            <td><span class="badge" data-status="{{ $attendance->status }}">{{ $statusLabel }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><div class="empty-state"><div class="icon"><i class="bi bi-check2-square"></i></div><p class="mb-0">Belum ada data presensi.</p></div></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body">{{ $attendances->links() }}</div>
    </div>
@endsection
