@props([
    'label',
    'for' => null,
    'col' => 'toolbar-col-3',
])

<div {{ $attributes->class([$col, 'filter-field']) }}>
    <label class="form-label" @if($for) for="{{ $for }}" @endif>{{ $label }}</label>
    {{ $slot }}
</div>
