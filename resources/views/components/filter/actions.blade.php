@props([
    'col' => 'toolbar-col-12',
    'stacked' => false,
])

<div {{ $attributes->class([$col, 'filter-actions-wrap']) }}>
    <div class="filter-actions {{ $stacked ? 'filter-actions--stacked' : '' }}">
        {{ $slot }}
    </div>
</div>
