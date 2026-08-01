@props([
    'column',
    'label',
    'currentSort',
    'direction',
])

@php
    $active = $currentSort === $column;
    $nextDirection = $active && $direction === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery([
        'sort' => $column,
        'direction' => $nextDirection,
        'page' => null,
    ]);
@endphp

<a
    href="{{ $url }}"
    class="student-sort-link {{ $active ? 'is-active' : '' }}"
    aria-label="Urutkan berdasarkan {{ $label }} {{ $nextDirection === 'asc' ? 'menaik' : 'menurun' }}"
>
    <span>{{ $label }}</span>
    <i class="bi {{ $active ? ($direction === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i>
</a>
