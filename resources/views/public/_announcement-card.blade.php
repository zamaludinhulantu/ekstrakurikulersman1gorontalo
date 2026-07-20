@php
    $priorityClass = match ($announcement->priority ?? null) {
        \App\Models\Announcement::PRIORITY_URGENT => 'is-urgent',
        \App\Models\Announcement::PRIORITY_IMPORTANT => 'is-important',
        default => 'is-normal',
    };
    $priorityLabel = $announcement->priority_label ?? 'Biasa';
@endphp

<article class="public-announcement-card">
    <div class="public-announcement-card-meta">
        <span><i class="bi bi-calendar-event"></i>{{ $announcement->created_at?->translatedFormat('d M Y') ?? '-' }}</span>
        <span><i class="bi bi-diagram-3"></i>{{ $announcement->extracurricular?->catalog_item_name ?? $announcement->extracurricular?->name ?? 'Semua kegiatan' }}</span>
        <span class="{{ $priorityClass }}"><i class="bi bi-flag"></i>{{ $priorityLabel }}</span>
    </div>
    <h3>{{ $announcement->title }}</h3>
    <p>{{ \Illuminate\Support\Str::limit($announcement->content, 140) }}</p>
    <div class="public-announcement-card-footer">
        <span>Dipublikasikan oleh {{ $announcement->publisher?->name ?? 'Admin' }}</span>
        <a href="{{ route('public.announcements') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-right-circle"></i>Baca Selengkapnya</a>
    </div>
</article>
