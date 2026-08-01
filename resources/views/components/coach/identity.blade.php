@props(['coach'])

@php
    $name = $coach->user?->name ?: 'Nama belum tersedia';
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<div {{ $attributes->class(['student-identity']) }}>
    <span class="student-identity__avatar" aria-hidden="true">{{ $initials ?: 'P' }}</span>
    <span class="student-identity__copy">
        <a href="{{ route('admin.coaches.show', $coach) }}" class="student-identity__name">{{ $name }}</a>
        @unless($coach->hasCompleteProfile())
            <small>Profil belum lengkap</small>
        @endunless
    </span>
</div>
