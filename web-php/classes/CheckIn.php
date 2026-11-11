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
    private ?Tiket $tiket = null;

    public function __construct(
        string $idCheckIn,
        string|Tiket $kodeOrTiket,
        string $statusValidasi = 'MENUNGGU',
        string $catatan = '',
        string $namaPetugas = 'Panitia Pintu Masuk',
        string $waktuCheckIn = ''
    ) {
        $this->idCheckIn = $idCheckIn;
        if ($kodeOrTiket instanceof Tiket) {
            $this->tiket = $kodeOrTiket;
            $this->kodeTiket = $kodeOrTiket->getKodeTiket();
        } else {
            $this->kodeTiket = $kodeOrTiket;
        }
        $this->statusValidasi = $statusValidasi;
        $this->catatan = $catatan;
        $this->namaPetugas = $namaPetugas;
        $this->waktuCheckIn = $waktuCheckIn ?: date('Y-m-d H:i:s');
    }

    public function validasiTiket(?Tiket $tiket = null): bool {
        $target = $tiket ?? $this->tiket;
        if ($target === null) {
            $this->statusValidasi = 'DITOLAK';
            $this->catatan = 'Tiket tidak ditemukan dalam sistem.';
            $this->waktuCheckIn = date('Y-m-d H:i:s');
            return false;
        }

        $status = strtoupper($target->getStatusTiket());
        if ($status === 'TERPAKAI') {
            $this->statusValidasi = 'DITOLAK';
            $this->catatan = 'Tiket ini sudah pernah digunakan untuk check-in sebelumnya.';
            $this->waktuCheckIn = date('Y-m-d H:i:s');
            return false;
        }

        if ($status === 'AKTIF' || $status === 'TERJUAL') {
            $this->statusValidasi = 'VALID';
            $this->catatan = 'Tiket terverifikasi asli dan peserta dipersilakan masuk ke ruangan acara.';
            $target->tandaiTerpakai();
            $this->waktuCheckIn = date('Y-m-d H:i:s');
            return true;
        }

        $this->statusValidasi = 'DITOLAK';
        $this->catatan = 'Status tiket ' . htmlspecialchars($target->getStatusTiket()) . ' tidak memenuhi syarat akses masuk.';
        $this->waktuCheckIn = date('Y-m-d H:i:s');
        return false;
    }

    public function catat(): void {
        // Log pencatatan check-in
    }

    public function getIdCheckIn(): string { return $this->idCheckIn; }
    public function getKodeTiket(): string { return $this->kodeTiket; }
    public function getStatusValidasi(): string { return $this->statusValidasi; }
    public function getCatatan(): string { return $this->catatan; }
    public function getNamaPetugas(): string { return $this->namaPetugas; }
    public function getWaktuCheckIn(): string { return $this->waktuCheckIn; }
    public function getTiket(): ?Tiket { return $this->tiket; }
}
