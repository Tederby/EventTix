<?php
// web/classes/Peserta.php
require_once __DIR__ . '/User.php';

class Peserta extends User {
    private string $nim;
    private string $noTelepon;

    public function __construct(string $idUser, string $nama, string $email, string $password, string $nim, string $noTelepon) {
        parent::__construct($idUser, $nama, $email, $password);
        $this->nim = $nim;
        $this->noTelepon = $noTelepon;
    }

    public function getNim(): string { return $this->nim; }
    public function getNoTelepon(): string { return $this->noTelepon; }

    public function getRole(): string {
        return 'Peserta';
    }
}

