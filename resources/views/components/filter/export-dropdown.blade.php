@props([
    'label' => 'Unduh Data',
    'items' => [],
    'buttonClass' => 'btn btn-outline-primary',
    'align' => 'end',
])

@if(count($items))
    <div class="dropdown">
        <button class="{{ $buttonClass }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-download"></i>{{ $label }}
        </button>
        <ul class="dropdown-menu dropdown-menu-{{ $align }} dropdown-menu-compact">
            @foreach($items as $item)
                <li>
                    <a class="dropdown-item" href="{{ $item['href'] ?? '#' }}" @if(!empty($item['target'])) target="{{ $item['target'] }}" rel="noopener" @endif>
                        <i class="bi {{ $item['icon'] ?? 'bi-download' }} me-2"></i>{{ $item['label'] ?? 'Unduh' }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
