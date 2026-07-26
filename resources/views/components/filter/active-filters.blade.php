@props([
    'items' => [],
    'resetUrl' => null,
])

@php
    $items = collect($items)->filter(fn ($item) => filled($item['value'] ?? null))->values();
@endphp

@if($items->isNotEmpty())
    <div {{ $attributes->class(['active-filter-bar']) }}>
        <div class="active-filter-bar__chips">
            @foreach($items as $item)
                <span class="active-filter-chip">
                    <strong>{{ $item['label'] }}:</strong> {{ $item['value'] }}
                </span>
            @endforeach
        </div>
        @if($resetUrl)
            <a href="{{ $resetUrl }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle"></i>Hapus semua filter
            </a>
        @endif
    </div>
@endif
