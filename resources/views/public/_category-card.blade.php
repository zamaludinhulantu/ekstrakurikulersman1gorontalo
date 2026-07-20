@php
    $cardClass = $summary['tone'] ?? 'is-extracurricular';
    $variant = $variant ?? 'media';
@endphp

@if($variant === 'media')
    <article class="activities-hub-card {{ $cardClass }}">
        <div class="activities-hub-card-media">
            <img src="{{ $summary['image'] }}" alt="{{ $summary['label'] }}" width="960" height="540" loading="lazy" decoding="async">
            <div class="activities-hub-card-overlay" aria-hidden="true"></div>
        </div>
        <div class="activities-hub-card-body">
            <div class="activities-hub-card-top">
                <span class="activities-hub-card-icon"><i class="bi {{ $summary['icon'] }}"></i></span>
                <span class="activities-hub-card-count"><i class="bi bi-grid-1x2"></i>{{ $summary['count'] }} kegiatan</span>
            </div>
            <h2>{{ $summary['label'] }}</h2>
            <p>{{ $summary['description'] }}</p>
            <a href="{{ $summary['route'] }}" class="btn btn-outline-primary"><i class="bi bi-arrow-right-circle"></i>Lihat Kategori</a>
        </div>
    </article>
@else
    <a href="{{ $summary['route'] }}" class="category-premium-card {{ $cardClass }}">
        <div class="category-premium-top">
            <span class="category-premium-icon"><i class="bi {{ $summary['icon'] }}"></i></span>
            <span class="category-premium-label"><i class="bi bi-grid-1x2"></i>{{ $summary['count'] }} kegiatan</span>
        </div>
        <h3>{{ $summary['label'] }}</h3>
        <p>{{ $summary['description'] }}</p>
        <span class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-right-circle"></i>Lihat Kategori</span>
    </a>
@endif
