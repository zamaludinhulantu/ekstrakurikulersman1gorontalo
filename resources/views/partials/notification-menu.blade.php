<div class="dropdown notification-dropdown">
    <button class="btn btn-outline-primary btn-sm notification-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka notifikasi">
        <i class="bi bi-bell"></i>
        @if($unreadNotificationCount > 0)
            <span class="notification-badge">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end notification-menu">
        <div class="notification-menu__header">
            <div>
                <strong>Notifikasi</strong>
                <div class="small text-muted">Pusat notifikasi aplikasi</div>
            </div>
            @if($unreadNotificationCount > 0)
                <form method="post" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0">Tandai semua</button>
                </form>
            @endif
        </div>
        <div class="notification-menu__body">
            @forelse($recentNotifications as $item)
                <a href="{{ route('notifications.open', $item->id) }}" class="notification-item {{ $item->read_at ? '' : 'is-unread' }}">
                    <span class="notification-item__icon"><i class="bi {{ data_get($item->data, 'icon', 'bi-bell') }}"></i></span>
                    <span class="notification-item__content">
                        <strong>{{ data_get($item->data, 'title', 'Pemberitahuan') }}</strong>
                        <span>{{ data_get($item->data, 'message', '') }}</span>
                        <small>{{ $item->created_at?->diffForHumans() }}</small>
                    </span>
                </a>
            @empty
                <div class="notification-empty-state">
                    <i class="bi bi-bell-slash"></i>
                    <p class="mb-0">Belum ada notifikasi.</p>
                </div>
            @endforelse
        </div>
        <div class="notification-menu__footer">
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary btn-sm w-100">Lihat semua notifikasi</a>
        </div>
    </div>
</div>
