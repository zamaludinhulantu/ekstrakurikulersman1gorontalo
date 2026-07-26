@extends('layouts.app')

@section('page_title', 'Laporan Jadwal Kegiatan')
@section('page_subtitle', 'Pantau agenda kegiatan ekstrakurikuler berdasarkan pembina dan periode')

@section('content')
    @php
        $activeFilters = [
            ['label' => 'Ekskul', 'value' => data_get($extracurriculars->firstWhere('id', $extracurricularId), 'name')],
            ['label' => 'Pembina', 'value' => data_get($coaches->firstWhere('id', $coachId), 'user.name')],
            ['label' => 'Mulai', 'value' => $dateFrom ?: null],
            ['label' => 'Selesai', 'value' => $dateTo ?: null],
        ];
    @endphp

    <x-filter.card class="mb-3" title="Filter Jadwal" description="Saring jadwal kegiatan agar pencarian agenda lebih cepat.">
        <x-slot:actions>
            <x-filter.export-dropdown
                :items="[
                    ['label' => 'Unduh CSV', 'href' => route('admin.reports.export', array_merge(request()->query(), ['type' => 'schedules', 'format' => 'csv'])), 'icon' => 'bi-download'],
                    ['label' => 'Unduh Excel', 'href' => route('admin.reports.export', array_merge(request()->query(), ['type' => 'schedules', 'format' => 'xls'])), 'icon' => 'bi-file-earmark-excel'],
                ]"
            />
        </x-slot:actions>
        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route('admin.schedules.index')" />
        </x-slot:active>
        <form class="toolbar-grid" method="get">
            <x-filter.field label="Ekstrakurikuler" for="schedule_report_extracurricular" col="toolbar-col-4">
                <select id="schedule_report_extracurricular" name="extracurricular_id" class="form-select">
                    <option value="">Semua ekskul</option>
                    @foreach($extracurriculars as $item)
                        <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Pembina" for="schedule_report_coach" col="toolbar-col-4">
                <select id="schedule_report_coach" name="coach_id" class="form-select">
                    <option value="">Semua pembina</option>
                    @foreach($coaches as $item)
                        <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Tanggal mulai" for="schedule_report_date_from" col="toolbar-col-2">
                <input id="schedule_report_date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
            </x-filter.field>
            <x-filter.field label="Tanggal selesai" for="schedule_report_date_to" col="toolbar-col-2">
                <input id="schedule_report_date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
            </x-filter.field>
            <x-filter.actions col="toolbar-col-12">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>
        </form>
    </x-filter.card>

    <div class="card">
        <div class="card-header">Daftar Jadwal</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Ekstrakurikuler</th>
                        <th>Pembina</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Lokasi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->extracurricular->name ?? '-' }}</td>
                            <td>{{ $schedule->coach->user->name ?? '-' }}</td>
                            <td>{{ $schedule->title }}</td>
                            <td>{{ optional($schedule->activity_date)->format('d-m-Y') }}</td>
                            <td>{{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }}</td>
                            <td>{{ $schedule->location ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><div class="empty-state"><div class="icon"><i class="bi bi-calendar3"></i></div><p class="mb-0">Belum ada data jadwal.</p></div></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($schedules as $schedule)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <div>
                                <h3 class="mobile-data-card-title">{{ $schedule->title }}</h3>
                                <div class="small text-muted">{{ $schedule->extracurricular->name ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Pembina</span><p class="mobile-data-item-value">{{ $schedule->extracurricular->coach_names }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($schedule->activity_date)->format('d-m-Y') }}</p></div>
                            <div><span class="mobile-data-item-label">Lokasi</span><p class="mobile-data-item-value">{{ $schedule->location ?: '-' }}</p></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state"><div class="icon"><i class="bi bi-calendar-event"></i></div><p class="mb-0">Belum ada data jadwal.</p></div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $schedules->links() }}</div>
    </div>
@endsection
