<?php
// web/classes/CheckIn.php
require_once __DIR__ . '/Tiket.php';

class CheckIn {
    private string $idCheckIn;
    private string $waktuCheckIn;
    private string $statusValidasi; // 'VALID', 'DITOLAK'
    private string $kodeTiket;
    private string $catatan;
    private string $namaPetugas;

    public function __construct(
        string $idCheckIn,
        string $kodeTiket,
        string $statusValidasi = 'MENUNGGU',
        string $catatan = '',
        string $namaPetugas = 'Panitia Pintu Masuk',
        string $waktuCheckIn = ''
    ) {
        $this->idCheckIn = $idCheckIn;
        $this->kodeTiket = $kodeTiket;
        $this->statusValidasi = $statusValidasi;
        $this->catatan = $catatan;
        $this->namaPetugas = $namaPetugas;
        $this->waktuCheckIn = $waktuCheckIn ?: date('Y-m-d H:i:s');
    }

    public function validasiTiket(Tiket $tiket): bool {
        if ($tiket->getStatusTiket() === 'AKTIF') {
            $this->statusValidasi = 'VALID';
            $this->catatan = 'Tiket terverifikasi asli dan peserta dipersilakan masuk ke ruangan acara.';
            $tiket->tandaiTerpakai();
            $this->waktuCheckIn = date('Y-m-d H:i:s');
            return true;
        }

        if ($tiket->getStatusTiket() === 'TERPAKAI') {
            $this->statusValidasi = 'DITOLAK';
            $this->catatan = 'Tiket ini sudah pernah digunakan untuk check-in sebelumnya.';
            $this->waktuCheckIn = date('Y-m-d H:i:s');
            return false;
        }

        $this->statusValidasi = 'DITOLAK';
        $this->catatan = 'Status tiket ' . htmlspecialchars($tiket->getStatusTiket()) . ' tidak memenuhi syarat akses masuk.';
        $this->waktuCheckIn = date('Y-m-d H:i:s');
        return false;
    }

    public function catat(): void {
        // Log pencatatan kehadiran
    }

    public function getIdCheckIn(): string { return $this->idCheckIn; }
    public function getKodeTiket(): string { return $this->kodeTiket; }
    public function getStatusValidasi(): string { return $this->statusValidasi; }
    public function getCatatan(): string { return $this->catatan; }
    public function getNamaPetugas(): string { return $this->namaPetugas; }
    public function getWaktuCheckIn(): string { return $this->waktuCheckIn; }
}

