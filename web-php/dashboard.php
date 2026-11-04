<?php
// web/dashboard.php
require_once __DIR__ . '/includes/init.php';

// Pastikan hanya Panitia yang mengakses (atau alihkan otomatis)
if ($currentUser->getRole() !== 'Panitia') {
    // Alihkan peran ke panitia jika pengguna ingin melihat laporan
    $_SESSION['user_id'] = 'ADM-01';
    $_SESSION['user_role'] = 'Panitia';
    $currentUser = $dataManager->getUserById('ADM-01');
}

$pesanNotifikasi = '';

// AKSI 1: Tutup atau Buka Penjualan Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tutup_penjualan') {
        $idEvt = $_POST['id_event'] ?? '';
        $ev = $dataManager->getEventById($idEvt);
        if ($ev) {
            $ev->tutupPenjualan();
            $dataManager->saveEvent($ev);
            $pesanNotifikasi = 'Penjualan tiket untuk event ' . htmlspecialchars($ev->getNamaEvent()) . ' berhasil ditutup.';
        }
    } elseif ($_POST['action'] === 'buka_penjualan') {
        $idEvt = $_POST['id_event'] ?? '';
        $ev = $dataManager->getEventById($idEvt);
        if ($ev) {
            $ev->bukaPenjualan();
            $dataManager->saveEvent($ev);
            $pesanNotifikasi = 'Penjualan tiket untuk event ' . htmlspecialchars($ev->getNamaEvent()) . ' berhasil dibuka kembali.';
        }
    } elseif ($_POST['action'] === 'tambah_event') {
        $idBaru = 'EVT-0' . (count($dataManager->getEvents()) + 1);
        $namaBaru = trim($_POST['nama_event'] ?? '');
        $deskripsiBaru = trim($_POST['deskripsi'] ?? '');
        $tanggalBaru = trim($_POST['tanggal'] ?? '') . ' ' . trim($_POST['jam'] ?? '08:00') . ':00';
        $lokasiBaru = trim($_POST['lokasi'] ?? '');
        $kuotaBaru = (int)($_POST['kuota'] ?? 50);
        $hargaBaru = (float)($_POST['harga_dasar'] ?? 40000);
        $kategoriBaru = trim($_POST['kategori'] ?? 'Seminar');

        if ($namaBaru && $lokasiBaru && $kuotaBaru > 0) {
            $newEvent = new Event($idBaru, $namaBaru, $deskripsiBaru, $tanggalBaru, $lokasiBaru, $kuotaBaru, 0, 'Buka', $hargaBaru, $kategoriBaru);
            $dataManager->saveEvent($newEvent);
            $pesanNotifikasi = 'Event baru berhasil ditambahkan ke dalam katalog.';
        }
    } elseif ($_POST['action'] === 'hapus_event') {
        $idEvt = $_POST['id_event'] ?? '';
        $ev = $dataManager->getEventById($idEvt);
        $nama = $ev ? $ev->getNamaEvent() : $idEvt;
        if ($dataManager->deleteEvent($idEvt)) {
            $pesanNotifikasi = 'Event ' . htmlspecialchars($nama) . ' berhasil dihapus dari sistem.';
        }
    } elseif ($_POST['action'] === 'tambah_promo') {
        $kode = strtoupper(trim($_POST['kode_promo'] ?? ''));
        $persen = (float)($_POST['persen_diskon'] ?? 10) / 100.0;
        $maks = (float)($_POST['maks_potongan'] ?? 10000);
        $berlaku = trim($_POST['masa_berlaku'] ?? '2026-12-31');
        $kuota = (int)($_POST['kuota_pemakaian'] ?? 50);
        $ket = trim($_POST['keterangan'] ?? '');

        if ($kode && $persen > 0) {
            $promoBaru = new KodePromo($kode, $persen, $maks, $berlaku, $kuota, $ket);
            $dataManager->savePromo($promoBaru);
            $pesanNotifikasi = 'Kode promo ' . htmlspecialchars($kode) . ' berhasil dibuat dan aktif.';
        }
    } elseif ($_POST['action'] === 'hapus_promo') {
        $kode = $_POST['kode_promo'] ?? '';
        if ($dataManager->deletePromo($kode)) {
            $pesanNotifikasi = 'Kode promo ' . htmlspecialchars($kode) . ' berhasil dihapus.';
        }
    }
}

$events = $dataManager->getEvents();
$promos = $dataManager->getPromos();
$orders = $dataManager->getOrders();
$tickets = $dataManager->getTickets();
$checkins = $dataManager->getCheckIns();

// Kalkulasi Agregasi Dashboard
$totalPendapatan = 0.0;
foreach ($orders as $ord) {
    if (($ord['statusBayar'] ?? '') === 'LUNAS') {
        $totalPendapatan += (float)($ord['totalBayar'] ?? 0);
    }
}

$totalTiketTerjual = 0;
$totalKapasitasKuota = 0;
foreach ($events as $ev) {
    $totalTiketTerjual += $ev->getTiketTerjual();
    $totalKapasitasKuota += $ev->getKuota();
}

$totalCheckInValid = 0;
foreach ($checkins as $chk) {
    if ($chk->getStatusValidasi() === 'VALID') {
        $totalCheckInValid++;
    }
}

$persenKehadiran = $totalTiketTerjual > 0 ? round(($totalCheckInValid / $totalTiketTerjual) * 100, 1) : 0;

$pageTitle = 'Dashboard & Laporan Panitia';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 2rem;">
  <h1 style="font-size: var(--text-28); margin-bottom: 0.5rem;">Dashboard & rekap penjualan</h1>
  <p style="color: var(--color-biru-abu); font-size: var(--text-16); margin: 0;">
    Ringkasan metrik penjualan tiket, tingkat validasi pintu masuk, dan kendali status event kampus.
  </p>
</div>

<?php if ($pesanNotifikasi): ?>
  <div class="alert alert-success"><?= htmlspecialchars($pesanNotifikasi) ?></div>
<?php endif; ?>

<!-- RINGKASAN METRIK PADAT & FUNGSIONAL -->
<div class="metric-grid">
  <div class="metric-card">
    <div class="metric-label">Total pendapatan bersih</div>
    <div class="metric-value"><?= rupiah($totalPendapatan) ?></div>
    <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.25rem;">Dari <?= count($orders) ?> transaksi pesanan</div>
  </div>

  <div class="metric-card">
    <div class="metric-label">Tiket terjual</div>
    <div class="metric-value"><?= number_format($totalTiketTerjual, 0, ',', '.') ?></div>
    <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.25rem;">Dari total kuota <?= number_format($totalKapasitasKuota, 0, ',', '.') ?> kursi</div>
  </div>

  <div class="metric-card">
    <div class="metric-label">Tingkat kehadiran check-in</div>
    <div class="metric-value"><?= $persenKehadiran ?>%</div>
    <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.25rem;"><?= $totalCheckInValid ?> peserta telah masuk pintu</div>
  </div>

  <div class="metric-card">
    <div class="metric-label">Event aktif berjalan</div>
    <div class="metric-value"><?= count($events) ?></div>
    <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.25rem;">Seminar & workshop</div>
  </div>
</div>

<!-- TABEL REKAP EVENT & KONTROL PENJUALAN -->
<div style="margin-bottom: 3rem;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h2 style="font-size: var(--text-20);">Rekapitulasi event</h2>
  </div>

  <div class="data-table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Kode & Nama Event</th>
          <th>Waktu Acara</th>
          <th class="text-right">Kapasitas</th>
          <th class="text-right">Terjual</th>
          <th class="text-right">Sisa Kursi</th>
          <th class="text-right">Harga Dasar</th>
          <th>Status Penjualan</th>
          <th class="text-right">Aksi Manajemen</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td>
              <div style="font-weight: 700;"><?= htmlspecialchars($ev->getNamaEvent()) ?></div>
              <div style="font-family: var(--font-mono); font-size: var(--text-12); color: var(--color-biru-abu);">
                <?= htmlspecialchars($ev->getIdEvent()) ?> / <?= htmlspecialchars($ev->getLokasi()) ?>
              </div>
            </td>
            <td style="font-size: var(--text-12);"><?= tanggalRingkas($ev->getTanggal()) ?></td>
            <td class="text-right"><?= number_format($ev->getKuota(), 0, ',', '.') ?></td>
            <td class="text-right"><strong><?= number_format($ev->getTiketTerjual(), 0, ',', '.') ?></strong></td>
            <td class="text-right"><?= number_format($ev->getSisaKuota(), 0, ',', '.') ?></td>
            <td class="text-right"><?= rupiah($ev->getHargaDasar()) ?></td>
            <td>
              <?php if ($ev->getStatusEvent() === 'Buka'): ?>
                <span style="font-family: var(--font-mono); font-size: var(--text-12); font-weight: 700; color: var(--color-hijau-cap); border: 1px solid var(--color-hijau-cap); padding: 2px 6px;">
                  BUKA
                </span>
              <?php elseif ($ev->getStatusEvent() === 'Penuh'): ?>
                <span style="font-family: var(--font-mono); font-size: var(--text-12); font-weight: 700; color: var(--color-biru-abu); border: 1px solid var(--color-biru-abu); padding: 2px 6px;">
                  PENUH
                </span>
              <?php else: ?>
                <span style="font-family: var(--font-mono); font-size: var(--text-12); font-weight: 700; color: var(--color-merah-cap); border: 1px solid var(--color-merah-cap); padding: 2px 6px;">
                  DITUTUP
                </span>
              <?php endif; ?>
            </td>
            <td class="text-right">
              <?php if ($ev->getStatusEvent() === 'Buka'): ?>
                <form method="POST" action="dashboard.php" style="display: inline;">
                  <input type="hidden" name="action" value="tutup_penjualan">
                  <input type="hidden" name="id_event" value="<?= htmlspecialchars($ev->getIdEvent()) ?>">
                  <button type="submit" class="btn btn-secondary btn-sm" style="font-size: var(--text-12); color: var(--color-merah-cap); border-color: var(--color-merah-cap);">
                    Tutup penjualan
                  </button>
                </form>
              <?php else: ?>
                <form method="POST" action="dashboard.php" style="display: inline;">
                  <input type="hidden" name="action" value="buka_penjualan">
                  <input type="hidden" name="id_event" value="<?= htmlspecialchars($ev->getIdEvent()) ?>">
                  <button type="submit" class="btn btn-secondary btn-sm" style="font-size: var(--text-12);">
                    Buka penjualan
                  </button>
                </form>
              <?php endif; ?>

              <form method="POST" action="dashboard.php" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event ini? Event akan dihapus permanen.');">
                <input type="hidden" name="action" value="hapus_event">
                <input type="hidden" name="id_event" value="<?= htmlspecialchars($ev->getIdEvent()) ?>">
                <button type="submit" class="btn btn-secondary btn-sm" style="font-size: var(--text-12); color: var(--color-merah-cap); border-color: var(--color-merah-cap); margin-left: 4px;">
                  Hapus
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- FORM TAMBAH EVENT BARU -->
<div style="background-color: var(--color-putih); border: var(--border-tegas); padding: 2rem; border-radius: var(--radius-karcis); margin-bottom: 3rem;">
  <h2 style="font-size: var(--text-20); margin-bottom: 0.5rem;">Buat event baru</h2>
  <p style="color: var(--color-biru-abu); font-size: var(--text-14); margin-bottom: 1.5rem;">
    Tambahkan jadwal seminar, workshop, atau kompetisi kampus baru ke dalam sistem EventTix.
  </p>

  <form method="POST" action="dashboard.php">
    <input type="hidden" name="action" value="tambah_event">

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
      <div class="form-group">
        <label class="form-label" for="nama_event">Nama event / seminar</label>
        <input type="text" id="nama_event" name="nama_event" class="form-input" required placeholder="Contoh: Seminar IoT & Embedded Systems 2026">
      </div>

      <div class="form-group">
        <label class="form-label" for="kategori">Kategori kegiatan</label>
        <select id="kategori" name="kategori" class="form-input">
          <option value="Seminar Nasional">Seminar Nasional</option>
          <option value="Workshop Hands-on">Workshop Hands-on</option>
          <option value="Kompetisi Mahasiswa">Kompetisi Mahasiswa</option>
          <option value="Kuliah Tamu">Kuliah Tamu</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="deskripsi">Deskripsi ringkas kegiatan</label>
      <input type="text" id="deskripsi" name="deskripsi" class="form-input" required placeholder="Ringkasan agenda, pembicara, dan manfaat bagi peserta">
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
      <div class="form-group">
        <label class="form-label" for="tanggal">Tanggal pelaksanaan</label>
        <input type="date" id="tanggal" name="tanggal" class="form-input" required value="2026-12-28">
      </div>

      <div class="form-group">
        <label class="form-label" for="lokasi">Tempat / Ruangan gedung</label>
        <input type="text" id="lokasi" name="lokasi" class="form-input" required placeholder="Contoh: Lab Jaringan Gedung E">
      </div>

      <div class="form-group">
        <label class="form-label" for="kuota">Kuota maksimal kursi</label>
        <input type="number" id="kuota" name="kuota" class="form-input" required value="50" min="5" max="500">
      </div>

      <div class="form-group">
        <label class="form-label" for="harga_dasar">Harga tiket dasar (Rp)</label>
        <input type="number" id="harga_dasar" name="harga_dasar" class="form-input" required value="40000" step="5000">
      </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
      <button type="submit" class="btn btn-primary">
        Simpan event
      </button>
    </div>
  </form>
</div>

<!-- SECTION KELOLA KODE PROMO -->
<div style="background-color: var(--color-putih); border: var(--border-tegas); padding: 2rem; border-radius: var(--radius-karcis); margin-bottom: 3rem;">
  <div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: var(--text-20); margin-bottom: 0.5rem;">Kelola kode promo</h2>
    <p style="color: var(--color-biru-abu); font-size: var(--text-14);">
      Atur kupon diskon mahasiswa, batas maksimal potongan harga, masa berlaku, dan kuota pemakaian.
    </p>
  </div>

  <div style="overflow-x: auto; margin-bottom: 2rem;">
    <table class="table-clean" style="margin-bottom: 0;">
      <thead>
        <tr>
          <th>Kode Promo</th>
          <th>Diskon</th>
          <th class="text-right">Maks. Potongan</th>
          <th>Masa Berlaku</th>
          <th class="text-right">Sisa Kuota</th>
          <th>Status</th>
          <th class="text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($promos)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--color-biru-abu); padding: 1.5rem;">
              Belum ada kode promo aktif. Buat promo baru menggunakan formulir di bawah.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($promos as $pr): ?>
            <tr>
              <td>
                <span style="font-family: var(--font-mono); font-weight: 700; font-size: var(--text-14); color: var(--color-tinta);">
                  <?= htmlspecialchars($pr->getKode()) ?>
                </span>
                <?php if ($pr->getKeterangan()): ?>
                  <div style="font-size: var(--text-12); color: var(--color-biru-abu);">
                    <?= htmlspecialchars($pr->getKeterangan()) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><strong><?= round($pr->getPersenDiskon() * 100) ?>%</strong></td>
              <td class="text-right"><?= rupiah($pr->getMaksPotongan()) ?></td>
              <td style="font-size: var(--text-12);"><?= tanggalRingkas($pr->getMasaBerlaku()) ?></td>
              <td class="text-right"><?= number_format($pr->getKuotaPemakaian(), 0, ',', '.') ?> kali</td>
              <td>
                <?php if ($pr->isValid()): ?>
                  <span style="font-family: var(--font-mono); font-size: var(--text-12); font-weight: 700; color: var(--color-hijau-cap); border: 1px solid var(--color-hijau-cap); padding: 2px 6px;">
                    AKTIF
                  </span>
                <?php else: ?>
                  <span style="font-family: var(--font-mono); font-size: var(--text-12); font-weight: 700; color: var(--color-merah-cap); border: 1px solid var(--color-merah-cap); padding: 2px 6px;">
                    KEDALUWARSA
                  </span>
                <?php endif; ?>
              </td>
              <td class="text-right">
                <form method="POST" action="dashboard.php" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kode promo <?= htmlspecialchars($pr->getKode()) ?>?');">
                  <input type="hidden" name="action" value="hapus_promo">
                  <input type="hidden" name="kode_promo" value="<?= htmlspecialchars($pr->getKode()) ?>">
                  <button type="submit" class="btn btn-secondary btn-sm" style="font-size: var(--text-12); color: var(--color-merah-cap); border-color: var(--color-merah-cap);">
                    Hapus
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Form Tambah Kode Promo Baru -->
  <div style="background-color: var(--color-karton); padding: 1.5rem; border-radius: 4px; border: 1px solid var(--color-garis-halus);">
    <h3 style="font-size: var(--text-16); margin-bottom: 1rem; color: var(--color-tinta);">Buat kode promo baru</h3>
    <form method="POST" action="dashboard.php">
      <input type="hidden" name="action" value="tambah_promo">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div class="form-group">
          <label class="form-label" for="kode_promo">Kode promo (Kombinasi huruf/angka)</label>
          <input type="text" id="kode_promo" name="kode_promo" class="form-input" required placeholder="Contoh: DIESNATALIS" style="text-transform: uppercase;">
        </div>

        <div class="form-group">
          <label class="form-label" for="persen_diskon">Persen diskon (%)</label>
          <input type="number" id="persen_diskon" name="persen_diskon" class="form-input" required value="20" min="1" max="100">
        </div>

        <div class="form-group">
          <label class="form-label" for="maks_potongan">Maksimal potongan (Rp)</label>
          <input type="number" id="maks_potongan" name="maks_potongan" class="form-input" required value="25000" step="1000">
        </div>

        <div class="form-group">
          <label class="form-label" for="masa_berlaku">Berlaku sampai tanggal</label>
          <input type="date" id="masa_berlaku" name="masa_berlaku" class="form-input" required value="2026-12-31">
        </div>

        <div class="form-group">
          <label class="form-label" for="kuota_pemakaian">Kuota pemakaian (kali)</label>
          <input type="number" id="kuota_pemakaian" name="kuota_pemakaian" class="form-input" required value="50" min="1" max="1000">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="keterangan">Keterangan promo bagi peserta</label>
        <input type="text" id="keterangan" name="keterangan" class="form-input" placeholder="Contoh: Diskon perayaan dies natalis fakultas teknik">
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
        <button type="submit" class="btn btn-primary">
          Simpan kode promo
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
