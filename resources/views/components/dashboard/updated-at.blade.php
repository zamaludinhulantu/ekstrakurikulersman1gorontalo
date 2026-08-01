@props(['value'])

<p {{ $attributes->class(['dashboard-updated-at']) }}>
    <i class="bi bi-clock-history" aria-hidden="true"></i>
    Terakhir diperbarui: {{ $value->translatedFormat('d F Y, H:i') }}
</p>
