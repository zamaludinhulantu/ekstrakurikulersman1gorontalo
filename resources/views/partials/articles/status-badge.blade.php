@php
    $statusKey = match ($article->display_status) {
        'Dipublikasikan' => 'published',
        'Dijadwalkan' => 'scheduled',
        'Diarsipkan' => 'archived',
        'Berakhir' => 'expired',
        'Dinonaktifkan' => 'inactive',
        default => 'draft',
    };
@endphp

<span class="article-management-status" data-status="{{ $statusKey }}">{{ $article->display_status }}</span>
