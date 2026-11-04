<?php
// web/classes/Tiket.php
abstract class Tiket {
    protected string $kodeTiket;
    protected float $hargaDasar;
    protected string $statusTiket; // 'AKTIF', 'TERPAKAI', 'DIBATALKAN'
    protected string $idEvent;
    protected string $idPesanan;

    public function __construct(
        string $kodeTiket,
        float $hargaDasar,
        string $statusTiket = 'AKTIF',
        string $idEvent = '',
        string $idPesanan = ''
    ) {
        $this->kodeTiket = $kodeTiket;
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
        $this->statusTiket = 'TERPAKAI';
    }

    public function setStatusTiket(string $status): void {
        $this->statusTiket = $status;
    }
}

