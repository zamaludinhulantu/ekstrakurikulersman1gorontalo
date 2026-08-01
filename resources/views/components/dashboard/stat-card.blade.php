@props([
    'label',
    'value',
    'hint' => null,
    'icon' => 'bi-bar-chart',
    'href' => null,
    'tone' => 'primary',
    'actionLabel' => null,
])

@php
    $hasContent = $slot->isNotEmpty();
    $cardClasses = 'dashboard-stat-card is-' . $tone;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$cardClasses, 'is-link']) }}>
        <span class="dashboard-stat-card__icon"><i class="bi {{ $icon }}"></i></span>
        <span class="dashboard-stat-card__label">{{ $label }}</span>
        <strong class="dashboard-stat-card__value">{{ $value }}</strong>
        @if($hint)<span class="dashboard-stat-card__hint">{{ $hint }}</span>@endif
        @if($actionLabel)<span class="dashboard-stat-card__action">{{ $actionLabel }} <i class="bi bi-arrow-right"></i></span>@endif
        @if($hasContent){{ $slot }}@endif
    </a>
@else
    <div {{ $attributes->class([$cardClasses]) }}>
        <span class="dashboard-stat-card__icon"><i class="bi {{ $icon }}"></i></span>
        <span class="dashboard-stat-card__label">{{ $label }}</span>
        <strong class="dashboard-stat-card__value">{{ $value }}</strong>
        @if($hint)<span class="dashboard-stat-card__hint">{{ $hint }}</span>@endif
        @if($hasContent){{ $slot }}@endif
    </div>
@endif
