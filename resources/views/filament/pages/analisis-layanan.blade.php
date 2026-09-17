<x-filament-panels::page>
    <x-report-sheet
        title="Analisis Layanan"
        :from="$this->reportFrom()"
        :until="$this->reportUntil()"
        orientation="landscape"
    >
        <x-slot:filters>
            {{ $this->form }}
        </x-slot:filters>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Layanan</th>
                        <th>Kendaraan</th>
                        <th class="report-num">Unit</th>
                        <th class="report-num">Omzet</th>
                        <th class="report-num">Pekerja</th>
                        <th class="report-num">Margin</th>
                        <th class="report-num">Rata-rata / hari operasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rows as $row)
                        <tr>
                            <td style="font-weight: 600">{{ $row['name'] }}</td>
                            <td>{{ $row['vehicle'] }}</td>
                            <td class="report-num">{{ $row['units'] }}</td>
                            <td class="report-num">{{ rupiah($row['revenue']) }}</td>
                            <td class="report-num">{{ rupiah($row['worker_cost']) }}</td>
                            <td class="report-num">{{ rupiah($row['margin']) }}</td>
                            <td class="report-num">{{ $row['avg_per_day'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-report-sheet>
</x-filament-panels::page>
