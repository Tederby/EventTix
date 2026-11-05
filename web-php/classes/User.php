<?php
// web/classes/User.php
abstract class User {
    protected string $idUser;
    protected string $nama;
    protected string $email;
    protected string $password;

    public function __construct(string $idUser, string $nama, string $email, string $password) {
        $this->idUser = $idUser;
        $this->nama = $nama;
        $this->email = $email;
        $this->password = $password;
    }

    public function login(string $email, string $password): bool {
        return $this->email === $email && $this->password === $password;
    }

    public function logout(): void {
        // Logika logout sesi
    }

    public function ubahPassword(string $lama, string $baru): bool {
        if ($this->password === $lama) {
            $this->password = $baru;
            return true;
        }
        return false;
    }

    abstract public function getRole(): string;

    public function getIdUser(): string { return $this->idUser; }
    public function getNama(): string { return $this->nama; }
    public function setNama(string $nama): void { $this->nama = $nama; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
}

