<?php
// web/classes/Peserta.php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Event.php';
require_once __DIR__ . '/Pesanan.php';
require_once __DIR__ . '/TiketReguler.php';

class Peserta extends User {
    private string $nim;
    private string $noTelepon;
    private array $riwayatPesanan = [];

    public function __construct(
        string $idUser,
        string $nama,
        string $email,
        string $password,
        string $nim,
        string $noTelepon,
        array $riwayatPesanan = []
    ) {
        parent::__construct($idUser, $nama, $email, $password);
        $this->nim = $nim;
        $this->noTelepon = $noTelepon;
        $this->riwayatPesanan = $riwayatPesanan;
    }

    public function lihatEvent(?array $daftarEvent = null): array {
        return $daftarEvent ?? [];
    }

    public function buatPesanan(Event $event, int $jumlah): ?Pesanan {
        if ($jumlah <= 0) {
            return null;
        }

        if (!$event->cekKetersediaan($jumlah)) {
            return null;
        }

        $idPesanan = 'ORD-' . date('Ymd') . '-' . substr(strtoupper(uniqid()), -4);
        $pesananBaru = new Pesanan($idPesanan, $this->getIdUser(), $event->getIdEvent(), date('Y-m-d H:i:s'), 'DRAFT');

        for ($i = 1; $i <= $jumlah; $i++) {
            $kodeTiket = 'ETX-' . strtoupper(substr($event->getIdEvent(), 0, 3)) . '-' . mt_rand(100, 999);
            $tiket = new TiketReguler($kodeTiket, $event->getHargaDasar(), 'AKTIF', $event->getIdEvent(), $idPesanan);
            $pesananBaru->tambahTiket($tiket);
        }

        $this->riwayatPesanan[] = $pesananBaru;
        return $pesananBaru;
    }

    public function lihatRiwayatPesanan(): array {
        return $this->riwayatPesanan;
    }

    public function tambahRiwayatPesanan(Pesanan $pesanan): void {
        $this->riwayatPesanan[] = $pesanan;
    }

    public function getRole(): string {
        return 'Peserta';
    }

    public function getNim(): string { return $this->nim; }
    public function setNim(string $nim): void { $this->nim = $nim; }
    public function getNoTelepon(): string { return $this->noTelepon; }
    public function setNoTelepon(string $noTelepon): void { $this->noTelepon = $noTelepon; }
    public function getRiwayatPesanan(): array { return $this->riwayatPesanan; }
    public function setRiwayatPesanan(array $riwayatPesanan): void { $this->riwayatPesanan = $riwayatPesanan; }
}
