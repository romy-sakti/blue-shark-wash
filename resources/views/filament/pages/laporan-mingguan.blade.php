<x-filament-panels::page>
    <x-report-sheet
        title="Laporan Mingguan"
        :from="$this->reportFrom()"
        :until="$this->reportUntil()"
        :period="$this->reportPeriodLabel()"
        orientation="landscape"
    >
        <x-slot:filters>
            {{ $this->form }}
        </x-slot:filters>

        @php($report = $this->report)

        <x-report-formula scope="week" />

        <x-report-kpis :items="[
            ['label' => 'Omzet minggu', 'value' => rupiah($report['actual_revenue'])],
            ['label' => 'Jasa pekerja', 'value' => rupiah($report['worker_cost'])],
            ['label' => 'Bahan harian', 'value' => rupiah($report['operational_cost']), 'hint' => 'Tanpa listrik/PDAM'],
            ['label' => 'Laba minggu', 'value' => rupiah($report['net_profit']), 'hint' => 'Belum hasil bersih bulan'],
        ]" />

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div class="report-panel">
                <h3 style="margin: 0; font-size: 0.875rem; font-weight: 600;">Hari operasi minggu ini</h3>
                <p style="margin: 0.5rem 0 0; font-size: 0.875rem;">{{ $report['operating_days_label'] }}</p>
                <p class="report-hint">Hari tutup tidak dihitung. Hasil bersih usaha tetap di Laporan Bulanan.</p>
            </div>
            <x-report-expenses :report="$report" :include-period-expenses="false" />
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th class="report-num">Unit</th>
                        <th class="report-num">Omzet</th>
                        <th class="report-num">Pekerja</th>
                        <th class="report-num">Bahan</th>
                        <th class="report-num">Laba hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['summaries'] as $row)
                        <tr>
                            <td>{{ tanggal_id($row->period_date, 'l, d F Y') }}</td>
                            <td class="report-num">{{ $row->total_units }}</td>
                            <td class="report-num" style="font-weight: 600">{{ rupiah($row->actual_revenue) }}</td>
                            <td class="report-num">{{ rupiah($row->worker_cost_total) }}</td>
                            <td class="report-num">{{ rupiah($row->operational_cost) }}</td>
                            <td class="report-num" style="font-weight: 600">{{ rupiah($row->net_profit) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: #64748b">Belum ada pembukuan pada minggu ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-report-sheet>
</x-filament-panels::page>
