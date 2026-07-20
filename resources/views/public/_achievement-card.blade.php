@php
    $achievementActivity = $achievement->extracurricular;
    $achievementName = $achievementActivity?->catalog_item_name ?? $achievementActivity?->name ?? 'Kegiatan';
    $achievementGroup = $achievementActivity?->catalog_group_label ?? 'Prestasi';
    $achievementLevel = str_contains(\Illuminate\Support\Str::upper($achievement->title), 'NASIONAL') ? 'Nasional' : 'Prestasi Siswa';
@endphp

<article class="public-achievement-card">
    <div class="public-achievement-card-media">
        @if($achievementActivity?->preview_image)
            <img src="{{ $achievementActivity->preview_image }}" alt="{{ $achievementName }}" width="640" height="360" loading="lazy" decoding="async">
        @else
            <div class="public-achievement-card-fallback" aria-hidden="true">
                <span><i class="bi bi-award"></i></span>
            </div>
        @endif
    </div>
    <div class="public-achievement-card-body">
        <div class="public-achievement-card-meta">
            <span><i class="bi bi-stars"></i>{{ $achievementGroup }}</span>
            <span><i class="bi bi-trophy"></i>{{ $achievementLevel }}</span>
            <span><i class="bi bi-calendar4"></i>{{ optional($achievement->achievement_date)->format('Y') ?: '-' }}</span>
        </div>
        <h3>{{ $achievement->title }}</h3>
        <p class="public-achievement-card-activity">{{ $achievementName }}</p>
        <p class="public-achievement-card-description">{{ \Illuminate\Support\Str::limit($achievement->description ?: 'Prestasi terbaru yang dicatat pada sistem.', 120) }}</p>
    </div>
</article>
