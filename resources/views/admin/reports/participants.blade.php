@extends('layouts.app')

@section('page_title', 'Laporan Peserta Ekstrakurikuler')
@section('page_subtitle', 'Rekap peserta yang sudah disetujui berdasarkan filter yang dipilih')

@section('content')
    @php
        $activeFilters = [
            ['label' => 'Ekskul', 'value' => data_get($extracurriculars->firstWhere('id', $extracurricularId), 'name')],
            ['label' => 'Pembina', 'value' => data_get($coaches->firstWhere('id', $coachId), 'user.name')],
            ['label' => 'Mulai', 'value' => $dateFrom ?: null],
            ['label' => 'Selesai', 'value' => $dateTo ?: null],
        ];
    @endphp

    <x-filter.card class="mb-3" title="Filter Laporan Peserta" description="Saring peserta berdasarkan ekstrakurikuler, pembina, dan periode pendaftaran.">
        <x-slot:actions>
            <x-filter.export-dropdown
                :items="[
                    ['label' => 'Unduh CSV', 'href' => route('admin.reports.export', array_merge(request()->query(), ['type' => 'participants', 'format' => 'csv'])), 'icon' => 'bi-download'],
                    ['label' => 'Unduh Excel', 'href' => route('admin.reports.export', array_merge(request()->query(), ['type' => 'participants', 'format' => 'xls'])), 'icon' => 'bi-file-earmark-excel'],
                ]"
            />
        </x-slot:actions>
        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route('admin.participants.index')" />
        </x-slot:active>
        <form class="toolbar-grid" method="get">
            <x-filter.field label="Ekstrakurikuler" for="participant_report_extracurricular" col="toolbar-col-4">
                <select id="participant_report_extracurricular" name="extracurricular_id" class="form-select">
                    <option value="">Semua ekskul</option>
                    @foreach($extracurriculars as $item)
                        <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Pembina" for="participant_report_coach" col="toolbar-col-4">
                <select id="participant_report_coach" name="coach_id" class="form-select">
                    <option value="">Semua pembina</option>
                    @foreach($coaches as $item)
                        <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Tanggal mulai" for="participant_report_date_from" col="toolbar-col-2">
                <input id="participant_report_date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
            </x-filter.field>
            <x-filter.field label="Tanggal selesai" for="participant_report_date_to" col="toolbar-col-2">
                <input id="participant_report_date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
            </x-filter.field>
            <x-filter.actions col="toolbar-col-12">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                <a href="{{ route('admin.participants.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>
        </form>
    </x-filter.card>

    <div class="card">
        <div class="card-header">Daftar Peserta</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
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
            <div class="mobile-stack-table p-3">
                @forelse($participants as $participant)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <div>
                                <h3 class="mobile-data-card-title">{{ $participant->student->user->name ?? '-' }}</h3>
                                <div class="small text-muted">{{ $participant->extracurricular->name ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">NIS</span><p class="mobile-data-item-value">{{ $participant->student->nis ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Pembina</span><p class="mobile-data-item-value">{{ $participant->extracurricular->coach_names }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal daftar</span><p class="mobile-data-item-value">{{ optional($participant->registration_date)->format('d-m-Y') }}</p></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state"><div class="icon"><i class="bi bi-card-checklist"></i></div><p class="mb-0">Belum ada data peserta.</p></div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $participants->links() }}</div>
    </div>
@endsection
