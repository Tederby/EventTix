<?php
// web/classes/Panitia.php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Event.php';
require_once __DIR__ . '/KodePromo.php';
require_once __DIR__ . '/CheckIn.php';
require_once __DIR__ . '/Tiket.php';

class Panitia extends User {
    private string $jabatan;
    private array $daftarEventDikelola = [];

    public function __construct(
        string $idUser,
        string $nama,
        string $email,
        string $password,
        string $jabatan,
        array $daftarEventDikelola = []
    ) {
        parent::__construct($idUser, $nama, $email, $password);
        $this->jabatan = $jabatan;
        $this->daftarEventDikelola = $daftarEventDikelola;
    }

    public function getJabatan(): string { return $this->jabatan; }
    public function setJabatan(string $jabatan): void { $this->jabatan = $jabatan; }

    public function getDaftarEventDikelola(): array { return $this->daftarEventDikelola; }
    public function setDaftarEventDikelola(array $daftarEventDikelola): void { $this->daftarEventDikelola = $daftarEventDikelola; }

    public function getRole(): string {
        return 'Panitia';
    }

    public function buatPromo(
        string $kode,
        float $persenDiskon,
        float $maksPotongan,
        string $masaBerlaku,
        int $kuota,
        string $keterangan = ''
    ): KodePromo {
        $promo = new KodePromo($kode, $persenDiskon, $maksPotongan, $masaBerlaku, $kuota, $keterangan);
        return $promo;
    }

    public function buatKodePromo(KodePromo $promo): void {
        // Mendaftarkan kode promo
    }

    public function buatEvent(
        string|Event $dataOrId,
        string $namaEvent = '',
        string $deskripsi = '',
        string $tanggal = '',
        string $lokasi = '',
        int $kuota = 0,
        float $hargaDasar = 45000,
        string $kategori = 'Seminar'
    ): Event {
        if ($dataOrId instanceof Event) {
            $this->daftarEventDikelola[] = $dataOrId;
            return $dataOrId;
        }

        $event = new Event($dataOrId, $namaEvent, $deskripsi, $tanggal, $lokasi, $kuota, 0, 'Buka', $hargaDasar, $kategori);
        $this->daftarEventDikelola[] = $event;
        return $event;
    }

    public function kelolaEvent(Event $event): void {
        if ($event->isPenuh()) {
            $event->tutupPenjualan();
        }
    }

    public function lakukanCheckIn(Tiket $tiket): CheckIn {
        $idCheckIn = 'CHK-' . round(microtime(true) * 1000);
        $checkIn = new CheckIn($idCheckIn, $tiket->getKodeTiket(), 'MENUNGGU', '', $this->getNama());
        $checkIn->validasiTiket($tiket);
        $checkIn->catat();
        return $checkIn;
    }

    public function lihatLaporanPenjualan(Event $event): float {
        $total = 0.0;
        $daftarTiket = $event->getDaftarTiket();
        if (!empty($daftarTiket)) {
            foreach ($daftarTiket as $t) {
                if (in_array($t->getStatusTiket(), ['Terjual', 'Terpakai', 'AKTIF', 'TERPAKAI'])) {
                    $total += $t->hitungHarga();
                }
            }
        } else {
            $total = $event->getTiketTerjual() * $event->getHargaDasar();
        }
        return $total;
    }
}
