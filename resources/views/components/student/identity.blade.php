@props([
    'student',
    'href' => null,
    'subtitle' => null,
])

@php
    $name = $student->user?->name ?: 'Nama siswa belum diisi';
    $initial = mb_strtoupper(mb_substr($name, 0, 1));
@endphp

<div {{ $attributes->class(['student-identity']) }}>
    <span class="student-identity__avatar" aria-hidden="true">{{ $initial }}</span>
    <span class="student-identity__copy">
        @if($href)
            <a class="student-identity__name" href="{{ $href }}" title="{{ $name }}">{{ $name }}</a>
        @else
            <strong class="student-identity__name" title="{{ $name }}">{{ $name }}</strong>
        @endif
        @if($subtitle)
            <small>{{ $subtitle }}</small>
        @endif
    </span>
</div>
