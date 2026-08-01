@extends('layouts.app')

@section('page_title', 'Pengaturan PWA dan Notifikasi')
@section('page_subtitle', 'Aktifkan notifikasi perangkat dengan satu tombol, lalu atur kategori notifikasi sesuai kebutuhan.')

@push('styles')
    <style>
        .notification-settings-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: nowrap;
            margin-bottom: 0;
        }

        .notification-settings-toolbar__title {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
        }

        .notification-settings-toolbar__control {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.35rem;
            justify-content: flex-end;
            flex: 0 0 auto;
        }

        .notification-settings-toggle {
            position: relative;
            width: 72px;
            height: 30px;
            padding: 0;
            border: 1px solid #d9dee7;
            border-radius: 999px;
            background: linear-gradient(180deg, #f8f9fb 0%, #eef1f5 100%);
            box-shadow: inset 0 1px 2px rgba(16, 35, 63, 0.12);
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .notification-settings-toggle:hover {
            border-color: #b8c7d8;
        }

        .notification-settings-toggle__label {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: rgba(16, 35, 63, 0.28);
            line-height: 1;
            pointer-events: none;
            transition: color 0.2s ease, opacity 0.2s ease;
        }

        .notification-settings-toggle__label--on {
            left: 11px;
            opacity: 0;
        }

        .notification-settings-toggle__label--off {
            right: 9px;
            opacity: 1;
        }

        .notification-settings-toggle__thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 3px 8px rgba(16, 35, 63, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .notification-settings-toggle.is-enabled {
            background: linear-gradient(180deg, #4fd067 0%, #2fc14d 100%);
            border-color: rgba(31, 137, 54, 0.5);
            box-shadow: inset 0 1px 2px rgba(12, 92, 34, 0.22);
        }

        .notification-settings-toggle.is-enabled .notification-settings-toggle__label--on {
            color: rgba(255, 255, 255, 0.92);
            opacity: 1;
        }

        .notification-settings-toggle.is-enabled .notification-settings-toggle__label--off {
            opacity: 0;
        }

        .notification-settings-toggle.is-enabled .notification-settings-toggle__thumb {
            transform: translateX(42px);
        }

        .notification-settings-toggle:not(.is-enabled) .notification-settings-toggle__label--off {
            color: rgba(16, 35, 63, 0.32);
        }

        .notification-settings-toolbar__status {
            font-size: 0.78rem;
            color: var(--ui-muted);
            margin: 0;
            text-align: right;
            order: 2;
        }

        .notification-settings-toolbar__status.is-active {
            color: var(--ui-success);
            font-weight: 600;
        }

        .notification-settings-help {
            margin-bottom: 0.75rem;
        }

        @media (max-width: 767.98px) {
            .notification-settings-toolbar {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .notification-settings-toolbar__control {
                width: 100%;
                align-items: flex-start;
            }

            .notification-settings-toolbar__status {
                text-align: left;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card" data-pwa-settings-root data-vapid-public-key="{{ config('services.webpush.public_key') }}">
        <div class="card-header">
            <div class="notification-settings-toolbar">
                <h2 class="notification-settings-toolbar__title">Kategori Notifikasi</h2>
                <div class="notification-settings-toolbar__control">
                    <p class="notification-settings-toolbar__status" id="settingsTogglePushStatus">Memeriksa status notifikasi...</p>
                    <button
                        type="button"
                        class="notification-settings-toggle"
                        id="settingsTogglePushButton"
                        data-state="unknown"
                        aria-label="Aktifkan notifikasi"
                        aria-pressed="false"
                        title="Aktifkan notifikasi"
                    >
                        <span class="visually-hidden" id="settingsTogglePushText">Aktifkan notifikasi</span>
                        <span class="notification-settings-toggle__label notification-settings-toggle__label--on" aria-hidden="true">ON</span>
                        <span class="notification-settings-toggle__label notification-settings-toggle__label--off" aria-hidden="true">OFF</span>
                        <span class="notification-settings-toggle__thumb" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="small text-muted notification-settings-help" id="pwaPushHelpText">
                Aktifkan notifikasi lalu pilih kategori yang ingin diterima.
            </div>
            <div class="d-none">
                <strong id="pwaInstallState">Memeriksa...</strong>
                <strong id="pwaConnectionState">Online</strong>
                <strong id="pwaPushState">Memeriksa...</strong>
            </div>

            <form method="post" action="{{ route('settings.pwa-notifications.update') }}">
                @csrf
                @method('put')
                <p class="text-muted small mb-3">
                    Kategori ini menentukan jenis notifikasi yang diizinkan setelah notifikasi perangkat diaktifkan.
                </p>
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
@endsection
