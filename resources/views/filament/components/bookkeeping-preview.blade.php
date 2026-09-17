@php
    $cards = [
        ['label' => 'Pendapatan normal', 'value' => rupiah($calc->normalRevenue)],
        ['label' => 'Tambahan', 'value' => rupiah($calc->additionalIncome)],
        ['label' => 'Potongan', 'value' => rupiah($calc->discount)],
        ['label' => 'Pendapatan aktual', 'value' => rupiah($calc->actualRevenue)],
        ['label' => 'Unit kendaraan', 'value' => number_format($calc->totalUnits, 0, ',', '.')],
        ['label' => 'Biaya pekerja', 'value' => rupiah($calc->workerCostTotal)],
        ['label' => 'Operasional hari ini', 'value' => rupiah($calc->operationalCost)],
        ['label' => 'Laba bersih harian', 'value' => rupiah($calc->netProfit)],
    ];
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr)); gap: 0.75rem;">
    @foreach ($cards as $card)
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $card['label'] }}</div>
            <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $card['value'] }}</div>
        </div>
    @endforeach
</div>

@if (count($calc->lines))
    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2">Layanan</th>
                    <th class="px-3 py-2 text-right whitespace-nowrap" style="width: 1%">Unit</th>
                    <th class="px-3 py-2 text-right whitespace-nowrap" style="width: 1%">Harga</th>
                    <th class="px-3 py-2 text-right whitespace-nowrap" style="width: 1%">Pendapatan</th>
                    <th class="px-3 py-2 text-right whitespace-nowrap" style="width: 1%">Pekerja</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($calc->lines as $line)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-2">{{ $line['vehicle_type_name'] ? $line['vehicle_type_name'].' · ' : '' }}{{ $line['service_name'] }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap" style="width: 1%; font-variant-numeric: tabular-nums;">{{ $line['quantity'] }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap" style="width: 1%; font-variant-numeric: tabular-nums;">{{ rupiah($line['normal_price']) }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap" style="width: 1%; font-variant-numeric: tabular-nums;">{{ rupiah($line['actual_revenue']) }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap" style="width: 1%; font-variant-numeric: tabular-nums;">{{ rupiah($line['worker_total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
