<?php
// web/classes/TiketVIP.php
require_once __DIR__ . '/Tiket.php';

class TiketVIP extends Tiket {
    private float $biayaLayananVIP;
    private string $nomorKursi;

    public function __construct(
        string $kodeTiket,
        float $hargaDasar,
        float $biayaLayananVIP = 35000,
        string $nomorKursi = 'VIP-01',
        string $statusTiket = 'AKTIF',
        string $idEvent = '',
        string $idPesanan = ''
    ) {
        parent::__construct($kodeTiket, $hargaDasar, $statusTiket, $idEvent, $idPesanan);
        $this->biayaLayananVIP = $biayaLayananVIP;
        $this->nomorKursi = $nomorKursi;
    }

    public function hitungHarga(): float {
        return $this->hargaDasar + $this->biayaLayananVIP;
    }

    public function getBiayaLayananVIP(): float {
        return $this->biayaLayananVIP;
    }

    public function getNomorKursi(): string {
        return $this->nomorKursi;
    }

    public function setNomorKursi(string $nomorKursi): void {
        $this->nomorKursi = $nomorKursi;
    }

    public function getFasilitas(): array {
        return [
            'Akses kursi baris depan khusus (Nomor kursi terdaftar)',
            'Sertifikat cetak fisik hardcover & e-certificate',
            'Merchandise kit eksklusif (tote bag kanvas, buku catatan, pin enamel)',
            'Akses lounge VIP & makan siang prasmanan bersama narasumber',
            'Sesi tanya jawab prioritas saat diskusi panel'
        ];
    }

    public function getJenis(): string {
        return 'VIP';
    }
}

