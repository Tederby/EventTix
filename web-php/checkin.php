<?php
// web/checkin.php
require_once __DIR__ . '/includes/init.php';

$kodeInput = strtoupper(trim($_POST['kode_tiket'] ?? $_GET['kode_tiket'] ?? ''));
$hasilCheckIn = null;
$tiketDitemukan = null;
$eventTerkait = null;
$pemesanTerkait = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($kodeInput)) {
    $tiketDitemukan = $dataManager->getTicketByCode($kodeInput);

    $idCheckIn = 'CHK-' . strtoupper(bin2hex(random_bytes(3)));
    $checkInObj = new CheckIn($idCheckIn, $kodeInput, 'MENUNGGU', '', $currentUser->getNama());

    if ($tiketDitemukan) {
        $eventTerkait = $dataManager->getEventById($tiketDitemukan->getIdEvent());
        
        // Ambil data pemesan dari order
        $order = $dataManager->getOrderById($tiketDitemukan->getIdPesanan());
        if ($order) {
            $pemesanTerkait = $dataManager->getUserById($order['idPeserta']);
        }

        // Jalankan validasi tiket menggunakan metode OOP
        $isValid = $checkInObj->validasiTiket($tiketDitemukan);
        
        // Simpan perubahan status tiket dan log check-in
        $dataManager->saveTicket($tiketDitemukan);
        $dataManager->saveCheckIn($checkInObj);

        $hasilCheckIn = [
            'status' => $checkInObj->getStatusValidasi(), // 'VALID' atau 'DITOLAK'
            'catatan' => $checkInObj->getCatatan(),
            'waktu' => $checkInObj->getWaktuCheckIn(),
            'kode' => $tiketDitemukan->getKodeTiket(),
            'jenis' => $tiketDitemukan->getJenis(),
            'nomorKursi' => $tiketDitemukan instanceof TiketVIP ? $tiketDitemukan->getNomorKursi() : 'Reguler',
            'namaEvent' => $eventTerkait ? $eventTerkait->getNamaEvent() : 'Seminar Kampus',
            'namaPeserta' => $pemesanTerkait ? $pemesanTerkait->getNama() : 'Peserta Mahasiswa',
            'nimPeserta' => ($pemesanTerkait instanceof Peserta) ? $pemesanTerkait->getNim() : '21120124130051'
        ];
    } else {
        $checkInObj = new CheckIn($idCheckIn, $kodeInput, 'DITOLAK', 'Kode tiket tidak ditemukan dalam sistem. Periksa kembali karakter kode tiket atau pastikan tiket telah dibayar.', $currentUser->getNama());
        $dataManager->saveCheckIn($checkInObj);

        $hasilCheckIn = [
            'status' => 'DITOLAK',
            'catatan' => $checkInObj->getCatatan(),
            'waktu' => $checkInObj->getWaktuCheckIn(),
            'kode' => $kodeInput,
            'jenis' => '-',
            'nomorKursi' => '-',
            'namaEvent' => '-',
            'namaPeserta' => '-',
            'nimPeserta' => '-'
        ];
    }
}

$daftarLogCheckIn = $dataManager->getCheckIns();

$pageTitle = 'Validasi Check-in Pintu Masuk';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 2rem;">
  <h1 style="font-size: var(--text-28); margin-bottom: 0.5rem;">Validasi check-in pintu</h1>
  <p style="color: var(--color-biru-abu); font-size: var(--text-16); margin: 0;">
    Pindai kode batang fisik tiket atau ketik kode tiket untuk memvalidasi akses peserta.
  </p>
</div>

<!-- FORM INPUT TIKET TUNGGAL BESAR & FOKUS OTOMATIS -->
<div style="background-color: var(--color-putih); border: var(--border-tegas); padding: 2rem; border-radius: var(--radius-karcis); margin-bottom: 2.5rem; box-shadow: 4px 4px 0 var(--color-tinta);">
  <form method="POST" action="checkin.php" autocomplete="off">
    <div class="form-group" style="margin-bottom: 1.5rem;">
      <label class="form-label" for="input-kode-checkin" style="font-size: var(--text-16);">
        Masukkan kode tiket peserta
      </label>
      <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <input 
          type="text" 
          id="input-kode-checkin" 
          name="kode_tiket" 
          class="form-input input-huge" 
          placeholder="CONTOH: ETX-2026-VIP-910" 
          required 
          autofocus 
          value="<?= htmlspecialchars($kodeInput) ?>"
          style="flex: 1; min-width: 280px;"
        >
        <button type="submit" class="btn btn-primary btn-lg" style="white-space: nowrap;">
          Check-in tiket
        </button>
      </div>
      <div style="font-size: var(--text-14); color: var(--color-biru-abu); margin-top: 0.5rem;">
        Kolom ini otomatis fokus setiap pemindaian. Mendukung pemindai USB barcode scanner dan pengetikan manual.
      </div>
    </div>
  </form>

  <!-- Tombol Contoh Cepat untuk Kemudahan Pengujian Antarmuka -->
  <div style="font-size: var(--text-12); color: var(--color-biru-abu); display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; border-top: 1px dashed var(--color-garis-halus); padding-top: 1rem;">
    <span>Uji kode sampel:</span>
    <form method="POST" action="checkin.php" style="display: inline;">
      <input type="hidden" name="kode_tiket" value="ETX-2026-VIP-910">
      <button type="submit" class="btn btn-secondary btn-sm" style="padding: 2px 8px; min-height: 28px; font-size: var(--text-12);">
        ETX-2026-VIP-910 (VIP)
      </button>
    </form>
  </div>
</div>

<!-- HASIL VALIDASI CHECK-IN DENGAN ANIMASI HENTAKAN CAP -->
<?php if ($hasilCheckIn): ?>
  <?php 
    $isValid = $hasilCheckIn['status'] === 'VALID';
    $borderBoxColor = $isValid ? 'var(--color-hijau-cap)' : 'var(--color-merah-cap)';
    $bgBoxColor = $isValid ? '#F0F9F4' : '#FDF2F2';
  ?>

  <div style="background-color: <?= $bgBoxColor ?>; border: 3px solid <?= $borderBoxColor ?>; padding: 2rem; border-radius: var(--radius-karcis); margin-bottom: 3rem; display: flex; flex-direction: column; gap: 1.5rem;">
    
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
      <!-- HENTAKAN CAP SATU-SATUNYA ANIMASI KHUSUS -->
      <div>
        <?php if ($isValid): ?>
          <div class="stamp stamp--valid stamp-lg stamp--animated">
            <span style="font-size: 2.25rem;">VALID</span>
            <span style="font-size: 11px; letter-spacing: 0.05em; font-family: var(--font-mono); margin-top: 4px;">
              AKSES DIIZINKAN MASUK
            </span>
          </div>
        <?php else: ?>
          <div class="stamp stamp--ditolak stamp-lg stamp--animated">
            <span style="font-size: 2.25rem;">DITOLAK</span>
            <span style="font-size: 11px; letter-spacing: 0.05em; font-family: var(--font-mono); margin-top: 4px;">
              AKSES TIDAK SAH
            </span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Penjelasan Kalimat Jelas Non-Basa Basi -->
      <div style="flex: 1; min-width: 260px;">
        <h2 style="font-size: var(--text-20); color: <?= $borderBoxColor ?>; margin-bottom: 0.5rem;">
          <?= $isValid ? 'Tiket berhasil divalidasi' : 'Validasi tiket ditolak' ?>
        </h2>
        <p style="font-size: var(--text-16); font-weight: 500; color: var(--color-tinta); margin-bottom: 0.5rem;">
          <?= htmlspecialchars($hasilCheckIn['catatan']) ?>
        </p>
        <div style="font-size: var(--text-12); font-family: var(--font-mono); color: var(--color-biru-abu);">
          Waktu verifikasi: <?= tanggalIndo($hasilCheckIn['waktu']) ?>
        </div>
      </div>
    </div>

    <!-- Rincian Identitas Peserta Jika Tiket Sah / Ditemukan -->
    <?php if ($isValid): ?>
      <div style="background-color: var(--color-putih); border: 1px solid var(--color-garis-halus); padding: 1.25rem; border-radius: 2px;">
        <div style="font-size: var(--text-12); font-weight: 700; color: var(--color-biru-abu); text-transform: uppercase; margin-bottom: 0.75rem;">
          Data kepesertaan terverifikasi:
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
          <div>
            <div style="font-size: var(--text-12); color: var(--color-biru-abu);">Nama peserta</div>
            <div style="font-size: var(--text-16); font-weight: 700; color: var(--color-tinta);"><?= htmlspecialchars($hasilCheckIn['namaPeserta']) ?></div>
          </div>
          <div>
            <div style="font-size: var(--text-12); color: var(--color-biru-abu);">NIM Mahasiswa</div>
            <div style="font-size: var(--text-16); font-family: var(--font-mono); font-weight: 700; color: var(--color-tinta);"><?= htmlspecialchars($hasilCheckIn['nimPeserta']) ?></div>
          </div>
          <div>
            <div style="font-size: var(--text-12); color: var(--color-biru-abu);">Kategori tiket & kursi</div>
            <div style="font-size: var(--text-16); font-weight: 700; color: var(--color-tinta);">
              Tiket <?= htmlspecialchars($hasilCheckIn['jenis']) ?>
              <?php if ($hasilCheckIn['nomorKursi'] !== 'Reguler'): ?>
                <span class="ticket-seat-badge" style="margin-left: 0.5rem; font-size: var(--text-12);"><?= htmlspecialchars($hasilCheckIn['nomorKursi']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <div style="font-size: var(--text-12); color: var(--color-biru-abu);">Acara seminar</div>
            <div style="font-size: var(--text-14); font-weight: 600; color: var(--color-tinta);"><?= htmlspecialchars($hasilCheckIn['namaEvent']) ?></div>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>
<?php endif; ?>

<!-- REKAP RIWAYAT CHECK-IN PINTU MASUK -->
<div>
  <h2 style="font-size: var(--text-20); margin-bottom: 1rem;">Riwayat validasi terakhir</h2>

  <?php if (empty($daftarLogCheckIn)): ?>
    <div class="empty-state">
      <p>Belum ada aktivitas check-in pada sesi ini. Pindai tiket pertama untuk mencatat kehadiran.</p>
    </div>
  <?php else: ?>
    <div class="data-table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Waktu verifikasi</th>
            <th>Kode tiket</th>
            <th>Status cap</th>
            <th>Hasil keterangan</th>
            <th>Petugas pintu</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_reverse($daftarLogCheckIn) as $log): ?>
            <tr>
              <td style="font-family: var(--font-mono); font-size: var(--text-12);"><?= htmlspecialchars($log->getWaktuCheckIn()) ?></td>
              <td style="font-family: var(--font-mono); font-weight: 700;"><?= htmlspecialchars($log->getKodeTiket()) ?></td>
              <td>
                <?php if ($log->getStatusValidasi() === 'VALID'): ?>
                  <span style="font-family: var(--font-display); font-weight: 700; color: var(--color-hijau-cap); font-size: var(--text-12); border: 1px solid var(--color-hijau-cap); padding: 2px 6px;">VALID</span>
                <?php else: ?>
                  <span style="font-family: var(--font-display); font-weight: 700; color: var(--color-merah-cap); font-size: var(--text-12); border: 1px solid var(--color-merah-cap); padding: 2px 6px;">DITOLAK</span>
                <?php endif; ?>
              </td>
              <td style="max-width: 380px;"><?= htmlspecialchars($log->getCatatan()) ?></td>
              <td style="color: var(--color-biru-abu);"><?= htmlspecialchars($log->getNamaPetugas()) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

