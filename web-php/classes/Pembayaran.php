<?php
// web/classes/Pembayaran.php
abstract class Pembayaran {
    public const STATUS_MENUNGGU = 'MENUNGGU';
    public const STATUS_BUKTI_TERVERIFIKASI = 'BUKTI_TERVERIFIKASI';
    public const STATUS_BERHASIL = 'BERHASIL';
    public const STATUS_GAGAL = 'GAGAL';
    public const STATUS_LUNAS = 'LUNAS';

    protected string $idPembayaran;
    protected float $jumlah;
    protected string $waktuBayar;
    protected string $statusBayar;

    public function __construct(
        string $idPembayaran,
        float $jumlah,
        string $waktuBayar = '',
        string $statusBayar = self::STATUS_LUNAS
    ) {
        if (empty($idPembayaran)) {
            throw new InvalidArgumentException("ID pembayaran tidak boleh kosong.");
        }
        if ($jumlah <= 0) {
            throw new InvalidArgumentException("Jumlah pembayaran harus lebih dari 0.");
        }
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

    public function setStatusBayar(string $statusBayar): void {
        $this->statusBayar = $statusBayar;
    }

    public function getIdPembayaran(): string { return $this->idPembayaran; }
    public function getJumlah(): float { return $this->jumlah; }
    public function getWaktuBayar(): string { return $this->waktuBayar; }
}
