<x-filament-panels::page>
    <x-report-sheet
        title="Laporan Bulanan"
        :from="$this->reportFrom()"
        :until="$this->reportUntil()"
        :period="$this->reportPeriodLabel()"
        orientation="portrait"
    >
        <x-slot:filters>
            {{ $this->form }}
        </x-slot:filters>

        @php($report = $this->report)

        <x-report-formula scope="month" />

        <div class="report-highlight">
            <span>
                <span class="report-highlight-kicker">Laba bersih {{ $this->reportPeriodLabel() }}</span>
                <span class="report-highlight-sub">Omzet − jasa pekerja − bahan − listrik/PDAM</span>
            </span>
            <span class="report-highlight-value">{{ rupiah($report['net_profit']) }}</span>
        </div>

        <x-report-kpis :items="[
            ['label' => 'Omzet', 'value' => rupiah($report['actual_revenue']), 'hint' => 'Uang masuk bulan ini'],
            ['label' => 'Jasa pekerja', 'value' => rupiah($report['worker_cost']), 'hint' => 'Ikut jumlah kendaraan'],
            ['label' => 'Pengeluaran', 'value' => rupiah($report['operational_cost']), 'hint' => 'Bahan + listrik + PDAM'],
            ['label' => 'Kendaraan', 'value' => number_format($report['total_units'], 0, ',', '.')],
            ['label' => 'Margin laba', 'value' => $report['margin_percent'].'%', 'hint' => 'Laba bersih ÷ omzet'],
        ]" />

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); gap: 1rem;">
            <x-report-expenses :report="$report" :include-period-expenses="true" />
            <div class="report-panel">
                <h3 style="margin: 0; font-size: 0.875rem; font-weight: 600;">Rata-rata per hari operasi</h3>
                <p class="report-hint">{{ $report['operating_days_label'] }}. Ini bukan hasil bersih bulan — hanya omzet/laba dibagi hari buka.</p>
                <dl class="report-dl">
                    <div><dt>Omzet / hari buka</dt><dd>{{ rupiah($report['avg_revenue_per_day']) }}</dd></div>
                    <div><dt>Kendaraan / hari buka</dt><dd>{{ $report['avg_units_per_day'] }}</dd></div>
                    <div><dt>Laba / hari buka</dt><dd>{{ rupiah($report['avg_profit_per_day']) }}</dd></div>
                </dl>
            </div>
            <div class="report-panel">
                <h3 style="margin: 0; font-size: 0.875rem; font-weight: 600;">Per kendaraan</h3>
                <dl class="report-dl">
                    <div><dt>Omzet / kendaraan</dt><dd>{{ rupiah($report['revenue_per_unit']) }}</dd></div>
                    <div><dt>Laba / kendaraan</dt><dd>{{ rupiah($report['profit_per_unit']) }}</dd></div>
                    <div><dt>Hari kalender bulan ini</dt><dd>{{ $report['calendar_days'] }} hari</dd></div>
                </dl>
            </div>
        </div>

        <div>
            <h3 class="report-section-label">Rincian per minggu</h3>
            <p class="report-hint" style="margin-top: 0.35rem; margin-bottom: 0.75rem;">Laba di tabel ini belum dikurangi listrik/PDAM. Jumlahkan omzet dan bahan saja; hasil bersih bulan ada di kotak hijau di atas.</p>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Minggu</th>
                            <th class="report-num">Unit</th>
                            <th>Motor / Mobil / Lainnya</th>
                            <th class="report-num">Omzet</th>
                            <th class="report-num">Pekerja</th>
                            <th class="report-num">Bahan</th>
                            <th class="report-num">Laba minggu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->weeks as $week)
                            <tr>
                                <td>{{ tanggal_id($week['from'], 'd M') }} – {{ tanggal_id($week['until'], 'd M Y') }}</td>
                                <td class="report-num">{{ $week['total_units'] }}</td>
                                <td>{{ $week['vehicle_hint'] !== '' ? $week['vehicle_hint'] : '—' }}</td>
                                <td class="report-num" style="font-weight: 600">{{ rupiah($week['actual_revenue']) }}</td>
                                <td class="report-num">{{ rupiah($week['worker_cost']) }}</td>
                                <td class="report-num">{{ rupiah($week['operational_cost']) }}</td>
                                <td class="report-num" style="font-weight: 600">{{ rupiah($week['net_profit']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b">Belum ada pembukuan pada bulan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-report-sheet>
</x-filament-panels::page>
