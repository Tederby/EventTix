-- ====================================================================
-- EventTix Database Schema (MySQL)
-- Sistem Ticketing Event & Seminar Kampus
-- Mata Kuliah Pemrograman Berorientasi Objek - Kelompok 6
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `eventtix` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eventtix`;

-- --------------------------------------------------------------------
-- 1. Tabel Users (Hierarki User: Peserta & Panitia)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id_user` VARCHAR(20) NOT NULL PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Peserta', 'Panitia') NOT NULL,
    `nim` VARCHAR(20) DEFAULT NULL,
    `no_telepon` VARCHAR(20) DEFAULT NULL,
    `jabatan` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Seed User Demo
INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `nim`, `no_telepon`, `jabatan`) VALUES
('USR-01', 'Muhammad Isya Asyhari', 'isya@mahasiswa.ac.id', 'password123', 'Peserta', '21120124130051', '0812-3456-7890', NULL),
('ADM-01', 'Panitia Registrasi Kampus', 'panitia@kampus.ac.id', 'panitia123', 'Panitia', NULL, NULL, 'Koordinator Registrasi & Pintu Masuk')
ON DUPLICATE KEY UPDATE `nama`=VALUES(`nama`);

-- --------------------------------------------------------------------
-- 2. Tabel Events (Class Event)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
    `id_event` VARCHAR(20) NOT NULL PRIMARY KEY,
    `nama_event` VARCHAR(150) NOT NULL,
    `deskripsi` TEXT NOT NULL,
    `tanggal` DATETIME NOT NULL,
    `lokasi` VARCHAR(150) NOT NULL,
    `kuota` INT NOT NULL DEFAULT 50,
    `tiket_terjual` INT NOT NULL DEFAULT 0,
    `status_event` ENUM('Buka', 'Tutup', 'Penuh') NOT NULL DEFAULT 'Buka',
    `harga_dasar` DECIMAL(12,2) NOT NULL DEFAULT 40000.00,
    `kategori` VARCHAR(50) NOT NULL DEFAULT 'Seminar',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `events` (`id_event`, `nama_event`, `deskripsi`, `tanggal`, `lokasi`, `kuota`, `tiket_terjual`, `status_event`, `harga_dasar`, `kategori`) VALUES
('EVT-01', 'Seminar Nasional Rekayasa Perangkat Lunak & Kecerdasan Artifisial 2026', 'Seminar tahunan eksplorasi arsitektur microservices modern, cloud native computing, dan implementasi machine learning enterprise.', '2026-11-28 08:30:00', 'Auditorium Gedung Prof. Soedarto, Tembalang', 100, 85, 'Buka', 45000.00, 'Seminar Nasional'),
('EVT-02', 'Workshop UI/UX Design System & Prototyping Interaktif', 'Pelatihan intensif satu hari merancang design token konsisten, micro-interaction responsif, dan pengujian kegunaan purwarupa.', '2026-12-05 09:00:00', 'Lab Komputer Rekayasa Perangkat Lunak Gedung B', 40, 28, 'Tutup', 35000.00, 'Workshop Hands-on'),
('EVT-03', 'Hackathon Mahasiswa Solusi Digital Berkelanjutan 2026', 'Kompetisi pembuatan aplikasi 24 jam non-stop menyelesaikan permasalahan mobilitas kampus dan efisiensi energi fasilitas universitas.', '2026-12-19 08:00:00', 'Hall Gedung Robotika & Otomasi Lantai 2', 60, 54, 'Tutup', 60000.00, 'Kompetisi')
ON DUPLICATE KEY UPDATE `nama_event`=VALUES(`nama_event`);

-- --------------------------------------------------------------------
-- 3. Tabel Kode Promo (Class KodePromo)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kode_promo` (
    `id_promo` INT AUTO_INCREMENT PRIMARY KEY,
    `kode` VARCHAR(30) NOT NULL UNIQUE,
    `persen_diskon` DECIMAL(4,2) NOT NULL, -- Contoh 0.20 untuk 20%
    `maks_potongan` DECIMAL(12,2) NOT NULL,
    `masa_berlaku` DATE NOT NULL,
    `kuota_pemakaian` INT NOT NULL DEFAULT 50,
    `keterangan` VARCHAR(255) DEFAULT '',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `kode_promo` (`kode`, `persen_diskon`, `maks_potongan`, `masa_berlaku`, `kuota_pemakaian`, `keterangan`) VALUES
('MHSHEMAT', 0.20, 20000.00, '2026-12-31', 45, 'Diskon 20% khusus mahasiswa hingga Rp 20.000'),
('SEMINARTEKNO', 0.15, 15000.00, '2026-12-31', 30, 'Potongan 15% untuk semua kategori tiket seminar')
ON DUPLICATE KEY UPDATE `kode`=VALUES(`kode`);

-- --------------------------------------------------------------------
-- 4. Tabel Pesanan (Class Pesanan)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pesanan` (
    `id_pesanan` VARCHAR(30) NOT NULL PRIMARY KEY,
    `id_peserta` VARCHAR(20) NOT NULL,
    `id_event` VARCHAR(20) NOT NULL,
    `tanggal_pesan` DATETIME NOT NULL,
    `status_pesanan` ENUM('DRAFT', 'KONFIRMASI', 'BATAL') NOT NULL DEFAULT 'DRAFT',
    `kode_promo` VARCHAR(30) DEFAULT NULL,
    `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `potongan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `biaya_admin` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_bayar` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status_bayar` ENUM('PENDING', 'LUNAS', 'GAGAL') NOT NULL DEFAULT 'PENDING',
    FOREIGN KEY (`id_peserta`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
    FOREIGN KEY (`id_event`) REFERENCES `events`(`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 5. Tabel Pembayaran (Hierarki Pembayaran: Transfer & E-Wallet)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pembayaran` (
    `id_pembayaran` VARCHAR(30) NOT NULL PRIMARY KEY,
    `id_pesanan` VARCHAR(30) NOT NULL,
    `metode` ENUM('transfer', 'ewallet') NOT NULL,
    `nama_bank` VARCHAR(50) DEFAULT NULL,
    `no_rekening` VARCHAR(50) DEFAULT NULL,
    `provider_ewallet` VARCHAR(50) DEFAULT NULL,
    `no_hp` VARCHAR(30) DEFAULT NULL,
    `nominal` DECIMAL(12,2) NOT NULL,
    `biaya_admin` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status_bayar` ENUM('LUNAS', 'PENDING', 'BATAL') NOT NULL DEFAULT 'LUNAS',
    `tanggal_bayar` DATETIME NOT NULL,
    FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan`(`id_pesanan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 6. Tabel Tiket (Hierarki Tiket: Reguler & VIP)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tiket` (
    `id_tiket` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_tiket` VARCHAR(30) NOT NULL UNIQUE,
    `id_pesanan` VARCHAR(30) NOT NULL,
    `id_event` VARCHAR(20) NOT NULL,
    `jenis_tiket` ENUM('Reguler', 'VIP') NOT NULL DEFAULT 'Reguler',
    `harga_dasar` DECIMAL(12,2) NOT NULL,
    `biaya_layanan_vip` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `nomor_kursi` VARCHAR(20) DEFAULT NULL,
    `status_tiket` ENUM('AKTIF', 'TERPAKAI', 'BATAL') NOT NULL DEFAULT 'AKTIF',
    `waktu_diterbitkan` DATETIME NOT NULL,
    FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan`(`id_pesanan`) ON DELETE CASCADE,
    FOREIGN KEY (`id_event`) REFERENCES `events`(`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 7. Tabel Check-In (Class CheckIn)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `check_in` (
    `id_checkin` INT AUTO_INCREMENT PRIMARY KEY,
    `id_tiket` VARCHAR(30) NOT NULL,
    `id_panitia` VARCHAR(20) NOT NULL,
    `waktu_checkin` DATETIME NOT NULL,
    `status_checkin` ENUM('VALID', 'DITOLAK') NOT NULL,
    `catatan` VARCHAR(255) DEFAULT '',
    FOREIGN KEY (`id_panitia`) REFERENCES `users`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
