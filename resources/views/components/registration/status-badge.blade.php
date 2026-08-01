@props(['registration'])

@php
    $displayStatus = $registration->displayStatus();
    $label = \App\Support\RegistrationStatusPresenter::label($displayStatus);
    $tone = \App\Support\RegistrationStatusPresenter::tone($displayStatus);
@endphp

<span {{ $attributes->class(['registration-status-badge', 'is-' . $tone]) }} data-status="{{ $displayStatus }}">
    <span class="registration-status-badge__dot" aria-hidden="true"></span>
    {{ $label }}
</span>
