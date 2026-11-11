<?php
// web/classes/Event.php
require_once __DIR__ . '/Tiket.php';

class Event {
    private string $idEvent;
    private string $namaEvent;
    private string $deskripsi;
    private string $tanggal;
    private string $lokasi;
    private int $kuota;
    private int $tiketTerjual;
    private string $statusEvent; // 'Buka', 'Tutup', 'Penuh'
    private float $hargaDasar;
    private string $kategori;
    private array $daftarTiket = [];

    public function __construct(
        string $idEvent,
        string $namaEvent,
        string $deskripsi,
        string $tanggal,
        string $lokasi,
        int $kuota,
        int $tiketTerjual = 0,
        string $statusEvent = 'Buka',
        float $hargaDasar = 45000,
        string $kategori = 'Seminar',
        array $daftarTiket = []
    ) {
        $this->idEvent = $idEvent;
        $this->namaEvent = $namaEvent;
        $this->deskripsi = $deskripsi;
        $this->tanggal = $tanggal;
        $this->lokasi = $lokasi;
        $this->kuota = $kuota;
        $this->tiketTerjual = $tiketTerjual;
        $this->statusEvent = $statusEvent;
        $this->hargaDasar = $hargaDasar;
        $this->kategori = $kategori;
        $this->daftarTiket = $daftarTiket;
    }

    public function cekKetersediaan(int $jumlah): bool {
        if ($jumlah <= 0) return false;
        return (strtolower($this->statusEvent) === 'buka') && (($this->tiketTerjual + $jumlah) <= $this->kuota);
    }

    public function kurangiKuota(int $jumlah): void {
        if ($jumlah > 0) {
            $this->tiketTerjual += $jumlah;
            if ($this->isPenuh()) {
                $this->tutupPenjualan();
            }
        }
    }

    public function isPenuh(): bool {
        return $this->tiketTerjual >= $this->kuota;
    }

    public function getSisaKuota(): int {
        return max(0, $this->kuota - $this->tiketTerjual);
    }

    public function getPersentaseTerisi(): float {
        if ($this->kuota <= 0) return 100.0;
        return round(($this->tiketTerjual / $this->kuota) * 100, 1);
    }

    public function tutupPenjualan(): void {
        $this->statusEvent = 'Tutup';
    }

    public function bukaPenjualan(): void {
        if (!$this->isPenuh()) {
            $this->statusEvent = 'Buka';
        }
    }

    public function tambahTiket(Tiket $tiket): void {
        $this->daftarTiket[] = $tiket;
    }

    // --- Getter dan Setter ---

    public function getIdEvent(): string { return $this->idEvent; }
    public function setIdEvent(string $idEvent): void { $this->idEvent = $idEvent; }

    public function getNamaEvent(): string { return $this->namaEvent; }
    public function setNamaEvent(string $namaEvent): void { $this->namaEvent = $namaEvent; }

    public function getDeskripsi(): string { return $this->deskripsi; }
    public function setDeskripsi(string $deskripsi): void { $this->deskripsi = $deskripsi; }

    public function getTanggal(): string { return $this->tanggal; }
    public function setTanggal(string $tanggal): void { $this->tanggal = $tanggal; }

    public function getLokasi(): string { return $this->lokasi; }
    public function setLokasi(string $lokasi): void { $this->lokasi = $lokasi; }

    public function getKuota(): int { return $this->kuota; }
    public function setKuota(int $kuota): void {
        $this->kuota = $kuota;
        if ($this->isPenuh()) {
            $this->tutupPenjualan();
        }
    }

    public function getTiketTerjual(): int { return $this->tiketTerjual; }
    public function setTiketTerjual(int $tiketTerjual): void {
        $this->tiketTerjual = $tiketTerjual;
        if ($this->isPenuh()) {
            $this->tutupPenjualan();
        }
    }

    public function getStatusEvent(): string { return $this->statusEvent; }
    public function setStatusEvent(string $statusEvent): void { $this->statusEvent = $statusEvent; }

    public function getHargaDasar(): float { return $this->hargaDasar; }
    public function setHargaDasar(float $hargaDasar): void {
        if ($hargaDasar < 0.0) {
            throw new InvalidArgumentException("Harga dasar event tidak boleh negatif.");
        }
        $this->hargaDasar = $hargaDasar;
    }

    public function getKategori(): string { return $this->kategori; }
    public function setKategori(string $kategori): void { $this->kategori = $kategori; }

    public function getDaftarTiket(): array { return $this->daftarTiket; }
    public function setDaftarTiket(array $daftarTiket): void { $this->daftarTiket = $daftarTiket; }
}
