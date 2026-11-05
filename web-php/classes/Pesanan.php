<?php
// web/classes/Pesanan.php
require_once __DIR__ . '/Tiket.php';
require_once __DIR__ . '/KodePromo.php';
require_once __DIR__ . '/Pembayaran.php';

class Pesanan {
    private string $idPesanan;
    private string $idPeserta;
    private string $idEvent;
    private string $tanggalPesan;
    private string $statusPesanan; // 'KONFIRMASI', 'DRAFT', 'BATAL'
    private array $daftarTiket = [];
    private ?KodePromo $kodePromo = null;
    private ?Pembayaran $pembayaran = null;

    public function __construct(
        string $idPesanan,
        string $idPeserta,
        string $idEvent,
        string $tanggalPesan = '',
        string $statusPesanan = 'DRAFT'
    ) {
        $this->idPesanan = $idPesanan;
        $this->idPeserta = $idPeserta;
        $this->idEvent = $idEvent;
        $this->tanggalPesan = $tanggalPesan ?: date('Y-m-d H:i:s');
        $this->statusPesanan = $statusPesanan;
    }

    public function tambahTiket(Tiket $tiket): void {
        $this->daftarTiket[] = $tiket;
    }

    public function terapkanPromo(KodePromo $promo): bool {
        if ($promo->isValid()) {
            $this->kodePromo = $promo;
            return true;
        }
        return false;
    }

    public function hapusPromo(): void {
        $this->kodePromo = null;
    }

    public function getSubtotal(): float {
        $subtotal = 0.0;
        foreach ($this->daftarTiket as $tiket) {
            $subtotal += $tiket->hitungHarga();
        }
        return $subtotal;
    }

    public function getPotonganPromo(): float {
        if ($this->kodePromo !== null) {
            return $this->kodePromo->hitungPotongan($this->getSubtotal());
        }
        return 0.0;
    }

    public function getBiayaAdmin(): float {
        if ($this->pembayaran !== null) {
            return $this->pembayaran->hitungBiayaAdmin();
        }
        return 0.0;
    }

    public function hitungTotal(): float {
        $total = $this->getSubtotal() - $this->getPotonganPromo() + $this->getBiayaAdmin();
        return max(0.0, $total);
    }

    public function setPembayaran(Pembayaran $pembayaran): void {
        $this->pembayaran = $pembayaran;
    }

    public function konfirmasi(): void {
        $this->statusPesanan = 'KONFIRMASI';
        if ($this->kodePromo !== null) {
            $this->kodePromo->kurangiKuota();
        }
        if ($this->pembayaran !== null) {
            $this->pembayaran->prosesPembayaran();
        }
    }

    public function batalkan(): void {
        $this->statusPesanan = 'BATAL';
    }

    public function getIdPesanan(): string { return $this->idPesanan; }
    public function getIdPeserta(): string { return $this->idPeserta; }
    public function getIdEvent(): string { return $this->idEvent; }
    public function getTanggalPesan(): string { return $this->tanggalPesan; }
    public function getStatusPesanan(): string { return $this->statusPesanan; }
    public function getDaftarTiket(): array { return $this->daftarTiket; }
    public function getKodePromo(): ?KodePromo { return $this->kodePromo; }
    public function getPembayaran(): ?Pembayaran { return $this->pembayaran; }
}

