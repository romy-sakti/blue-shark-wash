# Dokumentasi Implementasi

Sistem dibangun mengikuti PRD *Sistem Pembukuan Keuangan Cuci Motor & Mobil*. Fokus tahap ini adalah **MVP** ditambah fondasi versi 2 yang sudah ada di model data (audit log, kunci periode, analisis, grafik dashboard, ekspor CSV).

## 1. Cara menjalankan

1. Database MySQL `blue-shark-wash` sudah sesuai `.env`.
2. `composer dump-autoload`
3. `php artisan migrate --seed`
4. Buka `/admin`

Akun awal:

- Owner: `owner@blueshark.test` / `password`
- Admin: `admin@blueshark.test` / `password`

Seed juga mengisi master layanan sesuai PRD dan tiga hari contoh (14–16 September 2026), termasuk contoh PRD §38 pada 15 September.

## 2. Arsitektur

```text
Operasional selesai
        ↓
Admin input rekap (Filament)
        ↓
BookkeepingPeriodService
  - snapshot harga & tarif pekerja
  - simpan item + pengeluaran harian
  - hitung DailySummary
  - tulis AuditLog
        ↓
ReportService (dashboard & laporan)
```

Lapisan penting:

| Lapisan | Lokasi | Tugas |
|---|---|---|
| Rumus keuangan | `app/Services/BookkeepingCalculator.php` | Omzet, pekerja, margin, laba |
| Snapshot tarif | `app/Services/WorkerRateResolver.php` | Tarif pekerja menurut tanggal berlaku |
| Simpan pembukuan | `app/Services/BookkeepingPeriodService.php` | Transaksi, ringkasan, audit |
| Laporan | `app/Services/ReportService.php` | Agregasi harian sampai laba rugi |
| Input cepat | `app/Filament/Forms/BookkeepingForm.php` | Form qty per paket |

Uang disimpan sebagai **integer Rupiah** (bukan float). Di form dan tampilan, nominal memakai pemisah ribuan Indonesia (`10000` → `10.000` / `Rp 10.000`). Nilai yang diketik tetap diubah ke integer saat disimpan.

Paket tetap hanya **Motor** dan **Mobil**. Sepeda listrik, semir, kilap body, semprot air, dan jasa lepas lain dicatat di **Layanan lainnya** sebagai nama bebas + nominal bebas. Nilai itu tetap masuk omzet/pendapatan aktual. Biaya pekerja pada baris ini opsional (0 jika tidak ada).

## 3. Rumus (PRD §4 dan §18)

```text
Pendapatan aktual = Harga normal + Tambahan - Potongan
Margin            = Pendapatan aktual - Biaya pekerja
Laba bersih       = Pendapatan aktual - Biaya pekerja - Biaya operasional
```

Harga paket **tidak berubah** karena pelanggan bayar lebih atau kurang. Selisih tercatat sebagai tambahan atau potongan di tingkat hari.

Biaya operasional:

- `daily`: masuk laporan harian pada tanggal pengeluaran, lalu terakumulasi ke minggu.
- `period`: hanya masuk laporan bulan/tahun (listrik, PDAM). Tidak membebani laba satu hari.

## 4. Snapshot harga (PRD §29)

Saat pembukuan disimpan, setiap baris `bookkeeping_items` menyimpan:

- `normal_price` dari master layanan saat itu
- `worker_cost` hasil `WorkerRateResolver` pada tanggal pembukuan

Urutan tarif pekerja:

1. `worker_rates` khusus layanan yang `effective_from <= tanggal`
2. `worker_rates` jenis kendaraan (tanpa `service_id`)
3. `services.worker_cost` sebagai cadangan

Mengubah harga Paket 3 di master tidak mengubah transaksi lama.

## 5. Status periode (PRD §31)

`draft` → `final` → `locked`

- Admin dan owner dapat mengedit draft/final.
- Hanya owner yang mengunci.
- Periode terkunci hanya bisa diubah owner dengan **alasan koreksi** (audit log).

## 6. Hak akses (PRD §33)

| Fitur | Admin | Owner |
|---|---|---|
| Input pembukuan & pengeluaran | Ya | Ya |
| Laporan & dashboard | Ya | Ya |
| Master data | Ya | Ya |
| Kunci / buka periode | Tidak | Ya |
| Koreksi periode terkunci | Tidak | Ya |
| Kelola pengguna | Tidak | Ya |
| Log aktivitas | Tidak | Ya |

Log keuangan **bukan** file `laravel.log`. Setiap mutasi (catat/ubah/hapus pembukuan, pengeluaran mandiri, master layanan/tarif, final/kunci/buka kunci, serta masuk ke sistem) disimpan ke tabel MySQL `audit_logs` bersama siapa, kapan, IP, dan snapshot omzet/laba. Menu **Pengaturan → Log Aktivitas** menampilkan jejak itu per hari (tab Hari ini / Kemarin / 7 hari / Semua). Melihat halaman laporan tidak dicatat — hanya perubahan data. Cocok dijalankan lokal di Laragon; yang penting jejaknya ada di database, bukan di log aplikasi.

## 7. Pemetaan tabel PRD

| PRD | Tabel |
|---|---|
| `users` | `users` + kolom `role`, `is_active` |
| `vehicle_types` | `vehicle_types` |
| `services` | `services` |
| `worker_rates` | `worker_rates` |
| `bookkeeping_periods` | `bookkeeping_periods` |
| `bookkeeping_items` | `bookkeeping_items` |
| `income_adjustments` | `income_adjustments` + `income_adjustment_types` |
| `expense_categories` | `expense_categories` |
| `expenses` | `expenses` |
| `daily_summaries` | `daily_summaries` |
| `audit_logs` | `audit_logs` |

Database produksi PRD menyebut PostgreSQL. Proyek ini memakai **MySQL** karena itu yang sudah terpasang di `.env`. Skema tidak memakai fitur khusus PostgreSQL.

## 8. Menu Filament

- **Dashboard** — filter hari ini / minggu / bulan / tahun, KPI, unit kendaraan, grafik, pembukuan terbaru
- **Pembukuan Harian** — input qty paket, tambahan, potongan, pengeluaran, layanan di luar paket
- **Pengeluaran** — biaya harian atau bulanan
- **Laporan** — harian, mingguan, bulanan, laba rugi, analisis kendaraan, analisis layanan
- **Master Data** — kendaraan, layanan, tarif pekerja, kategori, jenis penyesuaian
- **Pengaturan** — pengguna dan log aktivitas harian (owner)

Ekspor: tombol **Unduh CSV** dan **Cetak / PDF**. Cetak memakai lembar A4 (kop Blue Shark Wash, periode, tanpa sidebar/filter). Pilih *Save as PDF* di dialog browser.

## 9. Filter periode laporan

Tiga laporan utama dan Laba Rugi memakai grain tetap (hari / minggu / bulan). Filter **Dari–Sampai** hanya di Analisis. Logika ada di `app/Filament/Concerns/HasReportFilters.php` lewat `reportGrain()`: `day` | `week` | `month` | `range`.

| Halaman | Grain | Input | Periode yang dihitung |
|---|---|---|---|
| Laporan Harian | `day` | Satu **Tanggal** | Hari itu saja (00:00–23:59) |
| Laporan Mingguan | `week` | Satu tanggal di **Minggu** | Otomatis **Senin–Minggu** (ISO). Pilih Rabu 16 Sep 2026 → 14–20 Sep 2026 |
| Laporan Bulanan | `month` | Pilih **Bulan** (36 bulan terakhir) | Tanggal 1 sampai akhir bulan kalender. September 2026 → 1–30 Sep. Termasuk biaya `period` (listrik/PDAM) |
| Laba Rugi | `month` | Pilih **Bulan** | Hasil bersih bulan (omzet − pekerja − bahan − listrik/PDAM) |
| Analisis Kendaraan / Layanan | `range` | **Dari** dan **Sampai** | Rentang bebas |

Minggu selalu mulai Senin, bukan Minggu kalender Amerika. Field Mingguan menampilkan Senin minggu itu; teks bantuan menampilkan `Senin–Minggu · … s/d …`.

Isi halaman:

- **Harian** — cek tutup toko. Laba hari belum dikurangi listrik/PDAM.
- **Mingguan** — kontrol 7 hari. Laba minggu belum dikurangi listrik/PDAM.
- **Bulanan** dan **Laba Rugi** — **hasil bersih bulan**: omzet − jasa pekerja − pengeluaran bulan (bahan + listrik + PDAM). Kotak hijau adalah angka yang dipakai rekap.

Setiap halaman menampilkan rumus di bagian atas supaya tidak tertukar.

### Cara menghitung

- **Jumlah** omzet, pekerja, dan pengeluaran = total yang benar-benar tercatat di jendela itu. Bukan dikali 7 atau 30.
- **Rata-rata per hari** = total ÷ **hari operasi** (hari ada pembukuan). Bukan ÷ 7, 30, atau 31. Contoh Agustus mulai 17: 15 hari operasi, bukan 31 hari kalender.
- Hari tanpa pembukuan (tutup) tidak membagi rata-rata.
- Label contoh: `15 hari operasi · 17 Agu – 31 Agu 2026 (dari 31 hari kalender)`.

### Pengeluaran di laporan

| Jenis | Harian | Mingguan | Bulanan / Laba Rugi |
|---|---|---|---|
| Jasa pekerja | Ya, ikut unit hari itu | Jumlah 7 hari | Jumlah bulan |
| Shampo, sabun, bahan | Ya, jika dicatat hari itu | Jumlah yang dicatat di minggu itu | Jumlah bulan |
| Listrik, PDAM | Tidak | Tidak | Ya, jika sudah diinput untuk bulan itu |

Nol artinya belum ada pengeluaran, bukan error. Shampo tidak dibagi rata ke setiap hari.

Kop cetak/PDF memakai `reportPeriodLabel()`: tanggal lengkap untuk harian, `F Y` untuk bulanan, `tanggal s/d tanggal` untuk mingguan dan rentang.

Tes filter: `tests/Feature/ReportPeriodFilterTest.php` (dilewati jika `pdo_sqlite` tidak ada). Tes rata-rata hari operasi: `tests/Unit/OperatingDaysTest.php`.

## 10. Data contoh 15 September 2026 (PRD §38)

```text
3 × Paket 1 Motor     = Rp 39.000
2 × Paket 2 Motor     = Rp 30.000
4 × Paket 3 Motor     = Rp 72.000
1 × Sepeda listrik    = Rp 10.000
Pendapatan normal     = Rp151.000
Tambahan              = Rp  2.000
Pendapatan aktual     = Rp153.000
9 motor × Rp5.000     = Rp 45.000
1 listrik × Rp5.000   = Rp  5.000
Biaya pekerja         = Rp 50.000
Margin                = Rp103.000
Shampo                = Rp 20.000
Laba bersih harian    = Rp 83.000
```

Tes otomatis rumus ini: `tests/Unit/BookkeepingCalculatorTest.php`.

## 11. Belum masuk tahap ini

Sesuai PRD versi 3, belum dikerjakan: stok shampo/pengkilap/semir, HPP bahan, estimasi listrik/air, profit per layanan yang lebih dalam.

Ekspor file Excel native (`.xlsx`) dan generator PDF server-side bisa ditambah nanti; MVP memakai CSV + cetak browser.
