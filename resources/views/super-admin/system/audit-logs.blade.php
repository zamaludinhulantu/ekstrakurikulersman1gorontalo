@extends('layouts.app')

@section('page_title', 'Audit Log Sistem')
@section('page_subtitle', 'Pantau jejak aksi sensitif yang dilakukan oleh super admin.')

@section('content')
    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <form class="toolbar-grid">
                <div class="toolbar-col-6">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari aksi, deskripsi, atau nama pengguna">
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i>Cari</button>
                </div>
                <div class="toolbar-col-2">
                    <a href="{{ route('super-admin.audit-logs.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

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
                        <td>{{ optional($log->created_at)->format('d-m-Y H:i') }}</td>
                        <td>{{ $log->user->name ?? 'Sistem' }}</td>
                        <td><span class="badge badge-status-secondary">{{ $log->action }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $log->description ?: '-' }}</div>
                            @if(!empty($log->metadata))
                                <div class="small text-muted mt-1">{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE) }}</div>
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
                        <span class="badge badge-status-secondary">{{ optional($log->created_at)->format('d-m-Y H:i') }}</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">Pengguna</span><p class="mobile-data-item-value">{{ $log->user->name ?? 'Sistem' }}</p></div>
                        <div><span class="mobile-data-item-label">Deskripsi</span><p class="mobile-data-item-value">{{ $log->description ?: '-' }}</p></div>
                        <div><span class="mobile-data-item-label">IP</span><p class="mobile-data-item-value">{{ $log->ip_address ?? '-' }}</p></div>
                        @if(!empty($log->metadata))
                            <div><span class="mobile-data-item-label">Metadata</span><p class="mobile-data-item-value">{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE) }}</p></div>
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
