@extends('layouts.app')

@section('page_title', 'Status Pendaftaran')
@section('page_subtitle', 'Pantau hasil verifikasi pendaftaran ekstrakurikuler')

@section('content')
    <div class="card">
        <div class="card-header">Riwayat Pendaftaran</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Ekstrakurikuler</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($registrations as $row)
                        <tr>
                            <td>{{ $row->extracurricular->name ?? '-' }}</td>
                            <td>{{ optional($row->registration_date)->format('d-m-Y') }}</td>
                            <td><span class="badge" data-status="{{ $row->status }}">{{ $row->status }}</span></td>
                            <td>{{ $row->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-inbox"></i></div>
                                    <p class="mb-0">Belum ada pendaftaran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($registrations as $row)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <h3 class="mobile-data-card-title">{{ $row->extracurricular->name ?? '-' }}</h3>
                            <span class="badge" data-status="{{ $row->status }}">{{ $row->status }}</span>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Tanggal daftar</span><p class="mobile-data-item-value">{{ optional($row->registration_date)->format('d-m-Y') }}</p></div>
                            <div><span class="mobile-data-item-label">Catatan</span><p class="mobile-data-item-value">{{ $row->notes ?? '-' }}</p></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Belum ada pendaftaran.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $registrations->links() }}</div>
    </div>
@endsection
