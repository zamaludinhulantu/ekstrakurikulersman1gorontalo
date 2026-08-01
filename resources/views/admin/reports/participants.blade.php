@extends('layouts.app')

@section('page_title', 'Laporan Peserta Ekstrakurikuler')
@section('page_subtitle', 'Rekap keanggotaan yang sudah diterima berdasarkan filter')

@section('content')
    @php
        $hasAdvancedFilters = filled($dateFrom) || filled($dateTo) || (int) $perPage !== 20;
        $activeFilters = [
            ['label' => 'Cari', 'value' => $search ?: null],
            ['label' => 'Kelas', 'value' => $className ?: null],
            ['label' => 'Ekskul', 'value' => data_get($extracurriculars->firstWhere('id', $extracurricularId), 'name')],
            ['label' => 'Pembina', 'value' => data_get($coaches->firstWhere('id', $coachId), 'user.name')],
            ['label' => 'Mulai', 'value' => $dateFrom ?: null],
            ['label' => 'Selesai', 'value' => $dateTo ?: null],
        ];
    @endphp

    <x-filter.card class="mb-3" title="Filter Laporan Peserta" description="Pencarian dan unduhan mengikuti keanggotaan berstatus diterima.">
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
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <x-filter.field label="Pencarian" for="participant_report_search" col="toolbar-col-3">
                <input id="participant_report_search" name="search" type="search" value="{{ $search }}" class="form-control" placeholder="Nama, NIS, email, atau ekskul">
            </x-filter.field>
            <x-filter.field label="Kelas" for="participant_report_class" col="toolbar-col-2">
                <select id="participant_report_class" name="class_name" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classOptions as $option)
                        <option value="{{ $option }}" @selected($className === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Ekstrakurikuler" for="participant_report_extracurricular" col="toolbar-col-3">
                <select id="participant_report_extracurricular" name="extracurricular_id" class="form-select">
                    <option value="">Semua ekskul</option>
                    @foreach($extracurriculars as $item)
                        <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Pembina" for="participant_report_coach" col="toolbar-col-2">
                <select id="participant_report_coach" name="coach_id" class="form-select">
                    <option value="">Semua pembina</option>
                    @foreach($coaches as $item)
                        <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.actions col="toolbar-col-2">
                <button class="btn btn-primary" type="submit" aria-label="Terapkan filter"><i class="bi bi-funnel"></i>Terapkan</button>
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#participantReportAdvanced" aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}" aria-controls="participantReportAdvanced" aria-label="Filter lanjutan"><i class="bi bi-sliders"></i></button>
                <a href="{{ route('admin.participants.index') }}" class="btn btn-outline-secondary" aria-label="Reset filter"><i class="bi bi-arrow-repeat"></i></a>
            </x-filter.actions>
            <div id="participantReportAdvanced" class="toolbar-col-12 filter-advanced collapse {{ $hasAdvancedFilters ? 'show' : '' }}">
                <div class="toolbar-grid">
                    <x-filter.field label="Tanggal mulai" for="participant_report_date_from" col="toolbar-col-4">
                        <input id="participant_report_date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
                    </x-filter.field>
                    <x-filter.field label="Tanggal selesai" for="participant_report_date_to" col="toolbar-col-4">
                        <input id="participant_report_date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
                    </x-filter.field>
                    <x-filter.field label="Per halaman" for="participant_report_per_page" col="toolbar-col-4">
                        <select id="participant_report_per_page" name="per_page" class="form-select">
                            @foreach([10, 20, 50, 100] as $option)
                                <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </x-filter.field>
                </div>
            </div>
        </form>
    </x-filter.card>

    <div class="card student-directory-card">
        <div class="card-header dashboard-panel-header">
            <div><strong>Daftar Keanggotaan</strong><small>{{ $participants->total() }} keanggotaan sesuai filter</small></div>
        </div>
        <div class="desktop-table table-responsive">
            <table class="table student-directory-table mb-0">
                <thead>
                <tr>
                    <th class="student-directory-table__name"><x-student.sort-link column="name" label="Siswa" :current-sort="$sort" :direction="$direction" /></th>
                    <th><x-student.sort-link column="nis" label="NIS" :current-sort="$sort" :direction="$direction" /></th>
                    <th><x-student.sort-link column="class_name" label="Kelas" :current-sort="$sort" :direction="$direction" /></th>
                    <th>Ekstrakurikuler</th>
                    <th>Pembina</th>
                    <th><x-student.sort-link column="registration_date" label="Tanggal Gabung" :current-sort="$sort" :direction="$direction" /></th>
                </tr>
                </thead>
                <tbody>
                @forelse($participants as $participant)
                    <tr>
                        <td><x-student.identity :student="$participant->student" :href="route('admin.students.show', $participant->student)" /></td>
                        <td>
                            @if($participant->student->nis)
                                <span class="student-nis">{{ $participant->student->nis }}</span>
                            @else
                                <span class="student-missing-value">NIS belum diisi</span>
                            @endif
                        </td>
                        <td>{{ $participant->student->class_name ?: 'Kelas belum diisi' }}</td>
                        <td>{{ $participant->extracurricular->name ?? '-' }}</td>
                        <td>{{ $participant->extracurricular->coach_names }}</td>
                        <td>{{ optional($participant->registration_date)->format('d-m-Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><div class="icon"><i class="bi bi-card-checklist"></i></div><p class="mb-2">Tidak ada peserta yang sesuai dengan filter.</p><a href="{{ route('admin.participants.index') }}" class="btn btn-outline-primary btn-sm">Reset Filter</a></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mobile-stack-table p-3">
            @forelse($participants as $participant)
                <article class="mobile-data-card student-mobile-card">
                    <div class="mobile-data-card-header">
                        <x-student.identity :student="$participant->student" :subtitle="$participant->student->class_name ?: 'Kelas belum diisi'" />
                        <span class="badge badge-status-success">Diterima</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">NIS</span><p class="mobile-data-item-value {{ $participant->student->nis ? '' : 'student-missing-value' }}">{{ $participant->student->nis ?: 'NIS belum diisi' }}</p></div>
                        <div><span class="mobile-data-item-label">Ekstrakurikuler</span><p class="mobile-data-item-value">{{ $participant->extracurricular->name ?? '-' }}</p></div>
                        <div><span class="mobile-data-item-label">Pembina</span><p class="mobile-data-item-value">{{ $participant->extracurricular->coach_names }}</p></div>
                        <div><span class="mobile-data-item-label">Tanggal gabung</span><p class="mobile-data-item-value">{{ optional($participant->registration_date)->format('d-m-Y') }}</p></div>
                    </div>
                </article>
            @empty
                <div class="empty-state"><div class="icon"><i class="bi bi-card-checklist"></i></div><p class="mb-2">Tidak ada peserta yang sesuai dengan filter.</p><a href="{{ route('admin.participants.index') }}" class="btn btn-outline-primary btn-sm">Reset Filter</a></div>
            @endforelse
        </div>
        <div class="card-body student-directory-card__footer">
            <x-student.pagination :paginator="$participants" noun="keanggotaan" :per-page="$perPage" />
        </div>
    </div>
@endsection
