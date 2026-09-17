@if (! request()->is('admin/login'))
    <footer class="admin-app-footer">
        <div class="admin-app-footer-main">
            <strong>Blue Shark Wash</strong>
            <span aria-hidden="true">·</span>
            <span>Sistem pembukuan cuci mobil &amp; motor</span>
        </div>
        <div class="admin-app-footer-meta">
            Dasbor operasional · {{ tanggal_id(now(), 'd F Y') }} · © {{ now()->year }}
        </div>
    </footer>
@endif
