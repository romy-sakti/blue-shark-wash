@props([
    'values' => null,
])

@php
    $rows = \App\Models\AuditLog::displayValues(is_array($values) ? $values : []);
@endphp

@if ($rows === [])
    <p class="text-sm text-gray-500">Tidak ada data.</p>
@else
    <dl class="audit-values">
        @foreach ($rows as $label => $value)
            <div class="audit-values-row">
                <dt>{{ $label }}</dt>
                <dd>{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
@endif
