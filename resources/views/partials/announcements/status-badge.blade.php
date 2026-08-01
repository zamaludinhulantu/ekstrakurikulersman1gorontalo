@php
    $statusKey = match ($announcement->display_status) {
        'Dipublikasikan' => 'published',
        'Terjadwal' => 'scheduled',
        'Draft' => 'draft',
        'Berakhir' => 'expired',
        default => 'inactive',
    };
@endphp

<span class="announcement-status" data-status="{{ $statusKey }}">
    {{ $announcement->display_status }}
</span>
