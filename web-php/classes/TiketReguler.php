<?php
// web/classes/TiketReguler.php
require_once __DIR__ . '/Tiket.php';

class TiketReguler extends Tiket {
    public function hitungHarga(): float {
        return $this->hargaDasar;
    }

    public function getFasilitas(): array {
        return [
            'Akses kursi reguler di area utama seminar',
            'Sertifikat digital resmi (e-certificate)',
            'Materi seminar & modul presentasi digital',
            'Paket snack dan minuman ringan'
        ];
    }

    public function getJenis(): string {
        return 'Reguler';
    }
}

