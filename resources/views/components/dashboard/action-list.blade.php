@props([
    'items' => [],
    'emptyMessage' => 'Tidak ada pekerjaan yang memerlukan tindakan saat ini.',
])

<div class="dashboard-action-list">
    @forelse($items as $item)
        <a href="{{ $item['href'] }}" class="dashboard-action-item">
            <span class="dashboard-action-item__icon is-{{ $item['tone'] ?? 'primary' }}">
                <i class="bi {{ $item['icon'] ?? 'bi-check2-circle' }}"></i>
            </span>
            <span class="dashboard-action-item__copy">
                <strong>{{ $item['label'] }}</strong>
                <small>{{ $item['description'] }}</small>
            </span>
            <span class="dashboard-action-item__count">{{ $item['count'] }}</span>
            <i class="bi bi-chevron-right dashboard-action-item__arrow" aria-hidden="true"></i>
        </a>
    @empty
        <div class="empty-state py-4">
            <div class="icon"><i class="bi bi-check2-circle"></i></div>
            <p class="mb-0">{{ $emptyMessage }}</p>
        </div>
    @endforelse
</div>
