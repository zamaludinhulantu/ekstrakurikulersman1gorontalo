@foreach($menuGroups as $group)
    <span class="sidebar-group-label">{{ $group['label'] }}</span>
    @foreach($group['items'] as $item)
        @php
            $isActive = request()->routeIs(...(array) $item['active']);
        @endphp
        <a href="{{ $item['route'] }}"
           class="side-link {{ $isActive ? 'active' : '' }}">
            <span class="side-link-icon"><i class="bi {{ $item['icon'] }}"></i></span>
            <span class="side-link-content">
                <span class="side-link-label">{{ $item['label'] }}</span>
                @if(!empty($item['caption']))
                    <span class="side-link-caption">{{ $item['caption'] }}</span>
                @endif
            </span>
        </a>
    @endforeach
@endforeach
