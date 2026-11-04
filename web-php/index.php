<?php
// web/index.php
require_once __DIR__ . '/includes/init.php';

$allEvents = $dataManager->getEvents();

// Filter: jangan tampilkan event yang berstatus Dihapus
$events = array_values(array_filter($allEvents, function($ev) {
    return $ev->getStatusEvent() !== 'Dihapus';
}));

// Pisahkan event terdekat sebagai hero dan event lainnya
$eventTerdekat = $events[0] ?? null;
$eventLainnya = array_slice($events, 1);

function renderSegmentedBar(int $kuota, int $terjual): string {
    $totalSegments = 10;
    $filledSegments = $kuota > 0 ? (int)round(($terjual / $kuota) * $totalSegments) : $totalSegments;
    $filledSegments = max(0, min($totalSegments, $filledSegments));
    
    $html = '<div class="segmented-bar" aria-label="Kapasitas kuota ' . $terjual . ' terisi dari ' . $kuota . '">';
    for ($i = 0; $i < $totalSegments; $i++) {
        $class = $i < $filledSegments ? 'segment-cell filled' : 'segment-cell';
        $html .= '<div class="' . $class . '"></div>';
    }
    $html .= '</div>';
    return $html;
}

$pageTitle = 'Katalog Event & Seminar Kampus';
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 2rem;">
  <h1 style="margin-bottom: 0.5rem;">Katalog seminar dan event</h1>
  <p style="color: var(--color-biru-abu); font-size: var(--text-16);">
    Pilih tiket acara akademik, workshop industri, dan kompetisi mahasiswa.
  </p>
</div>

<?php if ($eventTerdekat): ?>
  <div style="margin-bottom: 3rem;">
    <h2 style="font-size: var(--text-20); margin-bottom: 1rem; color: var(--color-tinta);">Acara terdekat</h2>
    
    <!-- Karcis Besar Event Terdekat -->
    <article class="ticket-card ticket-card--hero">
      <div class="ticket-body">
        <div>
          <div style="font-family: var(--font-mono); font-size: var(--text-14); color: var(--color-biru-abu); margin-bottom: 0.5rem;">
            <?= htmlspecialchars($eventTerdekat->getKategori()) ?> / Kode #<?= htmlspecialchars($eventTerdekat->getIdEvent()) ?>
          </div>
          <h2 style="font-size: var(--text-28); margin-bottom: 1rem;">
            <?= htmlspecialchars($eventTerdekat->getNamaEvent()) ?>
          </h2>
          <p class="event-deskripsi" style="color: var(--color-biru-abu); font-size: var(--text-16); margin-bottom: 1.5rem;">
            <?= htmlspecialchars($eventTerdekat->getDeskripsi()) ?>
          </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-garis-halus);">
          <div>
            <div style="font-size: var(--text-12); color: var(--color-biru-abu); text-transform: uppercase; font-weight: 600;">Waktu pelaksanaan</div>
            <div style="font-size: var(--text-14); font-weight: 600;"><?= tanggalIndo($eventTerdekat->getTanggal()) ?></div>
          </div>
          <div>
            <div style="font-size: var(--text-12); color: var(--color-biru-abu); text-transform: uppercase; font-weight: 600;">Tempat kegiatan</div>
            <div style="font-size: var(--text-14); font-weight: 600;"><?= htmlspecialchars($eventTerdekat->getLokasi()) ?></div>
          </div>
        </div>
      </div>

      <!-- Garis Perforasi Fisik & Potongan Lengkung -->
      <div class="ticket-perforation">
        <div class="ticket-notch-top"></div>
        <div class="ticket-notch-bottom"></div>
      </div>

      <!-- Kupon Sobek Sisi Kanan -->
      <div class="ticket-stub">
        <div>
          <div style="font-size: var(--text-12); color: var(--color-biru-abu); font-weight: 600; text-transform: uppercase;">Status ketersediaan</div>
          <div class="quota-box">
            <div class="quota-label">
              <span>Sisa kuota kursi</span>
              <span style="font-family: var(--font-mono); font-weight: 700; color: var(--color-tinta);">
                <?= $eventTerdekat->getSisaKuota() ?> / <?= $eventTerdekat->getKuota() ?>
              </span>
            </div>
            <?= renderSegmentedBar($eventTerdekat->getKuota(), $eventTerdekat->getTiketTerjual()) ?>
            <div style="font-size: var(--text-12); color: var(--color-biru-abu);">
              <?= $eventTerdekat->getTiketTerjual() ?> kursi telah terdaftar
            </div>
          </div>
        </div>

        <div>
          <div style="margin-bottom: 1rem;">
            <div style="font-size: var(--text-12); color: var(--color-biru-abu); text-transform: uppercase; font-weight: 600;">Mulai dari</div>
            <div style="font-family: var(--font-display); font-size: var(--text-28); font-weight: 800; color: var(--color-tinta);">
              <?= rupiah($eventTerdekat->getHargaDasar()) ?>
            </div>
          </div>

          <?php if ($eventTerdekat->cekKetersediaan(1)): ?>
            <a href="event-detail.php?id=<?= urlencode($eventTerdekat->getIdEvent()) ?>" class="btn btn-primary" style="width: 100%;">
              Pesan tiket
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled style="width: 100%; opacity: 0.6; cursor: not-allowed; border-color: var(--color-merah-cap); color: var(--color-merah-cap);">
              <?= $eventTerdekat->getStatusEvent() === 'Tutup' ? 'Penjualan ditutup' : 'Tiket habis' ?>
            </button>
          <?php endif; ?>
        </div>
      </div>
    </article>
  </div>
<?php endif; ?>

<!-- Daftar Acara Lainnya sebagai Karcis Horizontal Ringkas -->
<div style="margin-top: 2rem;">
  <h2 style="font-size: var(--text-20); margin-bottom: 1rem; color: var(--color-tinta);">Daftar acara lainnya</h2>

  <?php if (empty($eventLainnya)): ?>
    <div class="empty-state">
      <p>Belum ada acara tambahan saat ini. Silakan kembali lagi nanti untuk melihat jadwal terbaru.</p>
    </div>
  <?php else: ?>
    <?php foreach ($eventLainnya as $event): ?>
      <article class="ticket-card ticket-card--compact">
        <div class="ticket-body">
          <div>
            <div style="font-family: var(--font-mono); font-size: var(--text-12); color: var(--color-biru-abu); margin-bottom: 0.25rem;">
              <?= htmlspecialchars($event->getKategori()) ?>, <?= tanggalRingkas($event->getTanggal()) ?>
            </div>
            <h3 style="font-size: var(--text-20); margin-bottom: 0.5rem;">
              <a href="event-detail.php?id=<?= urlencode($event->getIdEvent()) ?>">
                <?= htmlspecialchars($event->getNamaEvent()) ?>
              </a>
            </h3>
            <p style="font-size: var(--text-14); color: var(--color-biru-abu); margin-bottom: 0.75rem;">
              <?= htmlspecialchars($event->getLokasi()) ?>
            </p>
          </div>

          <div style="max-width: 280px;">
            <div class="quota-label" style="font-size: var(--text-12);">
              <span>Kapasitas peserta</span>
              <span style="font-family: var(--font-mono); font-weight: 700;"><?= $event->getSisaKuota() ?> kursi</span>
            </div>
            <?= renderSegmentedBar($event->getKuota(), $event->getTiketTerjual()) ?>
          </div>
        </div>

        <div class="ticket-perforation">
          <div class="ticket-notch-top"></div>
          <div class="ticket-notch-bottom"></div>
        </div>

        <div class="ticket-stub" style="align-items: flex-end; text-align: right;">
          <div>
            <div style="font-size: var(--text-12); color: var(--color-biru-abu);">Harga tiket</div>
            <div style="font-family: var(--font-display); font-size: var(--text-20); font-weight: 800; color: var(--color-tinta); margin-bottom: 0.75rem;">
              <?= rupiah($event->getHargaDasar()) ?>
            </div>
          </div>

          <?php if ($event->cekKetersediaan(1)): ?>
            <a href="event-detail.php?id=<?= urlencode($event->getIdEvent()) ?>" class="btn btn-primary btn-sm" style="width: 100%;">
              Pesan tiket
            </a>
          <?php else: ?>
            <button class="btn btn-secondary btn-sm" disabled style="width: 100%; opacity: 0.6; cursor: not-allowed;">
              <?= $event->getStatusEvent() === 'Tutup' ? 'Ditutup' : 'Tiket habis' ?>
            </button>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
