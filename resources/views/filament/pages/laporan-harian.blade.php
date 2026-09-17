<x-filament-panels::page>
    <x-report-sheet
        title="Laporan Harian"
        :from="$this->reportFrom()"
        :until="$this->reportUntil()"
        :period="$this->reportPeriodLabel()"
        orientation="landscape"
    >
        <x-slot:filters>
            {{ $this->form }}
        </x-slot:filters>

        @php($report = $this->report)

        <x-report-formula scope="day" />

        <x-report-kpis :items="[
            ['label' => 'Omzet hari ini', 'value' => rupiah($report['actual_revenue'])],
            ['label' => 'Jasa pekerja', 'value' => rupiah($report['worker_cost'])],
            ['label' => 'Bahan hari ini', 'value' => rupiah($report['operational_cost']), 'hint' => 'Tanpa listrik/PDAM'],
            ['label' => 'Laba hari ini', 'value' => rupiah($report['net_profit']), 'hint' => 'Belum hasil bersih bulan'],
        ]" />

        <div style="margin-top: 1rem;">
            <x-report-expenses :report="$report" :include-period-expenses="false" />
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="report-num">Unit</th>
                        <th class="report-num">Normal</th>
                        <th class="report-num">Tambahan</th>
                        <th class="report-num">Potongan</th>
                        <th class="report-num">Omzet</th>
                        <th class="report-num">Pekerja</th>
                        <th class="report-num">Bahan</th>
                        <th class="report-num">Laba hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['summaries'] as $row)
                        <tr>
                            <td>{{ tanggal_id($row->period_date) }}</td>
                            <td class="report-num">{{ $row->total_units }}</td>
                            <td class="report-num">{{ rupiah($row->normal_revenue) }}</td>
                            <td class="report-num">{{ rupiah($row->additional_income) }}</td>
                            <td class="report-num">{{ rupiah($row->discount) }}</td>
                            <td class="report-num" style="font-weight: 600">{{ rupiah($row->actual_revenue) }}</td>
                            <td class="report-num">{{ rupiah($row->worker_cost_total) }}</td>
                            <td class="report-num">{{ rupiah($row->operational_cost) }}</td>
                            <td class="report-num" style="font-weight: 600">{{ rupiah($row->net_profit) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 2rem; text-align: center; color: #64748b">Belum ada pembukuan pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-report-sheet>
</x-filament-panels::page>
