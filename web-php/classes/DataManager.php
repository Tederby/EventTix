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
require_once __DIR__ . '/../config/database.php';

class DataManager {
    private static ?DataManager $instance = null;
    private ?PDO $pdo = null;
    private string $filePath;
    private array $data = [];

    private function __construct() {
        $this->filePath = __DIR__ . '/../data/store.json';
        if (class_exists('Database')) {
            $this->pdo = Database::getConnection();
        }
        if ($this->pdo === null) {
            $this->loadData();
        }
    }

    public static function getInstance(): DataManager {
        if (self::$instance === null) {
            self::$instance = new DataManager();
        }
        return self::$instance;
    }

    public function isUsingDatabase(): bool {
        return $this->pdo !== null;
    }

    // =========================================================================
    // STORAGE FILE JSON (Fallback Offline)
    // =========================================================================
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
                    'tiketTerjual' => 85,
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
                    'statusEvent' => 'Tutup',
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
                    'statusEvent' => 'Tutup',
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
            'orders' => [],
            'tickets' => [],
            'checkins' => []
        ];
    }

    // =========================================================================
    // EVENTS
    // =========================================================================
    public function getEvents(): array {
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM events ORDER BY tanggal ASC");
                $events = [];
                while ($row = $stmt->fetch()) {
                    $events[] = new Event(
                        $row['id_event'],
                        $row['nama_event'],
                        $row['deskripsi'],
                        $row['tanggal'],
                        $row['lokasi'],
                        (int)$row['kuota'],
                        (int)$row['tiket_terjual'],
                        $row['status_event'],
                        (float)$row['harga_dasar'],
                        $row['kategori'] ?? 'Seminar'
                    );
                }
                return $events;
            } catch (PDOException $e) {
                error_log('Error getEvents: ' . $e->getMessage());
            }
        }

        $events = [];
        foreach ($this->data['events'] ?? [] as $row) {
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
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM events WHERE id_event = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $row = $stmt->fetch();
                if ($row) {
                    return new Event(
                        $row['id_event'],
                        $row['nama_event'],
                        $row['deskripsi'],
                        $row['tanggal'],
                        $row['lokasi'],
                        (int)$row['kuota'],
                        (int)$row['tiket_terjual'],
                        $row['status_event'],
                        (float)$row['harga_dasar'],
                        $row['kategori'] ?? 'Seminar'
                    );
                }
                return null;
            } catch (PDOException $e) {
                error_log('Error getEventById: ' . $e->getMessage());
            }
        }

        foreach ($this->getEvents() as $ev) {
            if ($ev->getIdEvent() === $id) {
                return $ev;
            }
        }
        return null;
    }

    public function saveEvent(Event $event): void {
        if ($this->pdo !== null) {
            try {
                $sql = "INSERT INTO events (id_event, nama_event, deskripsi, tanggal, lokasi, kuota, tiket_terjual, status_event, harga_dasar, kategori)
                        VALUES (:id, :nama, :deskripsi, :tanggal, :lokasi, :kuota, :terjual, :status, :harga, :kategori)
                        ON CONFLICT (id_event) DO UPDATE SET
                        nama_event = EXCLUDED.nama_event,
                        deskripsi = EXCLUDED.deskripsi,
                        tanggal = EXCLUDED.tanggal,
                        lokasi = EXCLUDED.lokasi,
                        kuota = EXCLUDED.kuota,
                        tiket_terjual = EXCLUDED.tiket_terjual,
                        status_event = EXCLUDED.status_event,
                        harga_dasar = EXCLUDED.harga_dasar,
                        kategori = EXCLUDED.kategori";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'id' => $event->getIdEvent(),
                    'nama' => $event->getNamaEvent(),
                    'deskripsi' => $event->getDeskripsi(),
                    'tanggal' => $event->getTanggal(),
                    'lokasi' => $event->getLokasi(),
                    'kuota' => $event->getKuota(),
                    'terjual' => $event->getTiketTerjual(),
                    'status' => $event->getStatusEvent(),
                    'harga' => $event->getHargaDasar(),
                    'kategori' => $event->getKategori()
                ]);
                return;
            } catch (PDOException $e) {
                error_log('Error saveEvent: ' . $e->getMessage());
            }
        }

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
                $row['kategori'] = $event->getKategori();
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
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM events WHERE id_event = :id");
                $stmt->execute(['id' => $id]);
                return $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                error_log('Error deleteEvent: ' . $e->getMessage());
            }
        }

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

    // =========================================================================
    // KODE PROMO
    // =========================================================================
    public function getPromos(): array {
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM kode_promo ORDER BY id_promo DESC");
                $promos = [];
                while ($row = $stmt->fetch()) {
                    $promos[] = new KodePromo(
                        $row['kode'],
                        (float)$row['persen_diskon'],
                        (float)$row['maks_potongan'],
                        $row['masa_berlaku'],
                        (int)$row['kuota_pemakaian'],
                        $row['keterangan'] ?? ''
                    );
                }
                return $promos;
            } catch (PDOException $e) {
                error_log('Error getPromos: ' . $e->getMessage());
            }
        }

        $promos = [];
        foreach ($this->data['promos'] ?? [] as $p) {
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
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM kode_promo WHERE UPPER(kode) = :kode LIMIT 1");
                $stmt->execute(['kode' => $upper]);
                $row = $stmt->fetch();
                if ($row) {
                    return new KodePromo(
                        $row['kode'],
                        (float)$row['persen_diskon'],
                        (float)$row['maks_potongan'],
                        $row['masa_berlaku'],
                        (int)$row['kuota_pemakaian'],
                        $row['keterangan'] ?? ''
                    );
                }
                return null;
            } catch (PDOException $e) {
                error_log('Error getPromoByCode: ' . $e->getMessage());
            }
        }

        foreach ($this->getPromos() as $p) {
            if ($p->getKode() === $upper) {
                return $p;
            }
        }
        return null;
    }

    public function savePromo(KodePromo $promo): void {
        if ($this->pdo !== null) {
            try {
                $sql = "INSERT INTO kode_promo (kode, persen_diskon, maks_potongan, masa_berlaku, kuota_pemakaian, keterangan)
                        VALUES (:kode, :persen, :maks, :berlaku, :kuota, :ket)
                        ON CONFLICT (kode) DO UPDATE SET
                        persen_diskon = EXCLUDED.persen_diskon,
                        maks_potongan = EXCLUDED.maks_potongan,
                        masa_berlaku = EXCLUDED.masa_berlaku,
                        kuota_pemakaian = EXCLUDED.kuota_pemakaian,
                        keterangan = EXCLUDED.keterangan";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'kode' => $promo->getKode(),
                    'persen' => $promo->getPersenDiskon(),
                    'maks' => $promo->getMaksPotongan(),
                    'berlaku' => $promo->getMasaBerlaku(),
                    'kuota' => $promo->getKuotaPemakaian(),
                    'ket' => $promo->getKeterangan()
                ]);
                return;
            } catch (PDOException $e) {
                error_log('Error savePromo: ' . $e->getMessage());
            }
        }

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
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM kode_promo WHERE UPPER(kode) = :kode");
                $stmt->execute(['kode' => $upper]);
                return $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                error_log('Error deletePromo: ' . $e->getMessage());
            }
        }

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

    // =========================================================================
    // TIKET
    // =========================================================================
    public function getTickets(): array {
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM tiket ORDER BY id_tiket DESC");
                $tickets = [];
                while ($row = $stmt->fetch()) {
                    if ($row['jenis_tiket'] === 'VIP') {
                        $tickets[] = new TiketVIP(
                            $row['kode_tiket'],
                            (float)$row['harga_dasar'],
                            (float)$row['biaya_layanan_vip'],
                            $row['nomor_kursi'] ?? 'VIP-01',
                            $row['status_tiket'],
                            $row['id_event'],
                            $row['id_pesanan']
                        );
                    } else {
                        $tickets[] = new TiketReguler(
                            $row['kode_tiket'],
                            (float)$row['harga_dasar'],
                            $row['status_tiket'],
                            $row['id_event'],
                            $row['id_pesanan']
                        );
                    }
                }
                return $tickets;
            } catch (PDOException $e) {
                error_log('Error getTickets: ' . $e->getMessage());
            }
        }

        $tickets = [];
        foreach ($this->data['tickets'] ?? [] as $row) {
            if (($row['jenis'] ?? 'Reguler') === 'VIP') {
                $tickets[] = new TiketVIP(
                    $row['kodeTiket'],
                    (float)$row['hargaDasar'],
                    (float)($row['biayaLayananVIP'] ?? 35000),
                    $row['nomorKursi'] ?? 'VIP-01',
                    $row['statusTiket'],
                    $row['idEvent'] ?? '',
                    $row['idPesanan'] ?? ''
                );
            } else {
                $tickets[] = new TiketReguler(
                    $row['kodeTiket'],
                    (float)$row['hargaDasar'],
                    $row['statusTiket'],
                    $row['idEvent'] ?? '',
                    $row['idPesanan'] ?? ''
                );
            }
        }
        return $tickets;
    }

    public function getTicketByCode(string $code): ?Tiket {
        $clean = strtoupper(trim($code));
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM tiket WHERE UPPER(kode_tiket) = :kode LIMIT 1");
                $stmt->execute(['kode' => $clean]);
                $row = $stmt->fetch();
                if ($row) {
                    if ($row['jenis_tiket'] === 'VIP') {
                        return new TiketVIP(
                            $row['kode_tiket'],
                            (float)$row['harga_dasar'],
                            (float)$row['biaya_layanan_vip'],
                            $row['nomor_kursi'] ?? 'VIP-01',
                            $row['status_tiket'],
                            $row['id_event'],
                            $row['id_pesanan']
                        );
                    }
                    return new TiketReguler(
                        $row['kode_tiket'],
                        (float)$row['harga_dasar'],
                        $row['status_tiket'],
                        $row['id_event'],
                        $row['id_pesanan']
                    );
                }
                return null;
            } catch (PDOException $e) {
                error_log('Error getTicketByCode: ' . $e->getMessage());
            }
        }

        foreach ($this->getTickets() as $t) {
            if (strtoupper($t->getKodeTiket()) === $clean) {
                return $t;
            }
        }
        return null;
    }

    public function saveTicket(Tiket $tiket): void {
        if ($this->pdo !== null) {
            try {
                $sql = "INSERT INTO tiket (kode_tiket, id_pesanan, id_event, jenis_tiket, harga_dasar, biaya_layanan_vip, nomor_kursi, status_tiket, waktu_diterbitkan)
                        VALUES (:kode, :id_pesanan, :id_event, :jenis, :harga, :biaya_vip, :kursi, :status, :waktu)
                        ON CONFLICT (kode_tiket) DO UPDATE SET status_tiket = EXCLUDED.status_tiket";
                $stmt = $this->pdo->prepare($sql);
                $isVip = ($tiket instanceof TiketVIP);
                $stmt->execute([
                    'kode' => $tiket->getKodeTiket(),
                    'id_pesanan' => $tiket->getIdPesanan() ?: 'ORD-2026-001',
                    'id_event' => $tiket->getIdEvent() ?: 'EVT-01',
                    'jenis' => $isVip ? 'VIP' : 'Reguler',
                    'harga' => $tiket->getHargaDasar(),
                    'biaya_vip' => $isVip ? $tiket->getBiayaLayananVIP() : 0.0,
                    'kursi' => $isVip ? $tiket->getNomorKursi() : null,
                    'status' => $tiket->getStatusTiket(),
                    'waktu' => date('Y-m-d H:i:s')
                ]);
                return;
            } catch (PDOException $e) {
                error_log('Error saveTicket: ' . $e->getMessage());
            }
        }

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

    // =========================================================================
    // ORDERS
    // =========================================================================
    public function getOrders(): array {
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM pesanan ORDER BY tanggal_pesan DESC");
                $orders = [];
                while ($row = $stmt->fetch()) {
                    $orders[] = [
                        'idPesanan' => $row['id_pesanan'],
                        'idPeserta' => $row['id_peserta'],
                        'idEvent' => $row['id_event'],
                        'tanggalPesan' => $row['tanggal_pesan'],
                        'statusPesanan' => $row['status_pesanan'],
                        'kodePromo' => $row['kode_promo'],
                        'subtotal' => (float)$row['subtotal'],
                        'potongan' => (float)$row['potongan'],
                        'biayaAdmin' => (float)$row['biaya_admin'],
                        'totalBayar' => (float)$row['total_bayar'],
                        'statusBayar' => $row['status_bayar']
                    ];
                }
                return $orders;
            } catch (PDOException $e) {
                error_log('Error getOrders: ' . $e->getMessage());
            }
        }

        return $this->data['orders'] ?? [];
    }

    public function getOrderById(string $id): ?array {
        foreach ($this->getOrders() as $ord) {
            if ($ord['idPesanan'] === $id) {
                return $ord;
            }
        }
        return null;
    }

    public function saveOrderRecord(array $orderRow): void {
        if ($this->pdo !== null) {
            try {
                $sql = "INSERT INTO pesanan (id_pesanan, id_peserta, id_event, tanggal_pesan, status_pesanan, kode_promo, subtotal, potongan, biaya_admin, total_bayar, status_bayar)
                        VALUES (:id, :peserta, :event, :tgl, :status, :promo, :subtotal, :potongan, :admin, :total, :bayar)
                        ON CONFLICT (id_pesanan) DO UPDATE SET status_pesanan = EXCLUDED.status_pesanan, status_bayar = EXCLUDED.status_bayar";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'id' => $orderRow['idPesanan'],
                    'peserta' => $orderRow['idPeserta'],
                    'event' => $orderRow['idEvent'],
                    'tgl' => $orderRow['tanggalPesan'] ?? date('Y-m-d H:i:s'),
                    'status' => $orderRow['statusPesanan'] ?? 'KONFIRMASI',
                    'promo' => $orderRow['kodePromo'] ?? null,
                    'subtotal' => $orderRow['subtotal'] ?? 0,
                    'potongan' => $orderRow['potongan'] ?? 0,
                    'admin' => $orderRow['biayaAdmin'] ?? 0,
                    'total' => $orderRow['totalBayar'] ?? 0,
                    'bayar' => $orderRow['statusBayar'] ?? 'LUNAS'
                ]);

                // Simpan juga record pembayaran
                $sqlPay = "INSERT INTO pembayaran (id_pembayaran, id_pesanan, metode, nominal, biaya_admin, status_bayar, tanggal_bayar)
                           VALUES (:id_pay, :id_pesanan, :metode, :nominal, :admin, 'LUNAS', :tgl)";
                $stmtPay = $this->pdo->prepare($sqlPay);
                $stmtPay->execute([
                    'id_pay' => 'PAY-' . strtoupper(bin2hex(random_bytes(4))),
                    'id_pesanan' => $orderRow['idPesanan'],
                    'metode' => ($orderRow['metodeBayar'] ?? 'transfer') === 'ewallet' ? 'ewallet' : 'transfer',
                    'nominal' => $orderRow['totalBayar'] ?? 0,
                    'admin' => $orderRow['biayaAdmin'] ?? 0,
                    'tgl' => date('Y-m-d H:i:s')
                ]);
                return;
            } catch (PDOException $e) {
                error_log('Error saveOrderRecord: ' . $e->getMessage());
            }
        }

        $this->data['orders'][] = $orderRow;
        $this->saveData();
    }

    // =========================================================================
    // CHECK-IN
    // =========================================================================
    public function getCheckIns(): array {
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM check_in ORDER BY id_checkin DESC");
                $list = [];
                while ($c = $stmt->fetch()) {
                    $list[] = new CheckIn(
                        (string)$c['id_checkin'],
                        $c['id_tiket'],
                        $c['status_checkin'],
                        $c['catatan'] ?? '',
                        'Panitia Pintu Masuk',
                        $c['waktu_checkin']
                    );
                }
                return $list;
            } catch (PDOException $e) {
                error_log('Error getCheckIns: ' . $e->getMessage());
            }
        }

        $list = [];
        foreach ($this->data['checkins'] ?? [] as $c) {
            $list[] = new CheckIn(
                $c['idCheckIn'],
                $c['kodeTiket'],
                $c['statusValidasi'],
                $c['catatan'] ?? '',
                $c['namaPetugas'] ?? 'Panitia Pintu Masuk',
                $c['waktuCheckIn']
            );
        }
        return $list;
    }

    public function saveCheckIn(CheckIn $checkIn): void {
        if ($this->pdo !== null) {
            try {
                $sql = "INSERT INTO check_in (id_tiket, id_panitia, waktu_checkin, status_checkin, catatan)
                        VALUES (:tiket, :panitia, :waktu, :status, :catatan)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'tiket' => $checkIn->getKodeTiket(),
                    'panitia' => 'ADM-01',
                    'waktu' => $checkIn->getWaktuCheckIn(),
                    'status' => $checkIn->getStatusValidasi(),
                    'catatan' => $checkIn->getCatatan()
                ]);
                return;
            } catch (PDOException $e) {
                error_log('Error saveCheckIn: ' . $e->getMessage());
            }
        }

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

    // =========================================================================
    // USERS
    // =========================================================================
    public function getUsers(): array {
        if ($this->pdo !== null) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM users ORDER BY id_user ASC");
                $users = [];
                while ($u = $stmt->fetch()) {
                    if ($u['role'] === 'Peserta') {
                        $users[] = new Peserta($u['id_user'], $u['nama'], $u['email'], $u['password'], $u['nim'] ?? '', $u['no_telepon'] ?? '');
                    } else {
                        $users[] = new Panitia($u['id_user'], $u['nama'], $u['email'], $u['password'], $u['jabatan'] ?? 'Panitia Acara');
                    }
                }
                return $users;
            } catch (PDOException $e) {
                error_log('Error getUsers: ' . $e->getMessage());
            }
        }

        $users = [];
        foreach ($this->data['users'] ?? [] as $u) {
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
