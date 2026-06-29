@extends('layouts.app')

@section('page_title', 'Riwayat Presensi Pribadi')
@section('page_subtitle', 'Pantau kehadiran kegiatan ekstrakurikuler')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Filter Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua Ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string)$extracurricularId === (string)$item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button></div>
                <div class="col-md-2"><a href="{{ route('student.attendances.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a></div>
                <div class="col-md-3">
                    <div class="dropdown w-100">
                        <button class="btn btn-outline-success w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end w-100">
                            <li><a class="dropdown-item" href="{{ route('student.attendances.export', array_merge(request()->query(), ['format' => 'csv'])) }}">Unduh CSV</a></li>
                            <li><a class="dropdown-item" href="{{ route('student.attendances.export', array_merge(request()->query(), ['format' => 'xls'])) }}">Unduh Excel</a></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Ekstrakurikuler</th>
                    <th>Jadwal</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
                </thead>
                <tbody>
                @forelse($attendances as $row)
                    @php
                        $statusLabel = match ($row->status) {
                            'present' => 'Hadir',
                            'absent' => 'Alpa',
                            'sick' => 'Sakit',
                            'permission' => 'Izin',
                            default => $row->status,
                        };
                    @endphp
                    <tr>
                        <td>{{ $row->extracurricular->name ?? '-' }}</td>
                        <td>{{ $row->schedule->title ?? '-' }}</td>
                        <td><span class="badge" data-status="{{ $row->status }}">{{ $statusLabel }}</span></td>
                        <td>{{ $row->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-check2-square"></i></div>
                                <p class="mb-0">Belum ada data presensi.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $attendances->links() }}</div>
    </div>
@endsection
