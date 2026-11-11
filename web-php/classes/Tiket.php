<?php
// web/classes/Tiket.php
abstract class Tiket {
    public const STATUS_TERSEDIA = 'Tersedia';
    public const STATUS_TERJUAL = 'Terjual';
    public const STATUS_TERPAKAI = 'Terpakai';
    public const STATUS_AKTIF = 'AKTIF';

    protected string $kodeTiket;
    protected float $hargaDasar;
    protected string $statusTiket; // 'Tersedia', 'Terjual', 'Terpakai' / 'AKTIF'
    protected string $idEvent;
    protected string $idPesanan;

    public function __construct(
        string $kodeTiket,
        float $hargaDasar,
        string $statusTiket = self::STATUS_AKTIF,
        string $idEvent = '',
        string $idPesanan = ''
    ) {
        if (empty($kodeTiket)) {
            throw new InvalidArgumentException("Kode tiket tidak boleh kosong.");
        }
        if ($hargaDasar < 0.0) {
            throw new InvalidArgumentException("Harga dasar tiket tidak boleh negatif.");
        }
        $this->kodeTiket = trim($kodeTiket);
        $this->hargaDasar = $hargaDasar;
        $this->statusTiket = $statusTiket;
        $this->idEvent = $idEvent;
        $this->idPesanan = $idPesanan;
    }

    abstract public function hitungHarga(): float;
    abstract public function getFasilitas(): array;
    abstract public function getJenis(): string;

    public function getKodeTiket(): string { return $this->kodeTiket; }
    public function getStatusTiket(): string { return $this->statusTiket; }
    public function getHargaDasar(): float { return $this->hargaDasar; }
    public function getIdEvent(): string { return $this->idEvent; }
    public function getIdPesanan(): string { return $this->idPesanan; }

    public function tandaiTerpakai(): void {
        $this->statusTiket = self::STATUS_TERPAKAI;
    }

    public function setStatusTiket(string $status): void {
        $this->statusTiket = $status;
    }
}
