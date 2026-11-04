<?php
// web/classes/DataManager.php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Peserta.php';
require_once __DIR__ . '/Panitia.php';
require_once __DIR__ . '/Event.php';
require_once __DIR__ . '/Tiket.php';
require_once __DIR__ . '/TiketReguler.php';
require_once __DIR__ . '/TiketVIP.php';
require_once __DIR__ . '/KodePromo.php';
require_once __DIR__ . '/Pesanan.php';
require_once __DIR__ . '/Pembayaran.php';
require_once __DIR__ . '/PembayaranTransfer.php';
require_once __DIR__ . '/PembayaranEWallet.php';
require_once __DIR__ . '/CheckIn.php';

class DataManager {
    private static ?DataManager $instance = null;
    private string $filePath;
    private array $data = [];

    private function __construct() {
        $this->filePath = __DIR__ . '/../data/store.json';
        $this->loadData();
    }

    public static function getInstance(): DataManager {
        if (self::$instance === null) {
            self::$instance = new DataManager();
        }
        return self::$instance;
    }

    private function loadData(): void {
        if (!file_exists($this->filePath)) {
            $this->seedInitialData();
            $this->saveData();
        } else {
            $json = file_get_contents($this->filePath);
            $this->data = json_decode($json, true) ?: [];
            if (!isset($this->data['events']) || !is_array($this->data['events'])) {
                $this->seedInitialData();
                $this->saveData();
            }
        }
    }

    private function saveData(): void {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->filePath, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function seedInitialData(): void {
        $this->data = [
            'users' => [
                [
                    'role' => 'Peserta',
                    'idUser' => 'USR-01',
                    'nama' => 'Muhammad Isya Asyhari',
                    'email' => 'isya@mahasiswa.ac.id',
                    'password' => 'password123',
                    'nim' => '21120124130051',
                    'noTelepon' => '0812-3456-7890'
                ],
                [
                    'role' => 'Panitia',
                    'idUser' => 'ADM-01',
                    'nama' => 'Panitia Tiket Gedung',
                    'email' => 'panitia@kampus.ac.id',
                    'password' => 'panitia123',
                    'jabatan' => 'Koordinator Registrasi & Pintu Masuk'
                ]
            ],
            'events' => [
                [
                    'idEvent' => 'EVT-01',
                    'namaEvent' => 'Seminar Nasional Rekayasa Perangkat Lunak & Kecerdasan Artifisial 2026',
                    'deskripsi' => 'Seminar tahunan eksplorasi arsitektur microservices modern, cloud native computing, dan implementasi machine learning enterprise bersama pakar industri teknologi.',
                    'tanggal' => '2026-11-28 08:30:00',
                    'lokasi' => 'Auditorium Gedung Prof. Soedarto, Tembalang',
                    'kuota' => 100,
                    'tiketTerjual' => 82,
                    'statusEvent' => 'Buka',
                    'hargaDasar' => 45000,
                    'kategori' => 'Seminar Nasional'
                ],
                [
                    'idEvent' => 'EVT-02',
                    'namaEvent' => 'Workshop UI/UX Design System & Prototyping Interaktif',
                    'deskripsi' => 'Pelatihan intensif satu hari merancang design token konsisten, micro-interaction responsif, dan pengujian kegunaan purwarupa antarmuka produk digital.',
                    'tanggal' => '2026-12-05 09:00:00',
                    'lokasi' => 'Lab Komputer Rekayasa Perangkat Lunak Gedung B',
                    'kuota' => 40,
                    'tiketTerjual' => 28,
                    'statusEvent' => 'Buka',
                    'hargaDasar' => 35000,
                    'kategori' => 'Workshop Hands-on'
                ],
                [
                    'idEvent' => 'EVT-03',
                    'namaEvent' => 'Hackathon Mahasiswa Solusi Digital Berkelanjutan 2026',
                    'deskripsi' => 'Kompetisi pembuatan aplikasi 24 jam non-stop menyelesaikan permasalahan mobilitas kampus dan efisiensi energi fasilitas universitas.',
                    'tanggal' => '2026-12-19 08:00:00',
                    'lokasi' => 'Hall Gedung Robotika & Otomasi Lantai 2',
                    'kuota' => 60,
                    'tiketTerjual' => 54,
                    'statusEvent' => 'Buka',
                    'hargaDasar' => 60000,
                    'kategori' => 'Kompetisi'
                ]
            ],
            'promos' => [
                [
                    'kode' => 'MHSHEMAT',
                    'persenDiskon' => 0.20,
                    'maksPotongan' => 20000,
                    'masaBerlaku' => '2026-12-31',
                    'kuotaPemakaian' => 45,
                    'keterangan' => 'Diskon 20% khusus mahasiswa hingga Rp 20.000'
                ],
                [
                    'kode' => 'SEMINARTEKNO',
                    'persenDiskon' => 0.15,
                    'maksPotongan' => 15000,
                    'masaBerlaku' => '2026-12-31',
                    'kuotaPemakaian' => 30,
                    'keterangan' => 'Potongan 15% untuk semua kategori tiket seminar'
                ]
            ],
            'orders' => [
                [
                    'idPesanan' => 'ORD-2026-001',
                    'idPeserta' => 'USR-01',
                    'idEvent' => 'EVT-01',
                    'tanggalPesan' => '2026-11-01 10:20:00',
                    'statusPesanan' => 'KONFIRMASI',
                    'kodePromo' => 'MHSHEMAT',
                    'metodeBayar' => 'Transfer',
                    'namaBank' => 'Bank Mandiri',
                    'noRekening' => '137-00-2819201-9',
                    'biayaAdmin' => 2500,
                    'subtotal' => 80000,
                    'potongan' => 16000,
                    'totalBayar' => 66500,
                    'statusBayar' => 'LUNAS'
                ]
            ],
            'tickets' => [
                [
                    'kodeTiket' => 'ETX-2026-VIP-910',
                    'jenis' => 'VIP',
                    'hargaDasar' => 45000,
                    'biayaLayananVIP' => 35000,
                    'nomorKursi' => 'A-04',
                    'statusTiket' => 'AKTIF',
                    'idEvent' => 'EVT-01',
                    'idPesanan' => 'ORD-2026-001'
                ]
            ],
            'checkins' => []
        ];
    }

    // --- EVENTS ---
    public function getEvents(): array {
        $events = [];
        foreach ($this->data['events'] as $row) {
            $events[] = new Event(
                $row['idEvent'],
                $row['namaEvent'],
                $row['deskripsi'],
                $row['tanggal'],
                $row['lokasi'],
                (int)$row['kuota'],
                (int)$row['tiketTerjual'],
                $row['statusEvent'],
                (float)$row['hargaDasar'],
                $row['kategori'] ?? 'Seminar'
            );
        }
        return $events;
    }

    public function getEventById(string $id): ?Event {
        foreach ($this->getEvents() as $ev) {
            if ($ev->getIdEvent() === $id) {
                return $ev;
            }
        }
        return null;
    }

    public function saveEvent(Event $event): void {
        $found = false;
        foreach ($this->data['events'] as &$row) {
            if ($row['idEvent'] === $event->getIdEvent()) {
                $row['namaEvent'] = $event->getNamaEvent();
                $row['deskripsi'] = $event->getDeskripsi();
                $row['tanggal'] = $event->getTanggal();
                $row['lokasi'] = $event->getLokasi();
                $row['kuota'] = $event->getKuota();
                $row['tiketTerjual'] = $event->getTiketTerjual();
                $row['statusEvent'] = $event->getStatusEvent();
                $row['hargaDasar'] = $event->getHargaDasar();
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->data['events'][] = [
                'idEvent' => $event->getIdEvent(),
                'namaEvent' => $event->getNamaEvent(),
                'deskripsi' => $event->getDeskripsi(),
                'tanggal' => $event->getTanggal(),
                'lokasi' => $event->getLokasi(),
                'kuota' => $event->getKuota(),
                'tiketTerjual' => $event->getTiketTerjual(),
                'statusEvent' => $event->getStatusEvent(),
                'hargaDasar' => $event->getHargaDasar(),
                'kategori' => $event->getKategori()
            ];
        }
        $this->saveData();
    }

    public function deleteEvent(string $id): bool {
        $initialCount = count($this->data['events'] ?? []);
        $this->data['events'] = array_values(array_filter($this->data['events'] ?? [], function($row) use ($id) {
            return ($row['idEvent'] ?? '') !== $id;
        }));
        if (count($this->data['events']) < $initialCount) {
            $this->saveData();
            return true;
        }
        return false;
    }

    // --- PROMOS ---
    public function getPromos(): array {
        $promos = [];
        foreach ($this->data['promos'] as $p) {
            $promos[] = new KodePromo(
                $p['kode'],
                (float)$p['persenDiskon'],
                (float)$p['maksPotongan'],
                $p['masaBerlaku'],
                (int)$p['kuotaPemakaian'],
                $p['keterangan'] ?? ''
            );
        }
        return $promos;
    }

    public function getPromoByCode(string $code): ?KodePromo {
        $upper = strtoupper(trim($code));
        foreach ($this->getPromos() as $p) {
            if ($p->getKode() === $upper) {
                return $p;
            }
        }
        return null;
    }

    public function savePromo(KodePromo $promo): void {
        $found = false;
        foreach ($this->data['promos'] as &$p) {
            if (strtoupper($p['kode']) === $promo->getKode()) {
                $p['persenDiskon'] = $promo->getPersenDiskon();
                $p['maksPotongan'] = $promo->getMaksPotongan();
                $p['masaBerlaku'] = $promo->getMasaBerlaku();
                $p['kuotaPemakaian'] = $promo->getKuotaPemakaian();
                $p['keterangan'] = $promo->getKeterangan();
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->data['promos'][] = [
                'kode' => $promo->getKode(),
                'persenDiskon' => $promo->getPersenDiskon(),
                'maksPotongan' => $promo->getMaksPotongan(),
                'masaBerlaku' => $promo->getMasaBerlaku(),
                'kuotaPemakaian' => $promo->getKuotaPemakaian(),
                'keterangan' => $promo->getKeterangan()
            ];
        }
        $this->saveData();
    }

    public function deletePromo(string $code): bool {
        $upper = strtoupper(trim($code));
        $initialCount = count($this->data['promos'] ?? []);
        $this->data['promos'] = array_values(array_filter($this->data['promos'] ?? [], function($p) use ($upper) {
            return strtoupper($p['kode'] ?? '') !== $upper;
        }));
        if (count($this->data['promos']) < $initialCount) {
            $this->saveData();
            return true;
        }
        return false;
    }

    // --- TICKETS ---
    public function getTickets(): array {
        $tickets = [];
        foreach ($this->data['tickets'] as $row) {
            if (($row['jenis'] ?? 'Reguler') === 'VIP') {
                $tickets[] = new TiketVIP(
                    $row['kodeTiket'],
                    (float)$row['hargaDasar'],
                    (float)($row['biayaLayananVIP'] ?? 35000),
                    $row['nomorKursi'] ?? 'VIP-01',
                    $row['statusTiket'],
                    $row['idEvent'],
                    $row['idPesanan']
                );
            } else {
                $tickets[] = new TiketReguler(
                    $row['kodeTiket'],
                    (float)$row['hargaDasar'],
                    $row['statusTiket'],
                    $row['idEvent'],
                    $row['idPesanan']
                );
            }
        }
        return $tickets;
    }

    public function getTicketByCode(string $code): ?Tiket {
        $clean = strtoupper(trim($code));
        foreach ($this->getTickets() as $t) {
            if (strtoupper($t->getKodeTiket()) === $clean) {
                return $t;
            }
        }
        return null;
    }

    public function saveTicket(Tiket $tiket): void {
        $found = false;
        foreach ($this->data['tickets'] as &$row) {
            if (strtoupper($row['kodeTiket']) === strtoupper($tiket->getKodeTiket())) {
                $row['statusTiket'] = $tiket->getStatusTiket();
                $found = true;
                break;
            }
        }
        if (!$found) {
            $newRow = [
                'kodeTiket' => $tiket->getKodeTiket(),
                'jenis' => $tiket->getJenis(),
                'hargaDasar' => $tiket->getHargaDasar(),
                'statusTiket' => $tiket->getStatusTiket(),
                'idEvent' => $tiket->getIdEvent(),
                'idPesanan' => $tiket->getIdPesanan()
            ];
            if ($tiket instanceof TiketVIP) {
                $newRow['biayaLayananVIP'] = $tiket->getBiayaLayananVIP();
                $newRow['nomorKursi'] = $tiket->getNomorKursi();
            }
            $this->data['tickets'][] = $newRow;
        }
        $this->saveData();
    }

    // --- ORDERS ---
    public function getOrders(): array {
        return $this->data['orders'] ?? [];
    }

    public function getOrderById(string $id): ?array {
        foreach ($this->data['orders'] as $ord) {
            if ($ord['idPesanan'] === $id) {
                return $ord;
            }
        }
        return null;
    }

    public function saveOrderRecord(array $orderRow): void {
        $this->data['orders'][] = $orderRow;
        $this->saveData();
    }

    // --- CHECKIN ---
    public function getCheckIns(): array {
        $list = [];
        foreach ($this->data['checkins'] as $c) {
            $list[] = new CheckIn(
                $c['idCheckIn'],
                $c['kodeTiket'],
                $c['statusValidasi'],
                $c['catatan'],
                $c['namaPetugas'] ?? 'Panitia Pintu Masuk',
                $c['waktuCheckIn']
            );
        }
        return $list;
    }

    public function saveCheckIn(CheckIn $checkIn): void {
        $this->data['checkins'][] = [
            'idCheckIn' => $checkIn->getIdCheckIn(),
            'kodeTiket' => $checkIn->getKodeTiket(),
            'statusValidasi' => $checkIn->getStatusValidasi(),
            'catatan' => $checkIn->getCatatan(),
            'namaPetugas' => $checkIn->getNamaPetugas(),
            'waktuCheckIn' => $checkIn->getWaktuCheckIn()
        ];
        $this->saveData();
    }

    // --- USERS ---
    public function getUsers(): array {
        $users = [];
        foreach ($this->data['users'] as $u) {
            if ($u['role'] === 'Peserta') {
                $users[] = new Peserta($u['idUser'], $u['nama'], $u['email'], $u['password'], $u['nim'], $u['noTelepon']);
            } else {
                $users[] = new Panitia($u['idUser'], $u['nama'], $u['email'], $u['password'], $u['jabatan']);
            }
        }
        return $users;
    }

    public function getUserById(string $id): ?User {
        foreach ($this->getUsers() as $u) {
            if ($u->getIdUser() === $id) {
                return $u;
            }
        }
        return null;
    }

    public function getUserByEmail(string $email): ?User {
        foreach ($this->getUsers() as $u) {
            if (strtolower($u->getEmail()) === strtolower(trim($email))) {
                return $u;
            }
        }
        return null;
    }
}

