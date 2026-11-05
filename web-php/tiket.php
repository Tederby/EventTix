<?php
// web/tiket.php
require_once __DIR__ . '/includes/init.php';

$kodeTiket = trim($_GET['kode'] ?? '');
$tiket = null;

if (!empty($kodeTiket)) {
    $tiket = $dataManager->getTicketByCode($kodeTiket);
}

// Jika kode tidak ada atau tidak valid, tampilkan tiket aktif pertama
if (!$tiket) {
    $allTickets = $dataManager->getTickets();
    if (!empty($allTickets)) {
        $tiket = $allTickets[0];
    }
}

$event = $tiket ? $dataManager->getEventById($tiket->getIdEvent()) : null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$pageTitle = $tiket ? 'Tiket Digital — ' . $tiket->getKodeTiket() : 'Tiket Digital';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success print-hide" style="margin-bottom: 2rem;">
    <strong><?= htmlspecialchars($flashSuccess) ?></strong> Tunjukkan tiket digital ini di pintu masuk saat acara.
  </div>
<?php endif; ?>

<div class="ticket-digital-wrapper">
  <!-- Bar Navigasi Aksi Atas -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;" class="print-hide">
    <a href="index.php" style="font-size: var(--text-14); font-weight: 600; color: var(--color-biru-abu); text-decoration: underline;">
      Kembali ke katalog
    </a>
    <button onclick="window.print()" class="btn btn-secondary btn-sm" style="display: inline-flex; gap: 0.5rem; align-items: center;">
      <span>Cetak tiket</span>
    </button>
  </div>

  <?php if (!$tiket || !$event): ?>
    <div class="empty-state">
      <h2 style="font-size: var(--text-20); margin-bottom: 0.5rem;">Belum ada tiket</h2>
      <p>Belum ada tiket yang diterbitkan untuk akun ini. Pilih event untuk mulai memesan.</p>
      <a href="index.php" class="btn btn-primary">Pesan tiket sekarang</a>
    </div>
  <?php else: ?>
    <?php
      $isVIP = $tiket instanceof TiketVIP;
      $status = $tiket->getStatusTiket();
      $stampClass = 'stamp--aktif';
      $stampText = 'AKTIF';
      if ($status === 'TERPAKAI') {
          $stampClass = 'stamp--terpakai';
          $stampText = 'TERPAKAI';
      } elseif ($status === 'DIBATALKAN') {
          $stampClass = 'stamp--ditolak';
          $stampText = 'BATAL';
      }
    ?>

    <!-- Karcis Fisik Utuh dengan Kupon Sobek -->
    <article class="ticket-card <?= $isVIP ? 'ticket-card--vip' : '' ?>" style="border-width: 3px;">
      
      <!-- BADAN TIKET UTAMA -->
      <div class="ticket-body" style="padding: 2rem;">
        <div>
          <!-- Header Karcis -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--color-tinta); padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <div>
              <div style="font-family: var(--font-display); font-size: var(--text-20); font-weight: 800; letter-spacing: -0.02em;">
                EVENTTIX PASS RESMI
              </div>
              <div style="font-family: var(--font-mono); font-size: var(--text-12); color: var(--color-biru-abu);">
                KODE TIKET: <?= htmlspecialchars($tiket->getKodeTiket()) ?>
              </div>
            </div>

            <?php if ($isVIP): ?>
              <div class="ticket-vip-ribbon">VIP ALL-ACCESS</div>
            <?php else: ?>
              <div style="font-family: var(--font-mono); font-size: var(--text-12); font-weight: 700; background: var(--color-karton); padding: 4px 8px; border: 1px solid var(--color-tinta);">
                REGULER PASS
              </div>
            <?php endif; ?>
          </div>

          <!-- Nama Event -->
          <h1 style="font-size: var(--text-28); margin-bottom: 1rem; line-height: 1.2;">
            <?= htmlspecialchars($event->getNamaEvent()) ?>
          </h1>

          <!-- Rincian Acara & Peserta -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.25rem; margin-top: 1.5rem; margin-bottom: 1.5rem; background-color: #F8FAFD; padding: 1rem; border: 1px solid var(--color-garis-halus);">
            <div>
              <div style="font-size: var(--text-12); color: var(--color-biru-abu); font-weight: 600; text-transform: uppercase;">Waktu acara</div>
              <div style="font-size: var(--text-14); font-weight: 700; color: var(--color-tinta);"><?= tanggalIndo($event->getTanggal()) ?></div>
            </div>
            <div>
              <div style="font-size: var(--text-12); color: var(--color-biru-abu); font-weight: 600; text-transform: uppercase;">Gedung / Lokasi</div>
              <div style="font-size: var(--text-14); font-weight: 700; color: var(--color-tinta);"><?= htmlspecialchars($event->getLokasi()) ?></div>
            </div>
            <div>
              <div style="font-size: var(--text-12); color: var(--color-biru-abu); font-weight: 600; text-transform: uppercase;">Nama peserta</div>
              <div style="font-size: var(--text-14); font-weight: 700; color: var(--color-tinta);"><?= htmlspecialchars($currentUser->getNama()) ?></div>
            </div>
            <div>
              <div style="font-size: var(--text-12); color: var(--color-biru-abu); font-weight: 600; text-transform: uppercase;">NIM Mahasiswa</div>
              <div style="font-size: var(--text-14); font-family: var(--font-mono); font-weight: 700; color: var(--color-tinta);">
                <?= htmlspecialchars($currentUser instanceof Peserta ? $currentUser->getNim() : '21120124130051') ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel Kode Verifikasi Resmi (Fungsional & Otentik) -->
        <div style="background-color: #FAFBFD; border: 1px dashed var(--color-biru-abu); padding: 0.75rem 1rem; margin-top: 1.5rem; text-align: center; border-radius: 2px;">
          <div style="font-size: var(--text-12); font-weight: 700; color: var(--color-biru-abu); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
            Kode Verifikasi Resmi Peserta
          </div>
          <div style="font-family: var(--font-mono); font-size: var(--text-16); font-weight: 700; color: var(--color-tinta); letter-spacing: 0.1em;">
            <?= htmlspecialchars($tiket->getKodeTiket()) ?>
          </div>
        </div>
      </div>

      <!-- GARIS PERFORASI PUTUS-PUTUS & NOTCH BULAT -->
      <div class="ticket-perforation">
        <div class="ticket-notch-top"></div>
        <div class="ticket-notch-bottom"></div>
      </div>

      <!-- KUPON SOBEK (TEAR-OFF STUB) SISI KANAN -->
      <div class="ticket-stub" style="padding: 1.5rem 1.25rem; justify-content: space-between; align-items: center; text-align: center; box-sizing: border-box; overflow: hidden;">
        
        <!-- Header Kupon -->
        <div style="width: 100%;">
          <div style="font-family: var(--font-mono); font-size: var(--text-12); color: var(--color-biru-abu); text-transform: uppercase; letter-spacing: 0.05em;">
            KUPON SOBEK PINTU
          </div>
          <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.25rem;">
            Tunjukkan ke panitia
          </div>

          <?php if ($isVIP): ?>
            <div class="ticket-seat-badge" style="margin-top: 1rem; width: 100%;">
              KURSI VIP: <?= htmlspecialchars($tiket->getNomorKursi()) ?>
            </div>
          <?php else: ?>
            <div style="font-family: var(--font-mono); font-size: var(--text-12); background-color: var(--color-karton); padding: 4px 8px; border: 1px solid var(--color-tinta); margin-top: 1rem;">
              KURSI REGULER BEBAS
            </div>
          <?php endif; ?>
        </div>

        <!-- CAP STATUS TIKET (TITIK KEBERANIAN VISUAL) -->
        <div style="margin: 1.5rem 0;">
          <div class="stamp <?= $stampClass ?> stamp-lg">
            <span><?= $stampText ?></span>
            <span style="font-size: 9px; letter-spacing: 0.05em; font-family: var(--font-mono); font-weight: normal; margin-top: 2px;">
              <?= $status === 'AKTIF' ? 'SIAP CHECK-IN' : ($status === 'TERPAKAI' ? 'SUDAH MASUK' : 'NON-AKTIF') ?>
            </span>
          </div>
        </div>

        <!-- Kode Tiket Besar IBM Plex Mono yang Terbaca dari Jauh -->
        <div style="width: 100%;">
          <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-bottom: 0.25rem;">KODE VALIDASI:</div>
          <div class="ticket-code-display" style="width: 100%;">
            <?= htmlspecialchars($tiket->getKodeTiket()) ?>
          </div>
        </div>

      </div>
    </article>

    <!-- Catatan Validasi Bawah -->
    <div style="background-color: var(--color-putih); border: 1px solid var(--color-garis-halus); padding: 1rem 1.5rem; border-radius: 2px; font-size: var(--text-14); color: var(--color-biru-abu); margin-top: 1.5rem;">
      <strong>Petunjuk pintu masuk:</strong> Tiket ini adalah bukti sah kepesertaan seminar. Satu tiket hanya dapat divalidasi satu kali oleh petugas check-in menggunakan barcode scanner atau nomor kode di atas.
    </div>

    <!-- Daftar Tiket Lain jika Ada -->
    <?php
      $allUserTickets = $dataManager->getTickets();
    ?>
    <?php if (count($allUserTickets) > 1): ?>
      <div style="margin-top: 3rem;" class="print-hide">
        <h2 style="font-size: var(--text-20); margin-bottom: 1rem;">Daftar tiket lainnya</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <?php foreach ($allUserTickets as $t): ?>
            <?php if ($t->getKodeTiket() !== $tiket->getKodeTiket()): ?>
              <a href="tiket.php?kode=<?= urlencode($t->getKodeTiket()) ?>" class="btn btn-secondary btn-sm">
                Lihat <?= htmlspecialchars($t->getKodeTiket()) ?> (<?= htmlspecialchars($t->getJenis()) ?>, <?= htmlspecialchars($t->getStatusTiket()) ?>)
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
