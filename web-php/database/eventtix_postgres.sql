-- ====================================================================
-- EventTix Database Schema (PostgreSQL - Neon / Supabase)
-- Sistem Ticketing Event & Seminar Kampus
-- Mata Kuliah Pemrograman Berorientasi Objek - Kelompok 6
-- ====================================================================

-- --------------------------------------------------------------------
-- 1. Tabel Users (Hierarki User: Peserta & Panitia)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id_user VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('Peserta', 'Panitia')),
    nim VARCHAR(20) DEFAULT NULL,
    no_telepon VARCHAR(20) DEFAULT NULL,
    jabatan VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data Pengguna Bawaan (Demo)
INSERT INTO users (id_user, nama, email, password, role, nim, no_telepon, jabatan) VALUES
('USR-01', 'Muhammad Isya Asyhari', 'isya@mahasiswa.ac.id', 'password123', 'Peserta', '21120124130051', '0812-3456-7890', NULL),
('ADM-01', 'Panitia Registrasi Kampus', 'panitia@kampus.ac.id', 'panitia123', 'Panitia', NULL, NULL, 'Koordinator Registrasi & Pintu Masuk')
ON CONFLICT (id_user) DO UPDATE SET nama = EXCLUDED.nama;

-- --------------------------------------------------------------------
-- 2. Tabel Events (Class Event)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    id_event VARCHAR(20) PRIMARY KEY,
    nama_event VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    tanggal TIMESTAMP NOT NULL,
    lokasi VARCHAR(150) NOT NULL,
    kuota INT NOT NULL DEFAULT 50,
    tiket_terjual INT NOT NULL DEFAULT 0,
    status_event VARCHAR(20) NOT NULL DEFAULT 'Buka' CHECK (status_event IN ('Buka', 'Tutup', 'Penuh')),
    harga_dasar NUMERIC(12,2) NOT NULL DEFAULT 40000.00,
    kategori VARCHAR(50) NOT NULL DEFAULT 'Seminar',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO events (id_event, nama_event, deskripsi, tanggal, lokasi, kuota, tiket_terjual, status_event, harga_dasar, kategori) VALUES
('EVT-01', 'Seminar Nasional Rekayasa Perangkat Lunak & Kecerdasan Artifisial 2026', 'Seminar tahunan eksplorasi arsitektur microservices modern, cloud native computing, dan implementasi machine learning enterprise bersama pakar industri teknologi.', '2026-11-28 08:30:00', 'Auditorium Gedung Prof. Soedarto, Tembalang', 100, 85, 'Buka', 45000.00, 'Seminar Nasional'),
('EVT-02', 'Workshop UI/UX Design System & Prototyping Interaktif', 'Pelatihan intensif satu hari merancang design token konsisten, micro-interaction responsif, dan pengujian kegunaan purwarupa antarmuka produk digital.', '2026-12-05 09:00:00', 'Lab Komputer Rekayasa Perangkat Lunak Gedung B', 40, 28, 'Buka', 35000.00, 'Workshop Hands-on'),
('EVT-03', 'Hackathon Mahasiswa Solusi Digital Berkelanjutan 2026', 'Kompetisi pembuatan aplikasi 24 jam non-stop menyelesaikan permasalahan mobilitas kampus dan efisiensi energi fasilitas universitas.', '2026-12-19 08:00:00', 'Hall Gedung Robotika & Otomasi Lantai 2', 60, 54, 'Buka', 60000.00, 'Kompetisi')
ON CONFLICT (id_event) DO UPDATE SET nama_event = EXCLUDED.nama_event;

-- --------------------------------------------------------------------
-- 3. Tabel Kode Promo (Class KodePromo)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kode_promo (
    id_promo SERIAL PRIMARY KEY,
    kode VARCHAR(30) NOT NULL UNIQUE,
    persen_diskon NUMERIC(4,2) NOT NULL,
    maks_potongan NUMERIC(12,2) NOT NULL,
    masa_berlaku DATE NOT NULL,
    kuota_pemakaian INT NOT NULL DEFAULT 50,
    keterangan VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO kode_promo (kode, persen_diskon, maks_potongan, masa_berlaku, kuota_pemakaian, keterangan) VALUES
('MHSHEMAT', 0.20, 20000.00, '2026-12-31', 45, 'Diskon 20% khusus mahasiswa hingga Rp 20.000'),
('SEMINARTEKNO', 0.15, 15000.00, '2026-12-31', 30, 'Potongan 15% untuk semua kategori tiket seminar')
ON CONFLICT (kode) DO UPDATE SET persen_diskon = EXCLUDED.persen_diskon;

-- --------------------------------------------------------------------
-- 4. Tabel Pesanan (Class Pesanan)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesanan (
    id_pesanan VARCHAR(30) PRIMARY KEY,
    id_peserta VARCHAR(20) NOT NULL REFERENCES users(id_user) ON DELETE CASCADE,
    id_event VARCHAR(20) NOT NULL REFERENCES events(id_event) ON DELETE CASCADE,
    tanggal_pesan TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status_pesanan VARCHAR(20) NOT NULL DEFAULT 'DRAFT' CHECK (status_pesanan IN ('DRAFT', 'KONFIRMASI', 'BATAL')),
    kode_promo VARCHAR(30) DEFAULT NULL,
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    potongan NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    biaya_admin NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    total_bayar NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    status_bayar VARCHAR(20) NOT NULL DEFAULT 'PENDING' CHECK (status_bayar IN ('PENDING', 'LUNAS', 'GAGAL'))
);

-- --------------------------------------------------------------------
-- 5. Tabel Pembayaran (Hierarki Pembayaran: Transfer & E-Wallet)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pembayaran (
    id_pembayaran VARCHAR(30) PRIMARY KEY,
    id_pesanan VARCHAR(30) NOT NULL REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
    metode VARCHAR(20) NOT NULL CHECK (metode IN ('transfer', 'ewallet')),
    nama_bank VARCHAR(50) DEFAULT NULL,
    no_rekening VARCHAR(50) DEFAULT NULL,
    provider_ewallet VARCHAR(50) DEFAULT NULL,
    no_hp VARCHAR(30) DEFAULT NULL,
    nominal NUMERIC(12,2) NOT NULL,
    biaya_admin NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    status_bayar VARCHAR(20) NOT NULL DEFAULT 'LUNAS' CHECK (status_bayar IN ('LUNAS', 'PENDING', 'BATAL')),
    tanggal_bayar TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 6. Tabel Tiket (Hierarki Tiket: Reguler & VIP)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tiket (
    id_tiket SERIAL PRIMARY KEY,
    kode_tiket VARCHAR(30) NOT NULL UNIQUE,
    id_pesanan VARCHAR(30) NOT NULL REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
    id_event VARCHAR(20) NOT NULL REFERENCES events(id_event) ON DELETE CASCADE,
    jenis_tiket VARCHAR(20) NOT NULL DEFAULT 'Reguler' CHECK (jenis_tiket IN ('Reguler', 'VIP')),
    harga_dasar NUMERIC(12,2) NOT NULL,
    biaya_layanan_vip NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    nomor_kursi VARCHAR(20) DEFAULT NULL,
    status_tiket VARCHAR(20) NOT NULL DEFAULT 'AKTIF' CHECK (status_tiket IN ('AKTIF', 'TERPAKAI', 'BATAL')),
    waktu_diterbitkan TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 7. Tabel Check-In (Class CheckIn)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS check_in (
    id_checkin SERIAL PRIMARY KEY,
    id_tiket VARCHAR(30) NOT NULL,
    id_panitia VARCHAR(20) NOT NULL REFERENCES users(id_user) ON DELETE CASCADE,
    waktu_checkin TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status_checkin VARCHAR(20) NOT NULL CHECK (status_checkin IN ('VALID', 'DITOLAK')),
    catatan VARCHAR(255) DEFAULT ''
);
