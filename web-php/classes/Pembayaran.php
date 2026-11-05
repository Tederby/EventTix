<?php
// web/classes/Pembayaran.php
abstract class Pembayaran {
    protected string $idPembayaran;
    protected float $jumlah;
    protected string $waktuBayar;
    protected string $statusBayar; // 'LUNAS', 'MENUNGGU', 'BATAL'

    public function __construct(
        string $idPembayaran,
        float $jumlah,
        string $waktuBayar = '',
        string $statusBayar = 'LUNAS'
    ) {
        $this->idPembayaran = $idPembayaran;
        $this->jumlah = $jumlah;
        $this->waktuBayar = $waktuBayar ?: date('Y-m-d H:i:s');
        $this->statusBayar = $statusBayar;
    }

    abstract public function hitungBiayaAdmin(): float;
    abstract public function prosesPembayaran(): bool;
    abstract public function getMetodeNama(): string;
    abstract public function getKeteranganInstruksi(): string;

    public function getTotalBayar(): float {
        return $this->jumlah + $this->hitungBiayaAdmin();
    }

    public function getStatusBayar(): string {
        return $this->statusBayar;
    }

    public function getIdPembayaran(): string { return $this->idPembayaran; }
    public function getJumlah(): float { return $this->jumlah; }
    public function getWaktuBayar(): string { return $this->waktuBayar; }
}

