@extends('layouts.app')

@section('page_title', 'Pengaturan PWA & Notifikasi')
@section('page_subtitle', 'Kelola status instalasi aplikasi, koneksi, push notification, dan kategori notifikasi perangkat.')

@section('content')
    <div class="row g-3">
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">Status Perangkat</div>
                <div class="card-body settings-status-grid" data-pwa-settings-root data-vapid-public-key="{{ config('services.webpush.public_key') }}">
                    <div class="settings-status-item">
                        <span>Status instalasi</span>
                        <strong id="pwaInstallState">Memeriksa...</strong>
                    </div>
                    <div class="settings-status-item">
                        <span>Status koneksi</span>
                        <strong id="pwaConnectionState">Online</strong>
                    </div>
                    <div class="settings-status-item">
                        <span>Push notification</span>
                        <strong id="pwaPushState">Memeriksa...</strong>
                    </div>
                    <div class="settings-status-item">
                        <span>Versi aplikasi</span>
                        <strong>{{ substr(md5_file(public_path('build/manifest.json')), 0, 8) }}</strong>
                    </div>
                    <div class="d-grid gap-2 mt-2">
                        <button type="button" class="btn btn-primary" id="settingsEnablePushButton">Aktifkan Notifikasi</button>
                        <button type="button" class="btn btn-outline-danger" id="settingsDisablePushButton">Nonaktifkan Notifikasi di Perangkat Ini</button>
                        <button type="button" class="btn btn-outline-danger" id="settingsDisableAllPushButton">Nonaktifkan Notifikasi di Semua Perangkat</button>
                    </div>
                    <div class="small text-muted mt-2" id="pwaPushHelpText">
                        Izin notifikasi baru akan diminta setelah Anda menekan tombol aktivasi.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header">Kategori Push Notification</div>
                <div class="card-body">
                    <form method="post" action="{{ route('settings.pwa-notifications.update') }}">
                        @csrf
                        @method('put')
                        <div class="settings-preference-list">
                            @foreach($categories as $key => $label)
                                <label class="settings-preference-item">
                                    <span>
                                        <strong>{{ $label }}</strong>
                                        <small>Kontrol kategori ini untuk notifikasi perangkat.</small>
                                    </span>
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="push_preferences[{{ $key }}]"
                                        value="1"
                                        @checked(($preference->mergedPushPreferences()[$key] ?? false) === true)
                                    >
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Simpan pengaturan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
