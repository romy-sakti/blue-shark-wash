<x-filament-panels::page>
    <x-report-sheet
        title="Analisis Kendaraan"
        :from="$this->reportFrom()"
        :until="$this->reportUntil()"
        orientation="portrait"
    >
        <x-slot:filters>
            {{ $this->form }}
        </x-slot:filters>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Kendaraan</th>
                        <th class="report-num">Unit</th>
                        <th class="report-num">Omzet</th>
                        <th class="report-num">Pekerja</th>
                        <th class="report-num">Margin</th>
                        <th class="report-num">Kontribusi omzet</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rows as $row)
                        <tr>
                            <td style="font-weight: 600">{{ $row['name'] }}</td>
                            <td class="report-num">{{ $row['units'] }}</td>
                            <td class="report-num">{{ rupiah($row['revenue']) }}</td>
                            <td class="report-num">{{ rupiah($row['worker_cost']) }}</td>
                            <td class="report-num">{{ rupiah($row['margin']) }}</td>
                            <td class="report-num">{{ $row['contribution'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: #64748b">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-report-sheet>
</x-filament-panels::page>
