@php
    $cardClass = $summary['tone'] ?? 'is-extracurricular';
    $variant = $variant ?? 'media';
    $label = $summary['display_label'] ?? $summary['label'];
    $hasImage = (bool) ($summary['has_image'] ?? !empty($summary['image_url'] ?? $summary['image'] ?? null));
    $imageUrl = $summary['image_url'] ?? $summary['image'] ?? null;
    $countLabel = $summary['count_label'] ?? (($summary['count'] ?? 0).' kegiatan');
@endphp

@if($variant === 'media')
    <article class="activities-hub-card {{ $cardClass }}">
        <div class="activities-hub-card-media {{ $hasImage ? '' : 'has-image-fallback' }}" data-card-media>
            @if($hasImage)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $label }}"
                    width="960"
                    height="540"
                    loading="lazy"
                    decoding="async"
                    data-fallback-image
                >
            @endif
            <div class="activities-hub-card-overlay" aria-hidden="true"></div>
            <div class="activities-hub-card-fallback" aria-hidden="{{ $hasImage ? 'true' : 'false' }}">
                <span class="activities-hub-card-fallback-icon"><i class="bi {{ $summary['icon'] }}"></i></span>
                <span class="activities-hub-card-fallback-text">{{ $label }}</span>
            </div>
        </div>
        <div class="activities-hub-card-body">
            <div class="activities-hub-card-top">
                <span class="activities-hub-card-icon"><i class="bi {{ $summary['icon'] }}"></i></span>
                <span class="activities-hub-card-count"><i class="bi bi-grid-1x2"></i>{{ $countLabel }}</span>
            </div>
            <h3>{{ $label }}</h3>
            <p>{{ $summary['description'] }}</p>
            <a href="{{ $summary['route'] }}" class="btn btn-outline-primary"><i class="bi bi-arrow-right-circle"></i>Lihat Kategori</a>
        </div>
    </article>
@else
    <a href="{{ $summary['route'] }}" class="category-premium-card {{ $cardClass }}">
        <div class="category-premium-top">
            <span class="category-premium-icon"><i class="bi {{ $summary['icon'] }}"></i></span>
            <span class="category-premium-label"><i class="bi bi-grid-1x2"></i>{{ $countLabel }}</span>
        </div>
        <h3>{{ $label }}</h3>
        <p>{{ $summary['description'] }}</p>
        <span class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-right-circle"></i>Lihat Kategori</span>
    </a>
@endif
