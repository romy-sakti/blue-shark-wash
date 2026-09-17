@props([
    'scope' => 'month',
])

{{-- Hierarki: rumus di atas angka, supaya admin/owner tidak tertukar laba harian/mingguan dengan hasil bulan. --}}
<div {{ $attributes->class('report-callout') }}>
    @if ($scope === 'month')
        <p class="report-callout-kicker">Hasil bersih bulan</p>
        <p class="report-callout-formula">Laba bersih = Omzet − Jasa pekerja − Pengeluaran bulan</p>
        <p class="report-hint">Pengeluaran bulan = bahan (shampo, sabun, dll.) + listrik + PDAM yang sudah dicatat untuk bulan ini. Nol berarti belum diinput, bukan error. Rata-rata per hari hanya bantu baca — yang dipakai rekap adalah <strong>laba bersih</strong>.</p>
    @elseif ($scope === 'week')
        <p class="report-callout-kicker">Laba minggu ini — belum hasil bulan</p>
        <p class="report-callout-formula">Laba minggu = Omzet − Jasa pekerja − Bahan harian</p>
        <p class="report-hint">Listrik dan PDAM tidak dipotong di sini karena tagihannya sebulan. Untuk hasil bersih bulan, buka <strong>Laporan Bulanan</strong> atau <strong>Laba Rugi</strong>.</p>
    @else
        <p class="report-callout-kicker">Laba hari ini — belum hasil bulan</p>
        <p class="report-callout-formula">Laba hari = Omzet − Jasa pekerja − Bahan hari ini</p>
        <p class="report-hint">Listrik dan PDAM tidak dipotong di laporan harian. Hasil bersih usaha dihitung per bulan.</p>
    @endif
</div>
