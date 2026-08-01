@props(['coach', 'limit' => null])

@php
    $activities = $coach->extracurriculars;
    $visibleActivities = $limit === null ? $activities : $activities->take($limit);
    $remaining = max(0, $activities->count() - $visibleActivities->count());
@endphp

<div {{ $attributes->class(['student-activity-list', 'coach-activity-list']) }}>
    @forelse($visibleActivities as $activity)
        <a
            href="{{ route('admin.extracurriculars.show', $activity) }}"
            class="student-activity-link coach-activity-link"
            title="{{ $activity->name }}"
        >{{ $activity->name }}</a>
    @empty
        <span class="coach-assignment-empty"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>Belum ditugaskan</span>
    @endforelse

    @if($remaining > 0)
        <span class="student-activity-more" title="{{ $activities->pluck('name')->implode(', ') }}">+{{ $remaining }} lainnya</span>
    @endif
</div>
