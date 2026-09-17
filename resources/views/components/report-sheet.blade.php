@props([
    'title',
    'from' => null,
    'until' => null,
    'period' => null,
    'orientation' => 'portrait',
])

@php
    $periodLabel = $period;
    if (! $periodLabel && $from && $until) {
        $fromDate = \Illuminate\Support\Carbon::parse($from);
        $untilDate = \Illuminate\Support\Carbon::parse($until);
        $periodLabel = $fromDate->isSameDay($untilDate)
            ? tanggal_id($fromDate)
            : tanggal_id($fromDate).' s/d '.tanggal_id($untilDate);
    }
@endphp

<div
    {{ $attributes->class('report-sheet') }}
    data-report-title="{{ $title }}"
    data-orientation="{{ $orientation }}"
>
    <header class="report-print-header">
        <p class="report-print-brand">Blue Shark Wash</p>
        <h1>{{ $title }}</h1>
        @if ($periodLabel)
            <p class="report-print-period">{{ $periodLabel }}</p>
        @endif
        <p class="report-print-meta">Dicetak {{ tanggal_id(now(), 'd F Y · H:i') }}</p>
    </header>

    @isset($filters)
        <div class="report-print-hide">
            {{ $filters }}
        </div>
    @endisset

    {{ $slot }}

    <footer class="report-print-footer">
        Blue Shark Wash · {{ $title }}
        @if ($periodLabel)
            · {{ $periodLabel }}
        @endif
    </footer>
</div>
