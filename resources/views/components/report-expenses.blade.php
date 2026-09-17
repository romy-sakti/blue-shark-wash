@props([
    'report',
    'includePeriodExpenses' => false,
])

<div class="report-panel">
    <h3 style="margin: 0; font-size: 0.875rem; font-weight: 600;">Pengeluaran</h3>
    <dl class="report-dl">
        <div>
            <dt>Jasa pekerja</dt>
            <dd>{{ rupiah($report['worker_cost']) }}</dd>
        </div>
        @forelse ($report['expenses_by_category'] as $name => $amount)
            <div>
                <dt>{{ $name }}</dt>
                <dd>{{ rupiah($amount) }}</dd>
            </div>
        @empty
            <div>
                <dt>Bahan &amp; operasional</dt>
                <dd>{{ rupiah(0) }}</dd>
            </div>
        @endforelse
        <div class="report-dl-total">
            <dt>Total biaya</dt>
            <dd>{{ rupiah($report['worker_cost'] + $report['operational_cost']) }}</dd>
        </div>
    </dl>
    <p class="report-hint">
        @if ($includePeriodExpenses)
            Rincian yang dipotong dari omzet untuk mendapat <strong>laba bersih bulan</strong>. Listrik/PDAM hanya muncul jika sudah diinput untuk bulan ini.
        @else
            Dipotong dari omzet hari/minggu ini. Listrik dan PDAM <strong>tidak</strong> masuk — itu hanya di Laporan Bulanan / Laba Rugi.
        @endif
    </p>
</div>
