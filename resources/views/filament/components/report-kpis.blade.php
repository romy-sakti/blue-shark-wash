@props([
    'items' => [],
])

<div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4 print:grid-cols-4">
    @foreach ($items as $item)
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $item['label'] }}</div>
            <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ $item['value'] }}</div>
            @if (! empty($item['hint']))
                <div class="mt-1 text-xs text-gray-500">{{ $item['hint'] }}</div>
            @endif
        </div>
    @endforeach
</div>
