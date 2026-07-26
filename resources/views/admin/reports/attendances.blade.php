@extends('layouts.app')

@section('page_title', 'Laporan Presensi')
@section('page_subtitle', 'Pantau kehadiran siswa berdasarkan ekskul, pembina, dan status presensi')

@section('content')
    @php
        $activeFilters = [
            ['label' => 'Ekskul', 'value' => data_get($extracurriculars->firstWhere('id', $extracurricularId), 'name')],
            ['label' => 'Pembina', 'value' => data_get($coaches->firstWhere('id', $coachId), 'user.name')],
            ['label' => 'Status', 'value' => $status === 'present' ? 'Hadir' : ($status === 'permission' ? 'Izin' : ($status === 'sick' ? 'Sakit' : ($status === 'absent' ? 'Alpa' : null)))],
            ['label' => 'Mulai', 'value' => $dateFrom ?: null],
            ['label' => 'Selesai', 'value' => $dateTo ?: null],
        ];
    @endphp

    <x-filter.card class="mb-3" title="Filter Presensi" description="Saring laporan kehadiran siswa berdasarkan ekskul, pembina, status, dan periode.">
        <x-slot:actions>
            <x-filter.export-dropdown
                :items="[
                    ['label' => 'Unduh CSV', 'href' => route('admin.reports.export', array_merge(request()->query(), ['type' => 'attendances', 'format' => 'csv'])), 'icon' => 'bi-download'],
                    ['label' => 'Unduh Excel', 'href' => route('admin.reports.export', array_merge(request()->query(), ['type' => 'attendances', 'format' => 'xls'])), 'icon' => 'bi-file-earmark-excel'],
                ]"
            />
        </x-slot:actions>
        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route('admin.attendances.index')" />
        </x-slot:active>
        <form class="toolbar-grid" method="get">
            <x-filter.field label="Ekstrakurikuler" for="report_attendance_extracurricular" col="toolbar-col-3">
                <select id="report_attendance_extracurricular" name="extracurricular_id" class="form-select">
                    <option value="">Semua ekskul</option>
                    @foreach($extracurriculars as $item)
                        <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Pembina" for="report_attendance_coach" col="toolbar-col-3">
                <select id="report_attendance_coach" name="coach_id" class="form-select">
                    <option value="">Semua pembina</option>
                    @foreach($coaches as $item)
                        <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Status" for="report_attendance_status" col="toolbar-col-2">
                <select id="report_attendance_status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="present" @selected($status === 'present')>Hadir</option>
                    <option value="permission" @selected($status === 'permission')>Izin</option>
                    <option value="sick" @selected($status === 'sick')>Sakit</option>
                    <option value="absent" @selected($status === 'absent')>Alpa</option>
                </select>
            </x-filter.field>
            <x-filter.field label="Tanggal mulai" for="report_attendance_date_from" col="toolbar-col-2">
                <input id="report_attendance_date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
            </x-filter.field>
            <x-filter.field label="Tanggal selesai" for="report_attendance_date_to" col="toolbar-col-2">
                <input id="report_attendance_date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
            </x-filter.field>
            <x-filter.actions col="toolbar-col-12">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                <a href="{{ route('admin.attendances.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>
        </form>
    </x-filter.card>

    <div class="card">
        <div class="card-header">Daftar Presensi</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Ekstrakurikuler</th>
                        <th>Jadwal</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->student->user->name ?? '-' }}</td>
                            <td>{{ $attendance->extracurricular->name ?? '-' }}</td>
                            <td>{{ $attendance->schedule->title ?? '-' }}</td>
                            <td>{{ optional($attendance->schedule->activity_date)->format('d-m-Y') }}</td>
                            <td><span class="badge" data-status="{{ $attendance->display_status }}">{{ $attendance->display_status_label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><div class="empty-state"><div class="icon"><i class="bi bi-check2-square"></i></div><p class="mb-0">Belum ada data presensi.</p></div></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($attendances as $attendance)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <div>
                                <h3 class="mobile-data-card-title">{{ $attendance->student->user->name ?? '-' }}</h3>
                                <div class="small text-muted">{{ $attendance->extracurricular->name ?? '-' }}</div>
                            </div>
                            <span class="badge" data-status="{{ $attendance->display_status }}">{{ $attendance->display_status_label }}</span>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Pembina</span><p class="mobile-data-item-value">{{ $attendance->schedule->coach->user->name ?? $attendance->extracurricular->coach_names }}</p></div>
                            <div><span class="mobile-data-item-label">Jadwal</span><p class="mobile-data-item-value">{{ $attendance->schedule->title ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($attendance->schedule->activity_date)->format('d-m-Y') }}</p></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state"><div class="icon"><i class="bi bi-check2-square"></i></div><p class="mb-0">Belum ada data presensi.</p></div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $attendances->links() }}</div>
    </div>
@endsection
