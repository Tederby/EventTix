<?php
// web/classes/KodePromo.php
class KodePromo {
    private string $kode;
    private float $persenDiskon; // Contoh 0.20 untuk 20%
    private float $maksPotongan; // Batas atas diskon (Rp)
    private string $masaBerlaku; // Format Y-m-d
    private int $kuotaPemakaian;
    private string $keterangan;

    public function __construct(
        string $kode,
        float $persenDiskon,
        float $maksPotongan,
        string $masaBerlaku,
        int $kuotaPemakaian,
        string $keterangan = ''
    ) {
        $this->kode = strtoupper($kode);
        $this->persenDiskon = $persenDiskon;
        $this->maksPotongan = $maksPotongan;
        $this->masaBerlaku = $masaBerlaku;
        $this->kuotaPemakaian = $kuotaPemakaian;
        $this->keterangan = $keterangan;
    }

    public function isValid(): bool {
        $today = date('Y-m-d');
        return ($this->kuotaPemakaian > 0) && ($this->masaBerlaku >= $today);
    }

    public function hitungPotongan(float $subtotal): float {
        if (!$this->isValid()) {
            return 0.0;
        }
        $potongan = $subtotal * $this->persenDiskon;
        return min($potongan, $this->maksPotongan);
    }

    public function kurangiKuota(): void {
        if ($this->kuotaPemakaian > 0) {
            $this->kuotaPemakaian--;
        }
    }

    public function getKode(): string { return $this->kode; }
    public function getPersenDiskon(): float { return $this->persenDiskon; }
    public function getMaksPotongan(): float { return $this->maksPotongan; }
    public function getMasaBerlaku(): string { return $this->masaBerlaku; }
    public function getKuotaPemakaian(): int { return $this->kuotaPemakaian; }
    public function getKeterangan(): string { return $this->keterangan; }
}

