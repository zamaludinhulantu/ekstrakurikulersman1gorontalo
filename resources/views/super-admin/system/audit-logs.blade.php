@extends('layouts.app')

@section('page_title', 'Audit Log Sistem')
@section('page_subtitle', 'Pantau aktivitas penting akun seperti verifikasi pendaftaran, perubahan sistem, dan aksi operasional lain.')

@section('content')
    @php
        $activeFilters = [
            ['label' => 'Cari', 'value' => $search ?: null],
        ];
    @endphp

    <x-filter.card class="mb-3" title="Filter Audit Log" description="Cari aktivitas akun berdasarkan kata kunci pengguna, aksi, atau deskripsi.">
        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route('super-admin.audit-logs.index')" />
        </x-slot:active>

        <form class="toolbar-grid">
            <x-filter.field label="Pencarian" for="audit_log_search" col="toolbar-col-8">
                <input id="audit_log_search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari aksi, deskripsi, atau nama pengguna">
            </x-filter.field>
            <x-filter.actions col="toolbar-col-4">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i>Cari Data</button>
                <a href="{{ route('super-admin.audit-logs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>
        </form>
    </x-filter.card>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center gap-2">
            <span>Riwayat Aktivitas Sensitif</span>
            <span class="small text-muted">{{ $logs->total() }} log</span>
        </div>
        <div class="desktop-table table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Deskripsi</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ optional($log->created_at)->format('d-m-Y') }}</div>
                            <div class="small text-muted">{{ optional($log->created_at)->format('H:i:s') }}</div>
                        </td>
                        <td>{{ $log->user->name ?? 'Sistem' }}</td>
                        <td><span class="badge badge-status-secondary">{{ $log->action }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $log->description ?: '-' }}</div>
                            @if(!empty($log->metadata))
                                @php($details = [
                                    'Peran' => $log->metadata['actor_role'] ?? null,
                                    'Siswa' => $log->metadata['student_name'] ?? null,
                                    'Kegiatan' => $log->metadata['extracurricular_name'] ?? null,
                                    'Keputusan' => $log->metadata['decision'] ?? null,
                                    'Status awal' => $log->metadata['previous_status'] ?? null,
                                    'Status akhir' => $log->metadata['current_status'] ?? null,
                                    'Waktu verifikasi' => $log->metadata['verified_at'] ?? null,
                                ])
                                <div class="small text-muted mt-1">
                                    {{ collect($details)->filter(fn ($value) => filled($value))->map(fn ($value, $label) => $label.': '.$value)->implode(' | ') }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-journal-x"></i></div>
                                <p class="mb-0">Belum ada audit log.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mobile-stack-table p-3">
            @forelse($logs as $log)
                <div class="mobile-data-card">
                    <div class="mobile-data-card-header">
                        <h3 class="mobile-data-card-title">{{ $log->action }}</h3>
                        <span class="badge badge-status-secondary">{{ optional($log->created_at)->format('d-m-Y H:i:s') }}</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">Pengguna</span><p class="mobile-data-item-value">{{ $log->user->name ?? 'Sistem' }}</p></div>
                        <div><span class="mobile-data-item-label">Deskripsi</span><p class="mobile-data-item-value">{{ $log->description ?: '-' }}</p></div>
                        <div><span class="mobile-data-item-label">IP</span><p class="mobile-data-item-value">{{ $log->ip_address ?? '-' }}</p></div>
                        @if(!empty($log->metadata))
                            <div><span class="mobile-data-item-label">Peran</span><p class="mobile-data-item-value">{{ $log->metadata['actor_role'] ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Siswa</span><p class="mobile-data-item-value">{{ $log->metadata['student_name'] ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Kegiatan</span><p class="mobile-data-item-value">{{ $log->metadata['extracurricular_name'] ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Keputusan</span><p class="mobile-data-item-value">{{ $log->metadata['decision'] ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Status awal</span><p class="mobile-data-item-value">{{ $log->metadata['previous_status'] ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Status akhir</span><p class="mobile-data-item-value">{{ $log->metadata['current_status'] ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Waktu verifikasi</span><p class="mobile-data-item-value">{{ $log->metadata['verified_at'] ?? '-' }}</p></div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-journal-x"></i></div>
                    <p class="mb-0">Belum ada audit log.</p>
                </div>
            @endforelse
        </div>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
@endsection
