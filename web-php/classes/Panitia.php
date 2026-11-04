<?php
// web/classes/Panitia.php
require_once __DIR__ . '/User.php';

class Panitia extends User {
    private string $jabatan;

    public function __construct(string $idUser, string $nama, string $email, string $password, string $jabatan) {
        parent::__construct($idUser, $nama, $email, $password);
        $this->jabatan = $jabatan;
    }

    public function getJabatan(): string { return $this->jabatan; }

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
        return new KodePromo($kode, $persenDiskon, $maksPotongan, $masaBerlaku, $kuota, $keterangan);
    }

    public function buatEvent(
        string $idEvent,
        string $namaEvent,
        string $deskripsi,
        string $tanggal,
        string $lokasi,
        int $kuota,
        float $hargaDasar = 45000,
        string $kategori = 'Seminar'
    ): Event {
        return new Event($idEvent, $namaEvent, $deskripsi, $tanggal, $lokasi, $kuota, 0, 'Buka', $hargaDasar, $kategori);
    }
}

