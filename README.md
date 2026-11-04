# EventTix (Kelompok 6)

Repository untuk aplikasi pemesanan tiket event (**EventTix**) berbasis Java Console (OOP) dan Web PHP Native.

---

## BACA DULU!
1. **DILARANG KERAS** ngoding atau *push* langsung ke branch `main`.
2. Kerjakan tugas di **branch masing-masing** sesuai tabel pembagian di bawah.
3. Untuk bagian **Java**, **JANGAN** mengedit file `.java` yang bukan bagiannya supaya tidak terjadi *merge conflict* saat digabungkan.
4. Pastikan baris paling atas di setiap file `.java` tetap menggunakan `package eventtix;`.

---

## Pembagian Branch & Tugas

| Branch | Bagian | Deskripsi Tugas & File yang Dikerjakan |
|:---|:---|:---|
| `web-php` | **1. Web (PHP & MySQL)** | Fokus di folder `web-php/`. Halaman autentikasi, event, tiket, pesanan, pembayaran, check-in, & rancang skema DB MySQL (`database/eventtix.sql`). |
| `user-event` | **2. Pengguna & Event (Java)** | `User.java` (abstract), `Peserta.java`, `Panitia.java`, `Event.java`, dan **`Main.java`** (demo alur lengkap di console). |
| `tiket-checkin` | **3. Tiket & Check-In (Java)** | `Tiket.java` (abstract), `TiketReguler.java`, `TiketVIP.java`, `CheckIn.java`. *(**Penting:** Kerangka `Tiket.java` wajib beres **PALING AWAL** karena dipakai modul lain).* |
| `pesanan-promo` | **4. Pesanan & Promo (Java)** | `Pesanan.java`, `KodePromo.java`. Setup repo Git + struktur folder, merge semua kode ke `main`, & revisi Class Diagram v2. |
| `pembayaran` | **5. Pembayaran (Java)** | `Pembayaran.java` (abstract), `PembayaranTransfer.java`, `PembayaranEWallet.java`. Susun dokumen laporan Tahap 2 & pengumpulan tugas. |

---

## Struktur directory Proyek

```text
EventTix-PBO/
├── java-console/
│   └── src/
│       └── eventtix/          # Semua file .java ada di sini
├── web-php/
│   ├── assets/                # CSS, JS, Images
│   ├── config/                # Koneksi database (database.php)
│   ├── database/              # File export SQL (eventtix.sql)
│   └── index.php              # Entry point Web PHP
└── docs/
    └── class_diagram.jpeg     # Referensi Class Diagram
```

Untuk struktur directory menyesuaikan aja, `README.md` bakal mengikuti hasil akhir

---


## Cara Kerjain & Upload Kode (Panduan Git)

Buka terminal, masukin command  di bawah (tinggal *copy-paste*):

### 1. Clone Repo (Cuma dilakukan sekali di awal)

```bash
git clone https://github.com/Tederby/EventTix
cd EventTix

```

### 2. Pindah ke Branch Masing-Masing

Pilih **salah satu** sesuai bagian kalian:

```bash
git checkout web-php
# ATAU
git checkout user-event
# ATAU
git checkout tiket-checkin
# ATAU
git checkout pesanan-promo
# ATAU
git checkout pembayaran

```

*(Cek pakai perintah `git branch`, pastikan tanda bintang `*` sudah berada di nama branch kalian, **bukan** di `main`).*

### 3. Simpan & Upload Kerjaan ke GitHub

Kalau sudah selesai ngoding di bagian masing-masing, jalankan 3 perintah ini:

```bash
git add .
git commit -m "Pesan commit"
git push

```

**`git push` saja karena tiap branch push origin-nya sudah ke masing-masing branch. Tapi kalau memang mau lebih yakin bisa pake ini:**
```bash
git push origin <nama_branch>

# Contoh
git push origin tiket-checkin
```
---

## 📊 Referensi Desain

* 🖼️ **[Lihat Class Diagram (Gambar)](https://www.google.com/search?q=docs/class_diagram.jpeg)**


---
