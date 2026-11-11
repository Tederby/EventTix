<?php
// web/classes/Pesanan.php
require_once __DIR__ . '/Tiket.php';
require_once __DIR__ . '/KodePromo.php';
require_once __DIR__ . '/Pembayaran.php';

class Pesanan {
    public const STATUS_PENDING = 'Pending';
    public const STATUS_LUNAS = 'Lunas';
    public const STATUS_DIBATALKAN = 'Dibatalkan';
    public const STATUS_KONFIRMASI = 'KONFIRMASI';
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_BATAL = 'BATAL';

    private string $idPesanan;
    private string $idPeserta;
    private string $idEvent;
    private string $tanggalPesan;
    private string $statusPesanan; // 'KONFIRMASI', 'DRAFT', 'BATAL' / 'Lunas', 'Pending'
    private array $daftarTiket = [];
    private ?KodePromo $kodePromo = null;
    private ?Pembayaran $pembayaran = null;

    public function __construct(
        string $idPesanan,
        string $idPeserta,
        string $idEvent,
        string $tanggalPesan = '',
        string $statusPesanan = self::STATUS_DRAFT
    ) {
        if (empty($idPesanan)) {
            throw new InvalidArgumentException("ID pesanan tidak boleh kosong.");
        }
        $this->idPesanan = trim($idPesanan);
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

    public function hitungSubtotal(): float {
        $subtotal = 0.0;
        foreach ($this->daftarTiket as $tiket) {
            $subtotal += $tiket->hitungHarga();
        }
        return $subtotal;
    }

    public function getSubtotal(): float {
        return $this->hitungSubtotal();
    }

    public function getPotonganPromo(): float {
        if ($this->kodePromo !== null) {
            return $this->kodePromo->hitungPotongan($this->hitungSubtotal());
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
        $total = $this->hitungSubtotal() - $this->getPotonganPromo() + $this->getBiayaAdmin();
        return max(0.0, $total);
    }

    public function setPembayaran(Pembayaran $pembayaran): void {
        $this->pembayaran = $pembayaran;
    }

    public function konfirmasi(): void {
        $this->statusPesanan = self::STATUS_KONFIRMASI;
        if ($this->kodePromo !== null) {
            $this->kodePromo->kurangiKuota();
        }
        if ($this->pembayaran !== null) {
            $this->pembayaran->prosesPembayaran();
        }
        foreach ($this->daftarTiket as $tiket) {
            $tiket->setStatusTiket('Terjual');
        }
    }

    public function batalkan(): void {
        $this->statusPesanan = self::STATUS_BATAL;
        foreach ($this->daftarTiket as $tiket) {
            $tiket->setStatusTiket('Tersedia');
        }
    }

    public function getIdPesanan(): string { return $this->idPesanan; }
    public function getIdPeserta(): string { return $this->idPeserta; }
    public function getIdEvent(): string { return $this->idEvent; }
    public function getTanggalPesan(): string { return $this->tanggalPesan; }
    public function setTanggalPesan(string $tanggal): void { $this->tanggalPesan = $tanggal; }
    public function getStatusPesanan(): string { return $this->statusPesanan; }
    public function setStatusPesanan(string $status): void { $this->statusPesanan = $status; }
    public function getDaftarTiket(): array { return $this->daftarTiket; }
    public function setDaftarTiket(array $daftarTiket): void { $this->daftarTiket = $daftarTiket; }
    public function getKodePromo(): ?KodePromo { return $this->kodePromo; }
    public function setKodePromo(?KodePromo $promo): void { $this->kodePromo = $promo; }
    public function getPembayaran(): ?Pembayaran { return $this->pembayaran; }
}
