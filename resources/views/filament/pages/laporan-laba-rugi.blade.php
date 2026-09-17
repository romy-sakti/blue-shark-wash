<x-filament-panels::page>
    <x-report-sheet
        title="Laporan Laba Rugi"
        :from="$this->reportFrom()"
        :until="$this->reportUntil()"
        :period="$this->reportPeriodLabel()"
        orientation="portrait"
    >
        <x-slot:filters>
            {{ $this->form }}
        </x-slot:filters>

        @php($report = $this->report)

        <div class="report-panel" style="max-width: 40rem; margin: 0 auto; width: 100%;">
            <x-report-formula scope="month" />

            <h3 class="report-section-label">Pendapatan</h3>
            <dl class="report-dl">
                <div><dt>Pendapatan layanan</dt><dd>{{ rupiah($report['normal_revenue']) }}</dd></div>
                <div><dt>Pendapatan tambahan</dt><dd>{{ rupiah($report['additional_income']) }}</dd></div>
                <div><dt>Potongan</dt><dd>- {{ rupiah($report['discount']) }}</dd></div>
                <div class="report-dl-total"><dt>Omzet (pendapatan aktual)</dt><dd>{{ rupiah($report['actual_revenue']) }}</dd></div>
            </dl>

            <h3 class="report-section-label" style="margin-top: 1.5rem;">Biaya</h3>
            <dl class="report-dl">
                <div><dt>Jasa pekerja</dt><dd>{{ rupiah($report['worker_cost']) }}</dd></div>
                @forelse ($report['expenses_by_category'] as $name => $amount)
                    <div><dt>{{ $name }}</dt><dd>{{ rupiah($amount) }}</dd></div>
                @empty
                    <div><dt>Bahan, listrik, PDAM</dt><dd>{{ rupiah(0) }}</dd></div>
                @endforelse
                <div class="report-dl-total"><dt>Total biaya</dt><dd>{{ rupiah($report['total_cost']) }}</dd></div>
            </dl>

            <div class="report-highlight">
                <span>
                    <span class="report-highlight-kicker">Laba bersih {{ $this->reportPeriodLabel() }}</span>
                    <span class="report-highlight-sub">Omzet − jasa pekerja − pengeluaran bulan</span>
                </span>
                <span class="report-highlight-value">{{ rupiah($report['net_profit']) }}</span>
            </div>
        </div>
    </x-report-sheet>
</x-filament-panels::page>
