<?php
// web/classes/PembayaranTransfer.php
require_once __DIR__ . '/Pembayaran.php';

class PembayaranTransfer extends Pembayaran {
    private string $namaBank;
    private string $noRekening;
    private string $atasNama;

    public function __construct(
        string $idPembayaran,
        float $jumlah,
        string $namaBank = 'Bank Mandiri',
        string $noRekening = '137-00-2819201-9',
        string $atasNama = 'EventTix Kampus',
        string $waktuBayar = '',
        string $statusBayar = 'LUNAS'
    ) {
        parent::__construct($idPembayaran, $jumlah, $waktuBayar, $statusBayar);
        $this->namaBank = $namaBank;
        $this->noRekening = $noRekening;
        $this->atasNama = $atasNama;
    }

    public function hitungBiayaAdmin(): float {
        return 2500.0;
    }

    public function prosesPembayaran(): bool {
        $this->statusBayar = 'LUNAS';
        return true;
    }

    public function verifikasiBukti(string $bukti): bool {
        return !empty($bukti);
    }

    public function getNamaBank(): string { return $this->namaBank; }
    public function getNoRekening(): string { return $this->noRekening; }
    public function getAtasNama(): string { return $this->atasNama; }

    public function getMetodeNama(): string {
        return 'Transfer Bank (' . $this->namaBank . ')';
    }

    public function getKeteranganInstruksi(): string {
        return 'Transfer tepat ke rekening ' . $this->namaBank . ' ' . $this->noRekening . ' a/n ' . $this->atasNama . '.';
    }
}

