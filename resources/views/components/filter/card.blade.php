@props([
    'title',
    'description' => null,
    'advancedId' => null,
    'advancedLabel' => 'Filter Lanjutan',
    'advancedOpen' => false,
    'showAdvancedToggle' => false,
])

<div {{ $attributes->class(['card filter-card']) }}>
    <div class="card-body toolbar-card">
        <div class="filter-header">
            <div class="filter-header__copy">
                <h2 class="filter-header__title">{{ $title }}</h2>
                @if($description)
                    <p class="filter-header__description">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="filter-header__actions">
                    {{ $actions }}
                </div>
            @endisset
        </div>

        @isset($active)
            {{ $active }}
        @endisset

        {{ $slot }}

        @if($showAdvancedToggle && isset($advanced))
            <div class="filter-advanced-toggle">
                <button
                    class="btn btn-outline-secondary btn-sm"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $advancedId }}"
                    aria-expanded="{{ $advancedOpen ? 'true' : 'false' }}"
                    aria-controls="{{ $advancedId }}"
                >
                    <i class="bi bi-sliders"></i>{{ $advancedLabel }}
                </button>
            </div>
            <div id="{{ $advancedId }}" class="filter-advanced collapse {{ $advancedOpen ? 'show' : '' }}">
                {{ $advanced }}
            </div>
        @endif
    </div>
</div>
