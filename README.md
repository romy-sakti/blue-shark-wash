# Blue Shark Wash

Sistem pembukuan keuangan usaha cuci motor dan mobil. Bukan POS/kasir: admin menginput rekap operasional setelah usaha selesai.

Stack: Laravel 11, Filament 3, MySQL (sesuai environment proyek ini).

## Menjalankan

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Panel admin: `/admin`

Akun seed:

- Owner: `owner@blueshark.test` / `password`
- Admin: `admin@blueshark.test` / `password`

Dokumentasi arsitektur, rumus, filter laporan (harian / mingguan / bulanan), dan pemetaan PRD ada di `docs/IMPLEMENTASI.md`.
PRD asli ada di `docs/PRD Sistem Pembukuan Keuangan Cuci Motor dan Mobil.md`.
