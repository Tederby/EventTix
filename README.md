# EventTix — Sistem Ticketing Event & Seminar Kampus
> **Tugas Pemrograman Berorientasi Objek (PBO)**  
> **Kelompok 6**

---

> [!NOTE]
> **Konteks & Batasan Proyek:**  
> Repositori ini dikembangkan murni untuk keperluan pemenuhan tugas akademik perkuliahan Pemrograman Berorientasi Objek (PBO). Proyek ini merupakan studi kasus prototipe arsitektur perangkat lunak, **bukan** produk komersial atau sistem yang ditujukan untuk rilis produksi skala penuh.

---

## Ringkasan Proyek

**EventTix** adalah prototipe sistem pemesanan tiket kegiatan kampus (seminar nasional, workshop, hackathon) yang menerapkan prinsip-prinsip Object-Oriented Programming (OOP) secara ketat.

Proyek ini dibangun dalam dua platform paralel yang mengacu pada satu kontrak arsitektur yang sama ([ARCHITECTURE_SPEC.md](docs/ARCHITECTURE_SPEC.md)):
1. **Java Console (`java-console/`)**: Implementasi murni PBO (*core domain logic*) menggunakan pustaka standar Java (`java.util.*`) tanpa dependensi pihak ketiga. Berfungsi sebagai pembuktian 4 pilar PBO, integritas validasi transaksi, dan pencegahan duplikasi *check-in*.
2. **Web PHP Native (`web-php/`)**: Prototipe antarmuka pengguna interaktif bergaya karcis fisik (*skeuomorphic ticket*), dilengkapi fallback penyimpanan ganda (*Dual Storage Engine*: MySQL melalui `eventtix.sql` dan file JSON mandiri melalui `DataManager`).

---

## Penerapan 4 Pilar PBO

| Pilar PBO | Implementasi pada EventTix |
| :--- | :--- |
| **Abstraksi (*Abstraction*)** | Kelas abstrak `User`, `Tiket`, dan `Pembayaran` menetapkan metode kontrak (`getRole()`, `hitungHarga()`, `getFasilitas()`, `hitungBiayaAdmin()`, `prosesPembayaran()`) tanpa instansiasi langsung. |
| **Pewarisan (*Inheritance*)** | `Panitia` dan `Peserta` mewarisi `User`. `TiketReguler` dan `TiketVIP` mewarisi `Tiket`. `PembayaranTransfer` dan `PembayaranEWallet` mewarisi `Pembayaran`. |
| **Polimorfisme (*Polymorphism*)** | *Dynamic dispatch* saat kalkulasi harga tiket (`TiketReguler` vs `TiketVIP`) dan perhitungan biaya admin (`Transfer` flat Rp 2.500 vs `E-Wallet` 1%). *Method overloading* pada pembuatan event dan penjelajahan katalog. |
| **Enkapsulasi (*Encapsulation*)** | Seluruh atribut bernilai `private`/`protected`. Mutasi status dikendalikan oleh metode validasi ketat (pencegahan saldo/kuota negatif, verifikasi regex rekening/ponsel, pembatasan hak akses operasional). |

---

## Fitur Utama Berdasarkan Peran

### 1. Peserta (Mahasiswa / Pengunjung)
* **Katalog Event**: Eksplorasi daftar seminar kampus aktif dengan indikator kapasitas visual (*segmented bar*).
* **Pemesanan Fleksibel**: Pembelian tiket reguler maupun VIP (dengan benefit kursi nomor khusus dan fasilitas prioritas).
* **Klaim Kode Promo**: Validasi voucher diskon otomatis dengan batas kuota dan pagu potongan maksimal (*capped amount*).
* **Multi-Kanal Pembayaran**: Simulasi pelunasan tagihan melalui Transfer Bank (verifikasi berkas) atau E-Wallet (konfirmasi instan).
* **Tiket Digital**: Karcis digital berperforasi dengan kode unik, stempel status (*AKTIF* / *TERPAKAI*), dan dukungan cetak fisik.

### 2. Panitia (Penyelenggara Acara)
* **Manajemen Event**: Pembuatan event baru, penyesuaian kuota, dan penutupan penjualan otomatis saat kuota penuh.
* **Penerbitan Promo**: Perilisan kode diskon persentase dengan masa berlaku dan batas kuota pemakaian.
* **Terminal Check-In Pintu Masuk**: Pemindaian kode tiket saat hari H acara, verifikasi kelayakan akses, serta penolakan otomatis tiket duplikat (*anti-fraud double-scan*).
* **Laporan Penjualan**: Rekapitulasi metrik tiket terjual, sisa kuota, dan akumulasi total dana transaksi.

---

## Diagram Kelas & Arsitektur Sistem

Dokumentasi arsitektur EventTix disajikan dalam dua sudut pandang diagram kelas untuk merekam evolusi perancangan:
1. **Diagram Perencanaan / Konseptual (*Design-Time Blueprint*)**: Mengacu pada rancangan awal kelas dan relasi di [`docs/class_diagram.jpeg`](docs/class_diagram.jpeg).
2. **Diagram Hasil Implementasi (*As-Built Architecture*)**: Merefleksikan kondisi nyata setelah pengodean (penambahan atribut koleksi navigasi runtime, metode overload konsol, konstanta sistem, dan layer Singleton `DataManager`).

Detail perbandingan kedua diagram dapat dilihat pada [`docs/ARCHITECTURE_SPEC.md`](docs/ARCHITECTURE_SPEC.md).

---

## Struktur Direktori

```text
EventTix/
├── docs/
│   ├── ARCHITECTURE_SPEC.md      # Spesifikasi teknis & kontrak metode (Single Source of Truth)
│   ├── JEJAK_PENGERJAAN.md       # Catatan & panduan pembagian tugas kelompok awal
│   └── class_diagram.jpeg        # Diagram kelas PBO
├── java-console/
│   └── src/
│       └── eventtix/             # Source code Java (package eventtix)
│           ├── Main.java         # Driver simulasi 8 tahap skenario terpadu
│           ├── User.java, Panitia.java, Peserta.java
│           ├── Event.java, Tiket.java, TiketReguler.java, TiketVIP.java
│           ├── CheckIn.java, KodePromo.java, Pesanan.java
│           ├── Pembayaran.java, PembayaranTransfer.java, PembayaranEWallet.java
│           └── *Demo.java        # Driver pengujian isolasi modul mandiri
└── web-php/
    ├── index.php                 # Katalog publik event
    ├── checkout.php              # Alur pemesanan tiket 3 langkah
    ├── checkin.php               # Terminal validasi tiket panitia
    ├── dashboard.php             # Dashboard admin panitia
    ├── tiket.php                 # Tampilan karcis digital
    ├── classes/                  # Implementasi kelas OOP PHP
    ├── config/database.php       # Koneksi database PDO MySQL
    ├── data/store.json           # File database JSON portable
    └── database/eventtix.sql     # Skema DDL MySQL Single Table Inheritance
```

---

## Panduan Menjalankan Program

### A. Menjalankan Modul Java Console
Pastikan JDK (Java Development Kit) 8 atau versi lebih baru telah terpasang.

1. Buka terminal pada root repositori, lalu kompilasi seluruh file Java:
   ```bash
   javac -d java-console/bin java-console/src/eventtix/*.java
   ```

2. Jalankan simulasi alur utama:
   ```bash
   java -cp java-console/bin eventtix.Main
   ```

3. *(Opsional)* Menjalankan pengujian isolasi modul spesifik:
   ```bash
   # Uji modul Pembayaran
   java -cp java-console/bin eventtix.PembayaranDemo

   # Uji modul Pesanan & Promo
   java -cp java-console/bin eventtix.PesananPromoDemo

   # Uji modul Tiket & Check-In
   java -cp java-console/bin eventtix.TiketCheckInDemo
   ```

---

### B. Menjalankan Aplikasi Web PHP

Aplikasi web dapat dijalankan secara instan tanpa perlu menyiapkan server database MySQL karena telah dilengkapi penyimpanan berkas JSON bawaan (`data/store.json`).

#### Opsi 1: Menjalankan Menggunakan PHP Built-in Server (Rekomendasi Cepat)
1. Masuk ke direktori web:
   ```bash
   cd web-php
   ```
2. Jalankan web server bawaan PHP:
   ```bash
   php -S localhost:8000
   ```
3. Buka peramban (*browser*) dan akses:  
   `http://localhost:8000`

#### Opsi 2: Menggunakan MySQL (Opsional)
1. Impor file skema database `web-php/database/eventtix.sql` ke server MySQL lokal (phpMyAdmin / MySQL CLI).
2. Sesuaikan konfigurasi kredensial basis data pada `web-php/config/database.php` jika diperlukan (default: `127.0.0.1:3306`, user: `root`, password: `""`).

---

## Akun & Kredensial Demo

| Peran Akun | Email Akun | Kata Sandi | Halaman Akses Utama |
| :--- | :--- | :--- | :--- |
| **Peserta** | `isya@mahasiswa.ac.id` | `password123` | `index.php` (Katalog & Pemesanan Tiket) |
| **Panitia** | `panitia@kampus.ac.id` | `panitia123` | `dashboard.php` & `checkin.php` (Validasi Masuk) |

> *Catatan: Aplikasi web juga menyediakan pintasan tombol alih peran instan (`Switch Role`) pada menu login dan navigasi atas untuk mempermudah evaluasi.*

---

## 👥 Kontribusi & Pembagian Modul Tim (Kelompok 6)

* **Modul 1 (Web PHP & MySQL)**: Rancang bangun antarmuka web, skema database STI, dan integrasi DAL DataManager.
* **Modul 2 (Pengguna & Event - Java)**: `User`, `Panitia`, `Peserta`, `Event`, dan orkestrasi skenario di `Main.java`.
* **Modul 3 (Tiket & Check-In - Java)**: `Tiket`, `TiketReguler`, `TiketVIP`, dan `CheckIn`.
* **Modul 4 (Pesanan & Promo - Java)**: `Pesanan`, `KodePromo`, dan kalkulasi pemotongan harga.
* **Modul 5 (Pembayaran - Java)**: `Pembayaran`, `PembayaranTransfer`, dan `PembayaranEWallet`.

*Histori panduan pengerjaan internal tim selama masa tugas dapat dilihat pada [docs/JEJAK_PENGERJAAN.md](docs/JEJAK_PENGERJAAN.md).*