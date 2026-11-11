<?php
// web/classes/PembayaranEWallet.php
require_once __DIR__ . '/Pembayaran.php';

class PembayaranEWallet extends Pembayaran {
    private string $providerWallet;
    private string $noAkun;

    public function __construct(
        string $idPembayaran,
        float $jumlah,
        string $providerWallet = 'GoPay',
        string $noAkun = '0812-3456-7890',
        string $waktuBayar = '',
        string $statusBayar = self::STATUS_LUNAS
    ) {
        parent::__construct($idPembayaran, $jumlah, $waktuBayar, $statusBayar);
        $this->providerWallet = $providerWallet;
        $this->noAkun = $noAkun;
    }

    public function hitungBiayaAdmin(): float {
        // Biaya admin e-wallet: 1% dari total pesanan, minimal Rp 1.000
        return max(1000.0, round($this->jumlah * 0.01));
    }

    public function prosesPembayaran(): bool {
        $this->statusBayar = self::STATUS_LUNAS;
        return true;
    }

    public function getProviderWallet(): string { return $this->providerWallet; }
    public function getNoAkun(): string { return $this->noAkun; }

    public function getMetodeNama(): string {
        return 'E-Wallet (' . $this->providerWallet . ')';
    }

    public function getKeteranganInstruksi(): string {
        return 'Selesaikan otorisasi pembayaran melalui aplikasi ' . $this->providerWallet . ' ke nomor ' . $this->noAkun . '.';
    }
}
