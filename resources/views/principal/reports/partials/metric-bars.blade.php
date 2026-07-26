@php
    $rows = collect($rows ?? []);
    $maxValue = max((float) $rows->max('value'), 1);
    $suffix = $suffix ?? '';
@endphp

@if($rows->count())
    <div class="principal-chart-list">
        @foreach($rows as $row)
            @php
                $value = (float) ($row['value'] ?? 0);
                $width = min(100, max(0, $maxValue > 0 ? ($value / $maxValue) * 100 : 0));
            @endphp
            <div class="principal-chart-list__row">
                <div class="principal-chart-list__meta">
                    <span>{{ $row['label'] ?? '-' }}</span>
                    <strong>{{ rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') }}{{ $suffix ? ' '.$suffix : '' }}</strong>
                </div>
                <div class="principal-chart-list__track">
                    <span class="principal-chart-list__fill" style="width: {{ $width }}%"></span>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="icon"><i class="bi bi-bar-chart"></i></div>
        <p class="mb-0">Belum ada data statistik untuk periode ini.</p>
    </div>
@endif
