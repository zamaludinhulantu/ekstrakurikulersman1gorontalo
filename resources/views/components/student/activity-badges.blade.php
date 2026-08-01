@props([
    'activities',
    'routeName' => null,
    'limit' => 2,
])

@php
    $items = collect($activities)->filter()->unique('id')->values();
    $visibleItems = $items->take($limit);
    $remaining = max(0, $items->count() - $visibleItems->count());
    $allNames = $items->pluck('name')->implode(', ');
@endphp

<div {{ $attributes->class(['student-activity-list']) }} @if($allNames) title="{{ $allNames }}" @endif>
    @forelse($visibleItems as $activity)
        @if($routeName)
            <a href="{{ route($routeName, $activity) }}" class="student-activity-link">{{ $activity->name }}</a>
        @else
            <span class="student-activity-link">{{ $activity->name }}</span>
        @endif
    @empty
        <span class="student-activity-empty">Belum mengikuti kegiatan</span>
    @endforelse

    @if($remaining > 0)
        <span class="student-activity-more">+{{ $remaining }} lainnya</span>
    @endif
</div>
