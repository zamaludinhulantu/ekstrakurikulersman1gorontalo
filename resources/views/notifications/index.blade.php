@extends('layouts.app')

@section('page_title', 'Semua Notifikasi')
@section('page_subtitle', 'Lihat riwayat notifikasi, status baca, dan tautan menuju modul terkait.')

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong>Pusat Notifikasi</strong>
                <div class="small text-muted">Setiap pengguna hanya dapat melihat notifikasinya sendiri.</div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if(($unreadNotificationCount ?? 0) > 0)
                    <form method="post" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">Tandai semua dibaca</button>
                    </form>
                @endif
                <a href="{{ route('settings.pwa-notifications') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-gear"></i>Pengaturan
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="notification-list">
                @forelse($notifications as $item)
                    <div class="notification-list-item {{ $item->read_at ? '' : 'is-unread' }}">
                        <div class="notification-list-item__icon">
                            <i class="bi {{ data_get($item->data, 'icon', 'bi-bell') }}"></i>
                        </div>
                        <div class="notification-list-item__body">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <h3>{{ data_get($item->data, 'title', 'Pemberitahuan') }}</h3>
                                    <p>{{ data_get($item->data, 'message', '') }}</p>
                                </div>
                                <small class="text-muted">{{ $item->created_at?->translatedFormat('d M Y H:i') }}</small>
                            </div>
                            <div class="notification-list-item__meta">
                                <span class="status-badge {{ $item->read_at ? 'badge-status-secondary' : 'badge-status-info' }}">
                                    {{ $item->read_at ? 'Sudah dibaca' : 'Belum dibaca' }}
                                </span>
                                <span class="text-muted">Kategori: {{ str_replace('_', ' ', data_get($item->data, 'category', 'general')) }}</span>
                            </div>
                            <div class="row-actions mt-3">
                                <a href="{{ route('notifications.open', $item->id) }}" class="btn btn-primary btn-sm">Buka detail</a>
                                @if(! $item->read_at)
                                    <form method="post" action="{{ route('notifications.read', $item->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Tandai dibaca</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-bell-slash"></i></div>
                        <p class="mb-0">Belum ada notifikasi yang tersimpan.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@endsection
