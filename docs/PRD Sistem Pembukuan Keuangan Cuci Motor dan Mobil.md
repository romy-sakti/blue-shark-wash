# PRD Sistem Pembukuan Keuangan Cuci Motor & Mobil

## 1. Informasi Produk

Nama sistem: Sistem Pembukuan Keuangan Cuci Motor & Mobil

Jenis aplikasi: Web Application

Fungsi utama: Pembukuan dan analisis keuangan usaha cuci kendaraan

Target pengguna:
- Owner
- Admin

Sistem tidak berfungsi sebagai POS atau kasir.

Admin melakukan input berdasarkan rekap operasional setelah usaha selesai. Input dapat dilakukan setiap hari atau beberapa hari sekali dalam satu periode.

---

## 2. Latar Belakang

Usaha memiliki beberapa jenis layanan cuci motor dan mobil dengan harga yang berbeda.

Dalam operasional, terdapat kondisi yang tidak selalu sesuai dengan harga paket, misalnya:

- Pelanggan membayar lebih.
- Pelanggan membayar kurang.
- Pelanggan memberikan uang tambahan dan tidak mengambil kembalian.
- Kendaraan tidak termasuk dalam paket, seperti sepeda listrik.
- Harga layanan dapat berubah pada masa mendatang.
- Terdapat biaya pekerja berdasarkan jumlah unit.
- Terdapat biaya operasional seperti listrik, PDAM, shampo, pengkilap body, semir ban, dan perawatan jet cleaner.

Sistem harus mencatat kondisi tersebut tanpa mengganggu struktur pembukuan.

---

# 3. Tujuan Sistem

Sistem bertujuan untuk:

1. Mencatat pendapatan usaha.
2. Mencatat jumlah kendaraan yang dicuci.
3. Menghitung biaya pekerja secara otomatis.
4. Mencatat biaya operasional.
5. Menghitung margin setelah biaya pekerja.
6. Menghitung laba bersih.
7. Membandingkan pendapatan motor dan mobil.
8. Menganalisis performa layanan.
9. Menampilkan laporan harian, mingguan, bulanan, dan tahunan.
10. Menyediakan data yang mudah diekspor.

---

# 4. Prinsip Sistem

Sistem menggunakan konsep:

```text
Pendapatan Aktual
- Biaya Pekerja
- Biaya Operasional
= Laba Bersih
```

Pendapatan aktual berasal dari uang yang benar-benar diterima usaha.

Harga paket tetap disimpan sebagai harga normal.

Perbedaan antara harga normal dan uang aktual dicatat sebagai penyesuaian.

---

# 5. Contoh Kasus Pembayaran Lebih

Harga Paket 3:

```text
Harga normal     Rp18.000
Uang diterima    Rp20.000
Tambahan         Rp 2.000
```

Sistem mencatat:

```text
Pendapatan layanan     Rp18.000
Pendapatan tambahan    Rp 2.000
Pendapatan aktual      Rp20.000
Biaya pekerja          Rp 5.000
Margin                 Rp15.000
```

Rp2.000 tidak mengubah harga Paket 3 menjadi Rp20.000.

---

# 6. Contoh Kasus Pembayaran Kurang

Harga Paket 3:

```text
Harga normal     Rp18.000
Uang diterima    Rp15.000
Potongan         Rp 3.000
```

Sistem mencatat:

```text
Harga normal           Rp18.000
Potongan               Rp 3.000
Pendapatan aktual      Rp15.000
Biaya pekerja          Rp 5.000
Margin                 Rp10.000
```

Harga normal tetap Rp18.000.

---

# 7. Contoh Layanan di Luar Paket

Pelanggan mencuci sepeda listrik.

Sepeda listrik tidak masuk kategori paket motor.

Pelanggan membayar:

```text
Rp10.000
```

Sistem mencatat:

```text
Jenis kendaraan     Sepeda Listrik
Layanan             Cuci Sepeda Listrik
Harga layanan       Rp10.000
Uang diterima       Rp10.000
Biaya pekerja       Rp5.000
Margin              Rp5.000
```

Layanan di luar paket harus dapat dibuat oleh admin.

---

# 8. Master Jenis Kendaraan

Jenis kendaraan minimal:

- Motor
- Mobil
- Sepeda Listrik
- Lainnya

Admin dapat menambahkan jenis kendaraan baru.

Field:

```text
id
name
description
is_active
created_at
updated_at
```

---

# 9. Master Layanan

Setiap layanan memiliki:

```text
id
vehicle_type_id
name
normal_price
worker_cost
is_active
created_at
updated_at
```

Contoh:

| Kendaraan | Layanan | Harga | Pekerja |
|---|---|---:|---:|
| Motor | Paket 1 | Rp13.000 | Rp5.000 |
| Motor | Paket 2 | Rp15.000 | Rp5.000 |
| Motor | Paket 3 | Rp18.000 | Rp5.000 |
| Sepeda Listrik | Cuci Sepeda Listrik | Rp10.000 | Rp5.000 |
| Mobil | Paket 1 | Sesuai konfigurasi | Rp25.000 |

Harga dan biaya pekerja tidak boleh di-hardcode di source code.

---

# 10. Biaya Pekerja

Biaya pekerja mengikuti unit kendaraan atau layanan.

Default:

```text
Motor           Rp5.000/unit
Mobil           Rp25.000/unit
```

Biaya untuk kendaraan lain dapat dikonfigurasi.

Contoh:

```text
Sepeda Listrik  Rp5.000/unit
```

Sistem harus menyimpan histori perubahan biaya pekerja.

Contoh:

```text
01-09-2026
Motor
Rp5.000
```

Jika kemudian berubah:

```text
01-01-2027
Motor
Rp6.000
```

Transaksi lama tetap menggunakan Rp5.000.

---

# 11. Pembukuan Harian

Input utama berupa rekap operasional.

Admin tidak perlu memasukkan data pelanggan satu per satu.

Form:

```text
Tanggal

Motor
Paket 1        [jumlah]
Paket 2        [jumlah]
Paket 3        [jumlah]

Mobil
Paket 1        [jumlah]
Paket 2        [jumlah]
Paket 3        [jumlah]

Layanan lainnya
[Tambah]

Pendapatan tambahan
[Rp]

Potongan
[Rp]

Pengeluaran
[Tambah]

Keterangan
[...]
```

Sistem menghitung seluruh nilai secara otomatis.

---

# 12. Contoh Input Harian

Tanggal:

```text
15 September 2026
```

Motor:

```text
Paket 1 = 3 unit
Paket 2 = 4 unit
Paket 3 = 5 unit
```

Perhitungan:

```text
3 × Rp13.000 = Rp39.000
4 × Rp15.000 = Rp60.000
5 × Rp18.000 = Rp90.000
```

Total pendapatan layanan:

```text
Rp189.000
```

Total motor:

```text
12 unit
```

Biaya pekerja:

```text
12 × Rp5.000
= Rp60.000
```

Margin:

```text
Rp189.000 - Rp60.000
= Rp129.000
```

---

# 13. Pendapatan Aktual

Sistem harus membedakan:

```text
Harga Normal
Penyesuaian
Pendapatan Aktual
```

Rumus:

```text
Pendapatan Aktual =
Harga Normal + Tambahan - Potongan
```

Contoh:

```text
Harga normal       Rp100.000
Tambahan           Rp  5.000
Potongan           Rp  3.000
----------------------------
Pendapatan aktual  Rp102.000
```

---

# 14. Jenis Penyesuaian Pendapatan

Master penyesuaian:

- Tambahan pembayaran
- Tip
- Pembayaran lebih
- Potongan
- Pembayaran kurang
- Layanan khusus
- Lainnya

Admin dapat menambahkan jenis baru.

---

# 15. Biaya Operasional

Biaya operasional dicatat terpisah dari biaya pekerja.

Kategori minimal:

- Listrik
- Air PDAM
- Shampo
- Pengkilap body
- Semir ban
- Lap
- Sabun
- Perawatan jet cleaner
- Peralatan
- Sewa
- Transportasi
- Lainnya

---

# 16. Input Pengeluaran

Field:

```text
Tanggal
Kategori
Nama/Keterangan
Nominal
Periode
Catatan
```

Contoh:

```text
Tanggal      15-09-2026
Kategori     Bahan Cuci
Keterangan   Shampo
Nominal      Rp100.000
```

---

# 17. Biaya Bulanan

Biaya seperti listrik dan PDAM tidak harus dibebankan setiap hari.

Contoh:

```text
Listrik
September 2026
Rp300.000
```

```text
PDAM
September 2026
Rp200.000
```

Sistem memasukkan biaya tersebut ke laporan periode September.

---

# 18. Rumus Keuangan

## Total Omzet

```text
Total Omzet =
Pendapatan seluruh layanan
+ Pendapatan tambahan
- Potongan
```

## Biaya Pekerja

```text
Biaya Pekerja =
Jumlah unit × biaya pekerja
```

## Margin Setelah Pekerja

```text
Margin =
Pendapatan Aktual - Biaya Pekerja
```

## Laba Bersih

```text
Laba Bersih =
Pendapatan Aktual
- Biaya Pekerja
- Biaya Operasional
```

---

# 19. Dashboard

Dashboard menampilkan periode yang dipilih.

Contoh hari ini:

```text
OMZET
Rp76.000

BIAYA PEKERJA
Rp25.000

OPERASIONAL
Rp10.000

LABA BERSIH
Rp41.000
```

Statistik kendaraan:

```text
Motor          5
Mobil          0
Sepeda Listrik 0
Total          5
```

---

# 20. Dashboard Mingguan

Menampilkan:

```text
Periode:
09-09-2026 s/d 15-09-2026
```

Informasi:

```text
Total kendaraan
Motor
Mobil
Sepeda listrik

Total omzet
Biaya pekerja
Biaya operasional
Laba bersih
```

---

# 21. Dashboard Bulanan

Menampilkan:

```text
Total kendaraan
Total omzet
Biaya pekerja
Biaya operasional
Laba bersih
Margin laba
```

Analisis:

```text
Omzet rata-rata per hari
Kendaraan rata-rata per hari
Laba rata-rata per hari
Omzet per kendaraan
Laba per kendaraan
```

---

# 22. Analisis Kendaraan

Sistem membandingkan:

```text
Motor
Mobil
Sepeda Listrik
Lainnya
```

Data:

```text
Jumlah unit
Omzet
Biaya pekerja
Margin
Persentase kontribusi omzet
```

---

# 23. Analisis Layanan

Contoh:

```text
Paket 1 Motor
120 unit

Paket 2 Motor
150 unit

Paket 3 Motor
180 unit
```

Sistem menampilkan:

```text
Jumlah unit
Omzet
Biaya pekerja
Margin
Rata-rata per hari
```

---

# 24. Analisis Pendapatan Tambahan

Sistem menampilkan total:

```text
Pendapatan paket
Pendapatan tambahan
Potongan
Pendapatan aktual
```

Contoh:

```text
Pendapatan paket       Rp3.000.000
Tambahan pembayaran    Rp   50.000
Potongan               Rp   25.000
Pendapatan aktual      Rp3.025.000
```

---

# 25. Laporan Laba Rugi Sederhana

Format:

```text
PENDAPATAN

Pendapatan layanan       Rp3.000.000
Pendapatan tambahan      Rp   50.000
Potongan                 Rp  -25.000
-------------------------------------
Pendapatan aktual        Rp3.025.000


BIAYA

Biaya pekerja            Rp1.500.000
Listrik                  Rp  200.000
PDAM                     Rp  150.000
Shampo                   Rp  100.000
Pengkilap                Rp   75.000
Semir ban                Rp   50.000
Perawatan alat           Rp   50.000
-------------------------------------
Total biaya              Rp2.125.000


LABA BERSIH              Rp  900.000
```

---

# 26. Input Mingguan

Sistem boleh menyediakan input berdasarkan periode.

Contoh:

```text
Periode
09-09-2026 sampai 15-09-2026
```

Admin memasukkan:

```text
Paket 1 motor    30
Paket 2 motor    40
Paket 3 motor    50

Mobil Paket 1    10
Mobil Paket 2     5
```

Namun data tetap disimpan berdasarkan tanggal input atau periode pembukuan.

---

# 27. Rekomendasi Model Data

Database PostgreSQL.

Tabel utama:

```text
users
vehicle_types
services
worker_rates

bookkeeping_periods
bookkeeping_items

income_adjustments
expense_categories
expenses

daily_summaries
audit_logs
```

---

# 28. Struktur Bookkeeping

## bookkeeping_periods

```text
id
period_date
notes
status
created_by
created_at
updated_at
```

Status:

```text
draft
final
locked
```

## bookkeeping_items

```text
id
bookkeeping_period_id
vehicle_type_id
service_id
quantity
normal_price
adjustment_amount
actual_revenue
worker_cost
worker_total
notes
created_at
updated_at
```

---

# 29. Snapshot Harga

Ketika pembukuan disimpan, sistem harus menyimpan harga pada saat transaksi.

Contoh:

Harga Paket 3 sekarang:

```text
Rp18.000
```

Admin mencatat:

```text
5 unit
```

Kemudian harga berubah menjadi:

```text
Rp20.000
```

Data lama tetap:

```text
5 × Rp18.000
```

Tidak boleh otomatis berubah menjadi:

```text
5 × Rp20.000
```

Hal yang sama berlaku untuk biaya pekerja.

---

# 30. Koreksi Pembukuan

Admin dapat memperbaiki data yang salah.

Contoh:

Salah input:

```text
Paket 3 = 15
```

Seharusnya:

```text
Paket 3 = 5
```

Sistem harus menyimpan audit:

```text
User
Tanggal
Data sebelum
Data sesudah
Alasan perubahan
```

---

# 31. Penguncian Periode

Setelah pembukuan selesai, admin dapat mengunci periode.

Contoh:

```text
September 2026
Status: Locked
```

Data tidak dapat diubah sembarangan.

Perubahan setelah periode dikunci harus menggunakan mekanisme koreksi.

---

# 32. Export Laporan

Sistem menyediakan:

```text
Excel
PDF
CSV
```

Laporan:

- Pembukuan harian
- Pembukuan mingguan
- Pembukuan bulanan
- Pendapatan
- Pengeluaran
- Biaya pekerja
- Laba rugi
- Analisis kendaraan
- Analisis layanan

---

# 33. Hak Akses

## Owner

- Melihat dashboard
- Melihat semua laporan
- Melihat analisis
- Mengelola master
- Mengoreksi pembukuan
- Mengunci periode
- Export laporan

## Admin

- Input pembukuan
- Input pengeluaran
- Melihat laporan
- Mengelola data sesuai izin

---

# 34. Hal yang Tidak Dibutuhkan

Versi awal tidak membutuhkan:

- POS
- Transaksi pelanggan
- Nomor plat
- Data pelanggan
- Printer struk
- Scanner
- Integrasi QRIS
- Pembayaran online
- Stok kompleks
- Piutang
- Hutang
- Akuntansi double-entry
- Jurnal debit/kredit

Sistem fokus pada pembukuan usaha.

---

# 35. Alur Penggunaan

```text
Usaha beroperasi
       ↓
Catatan manual dibuat
       ↓
Operasional selesai
       ↓
Admin membuka sistem
       ↓
Pilih tanggal/periode
       ↓
Masukkan jumlah kendaraan
       ↓
Masukkan layanan
       ↓
Masukkan pendapatan aktual
       ↓
Masukkan pengeluaran
       ↓
Sistem menghitung
       ↓
Dashboard diperbarui
       ↓
Laporan tersedia
```

---

# 36. Prioritas Pengembangan

## MVP

Fokus pada fitur yang benar-benar diperlukan.

1. Login
2. Dashboard
3. Master kendaraan
4. Master layanan
5. Master biaya pekerja
6. Input pembukuan
7. Pendapatan tambahan
8. Potongan
9. Layanan di luar paket
10. Pengeluaran
11. Laporan harian
12. Laporan mingguan
13. Laporan bulanan
14. Laporan laba rugi

## Versi 2

- Grafik
- Analisis kendaraan
- Analisis layanan
- Export Excel
- Export PDF
- Audit log
- Penguncian periode

## Versi 3

- Stok shampo
- Stok pengkilap
- Stok semir
- Perhitungan HPP bahan
- Estimasi biaya listrik
- Estimasi biaya air
- Biaya per kendaraan
- Profit per layanan

---

# 37. Prinsip UX

Karena admin hanya melakukan pembukuan setelah operasional, input harus sangat cepat.

Halaman utama input cukup memiliki:

```text
Tanggal

MOTOR
Paket 1    [  ]
Paket 2    [  ]
Paket 3    [  ]

MOBIL
Paket 1    [  ]
Paket 2    [  ]
Paket 3    [  ]

LAINNYA
[ + Tambah layanan ]

PENYESUAIAN
Tambahan   [ Rp ]
Potongan   [ Rp ]

PENGELUARAN
[ + Tambah ]

KETERANGAN
[........................]

[ SIMPAN PEMBUKUAN ]
```

Semua perhitungan tampil otomatis.

---

# 38. Contoh Pembukuan Nyata

Misalnya dalam satu hari:

```text
Motor Paket 1
3 × Rp13.000 = Rp39.000

Motor Paket 2
2 × Rp15.000 = Rp30.000

Motor Paket 3
4 × Rp18.000 = Rp72.000

Sepeda listrik
1 × Rp10.000 = Rp10.000
```

Total pendapatan normal:

```text
Rp151.000
```

Kemudian satu pelanggan Paket 3 membayar Rp20.000.

Tambahan:

```text
Rp2.000
```

Pendapatan aktual:

```text
Rp153.000
```

Total kendaraan:

```text
10 unit
```

Biaya pekerja:

```text
9 motor × Rp5.000
= Rp45.000

1 sepeda listrik × Rp5.000
= Rp5.000

Total pekerja
= Rp50.000
```

Margin:

```text
Rp153.000 - Rp50.000
= Rp103.000
```

Jika terdapat biaya operasional Rp20.000:

```text
Laba bersih
Rp103.000 - Rp20.000
= Rp83.000
```

---

# 39. Indikator Utama Owner

Owner dapat melihat:

```text
Omzet
Biaya pekerja
Biaya operasional
Laba bersih

Jumlah kendaraan
Motor
Mobil
Layanan lainnya

Omzet per kendaraan
Laba per kendaraan
Margin laba
```

Sistem harus menampilkan angka aktual berdasarkan periode yang dipilih.

---

# 40. Prinsip Akhir Sistem

Sistem tidak mencoba menggantikan aktivitas operasional cuci kendaraan.

Sistem hanya mencatat hasil operasional.

```text
Operasional
     ↓
Rekap manual
     ↓
Input pembukuan
     ↓
Perhitungan otomatis
     ↓
Laporan keuangan
     ↓
Analisis usaha
```

Fokus utama aplikasi adalah membuat pemilik mengetahui:

```text
Berapa kendaraan yang dicuci?
Berapa uang yang masuk?
Berapa biaya pekerja?
Berapa biaya operasional?
Berapa laba?
Layanan mana yang menghasilkan pendapatan?
Bagaimana perkembangan usaha?
```

Dengan struktur ini, sistem tetap sederhana untuk admin, tetapi database sudah cukup kuat untuk dikembangkan menjadi sistem analisis keuangan usaha di tahap berikutnya.