@extends('layouts.app')

@section('page_title', 'Maintenance Sistem')
@section('page_subtitle', 'Aktifkan mode pemeliharaan internal tanpa mengunci akses super admin.')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header">Status Saat Ini</div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="title">Mode Maintenance</div>
                            <div class="small text-muted mt-1">
                                {{ $isMaintenanceEnabled ? 'Aktif. Pengguna selain super admin akan melihat halaman pemeliharaan.' : 'Tidak aktif. Aplikasi berjalan normal.' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="title">Driver Maintenance Laravel</div>
                            <div class="small text-muted mt-1">{{ $maintenanceDriver ?: 'file' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="title">Catatan Penting</div>
                            <div class="small text-muted mt-1">Mode ini berbasis database setting, bukan `php artisan down`, jadi super admin tetap dapat masuk.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header">Atur Maintenance</div>
                <div class="card-body">
                    <form method="post" action="{{ route('super-admin.maintenance.update') }}" class="row g-3">
                        @csrf
                        @method('put')

                        <div class="col-12">
                            <div class="data-point h-100 d-flex align-items-center">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" name="maintenance_enabled" value="1" id="maintenance_enabled" @checked(old('maintenance_enabled', $isMaintenanceEnabled))>
                                    <label class="form-check-label fw-semibold" for="maintenance_enabled">Aktifkan maintenance mode</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="maintenance_message">Pesan Pemeliharaan</label>
                            <textarea id="maintenance_message" name="maintenance_message" class="form-control" rows="4" required>{{ old('maintenance_message', $maintenanceMessage) }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-actions mt-2">
                                <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan status...">
                                    <i class="bi bi-save"></i>Simpan Status Maintenance
                                </button>
                                <a href="{{ route('super-admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-journal-text"></i>Lihat Audit Log
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
