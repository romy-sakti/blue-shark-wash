@props([
    'items' => [],
])

<div class="report-kpis">
    @foreach ($items as $item)
        <div class="report-kpi">
            <div class="report-kpi-label">{{ $item['label'] }}</div>
            <div class="report-kpi-value">{{ $item['value'] }}</div>
            @if (! empty($item['hint']))
                <div class="report-kpi-hint">{{ $item['hint'] }}</div>
            @endif
        </div>
    @endforeach
</div>
