<?php
// web/classes/Event.php
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
        string $kategori = 'Seminar'
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
    }

    public function cekKetersediaan(int $jumlah): bool {
        return ($this->statusEvent === 'Buka') && (($this->tiketTerjual + $jumlah) <= $this->kuota);
    }

    public function kurangiKuota(int $jumlah): void {
        $this->tiketTerjual += $jumlah;
        if ($this->tiketTerjual >= $this->kuota) {
            $this->statusEvent = 'Penuh';
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
        if ($this->tiketTerjual < $this->kuota) {
            $this->statusEvent = 'Buka';
        } else {
            $this->statusEvent = 'Penuh';
        }
    }

    public function getIdEvent(): string { return $this->idEvent; }
    public function getNamaEvent(): string { return $this->namaEvent; }
    public function getDeskripsi(): string { return $this->deskripsi; }
    public function getTanggal(): string { return $this->tanggal; }
    public function getLokasi(): string { return $this->lokasi; }
    public function getKuota(): int { return $this->kuota; }
    public function getTiketTerjual(): int { return $this->tiketTerjual; }
    public function getStatusEvent(): string { return $this->statusEvent; }
    public function getHargaDasar(): float { return $this->hargaDasar; }
    public function getKategori(): string { return $this->kategori; }
}

