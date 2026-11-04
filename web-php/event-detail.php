<?php
// web/event-detail.php
require_once __DIR__ . '/includes/init.php';

$idEvent = $_GET['id'] ?? 'EVT-01';
$event = $dataManager->getEventById($idEvent);

if (!$event) {
    header('Location: index.php');
    exit;
}

// Model Tiket Dummy untuk Mengambil Fasilitas & Kalkulasi Polimorfisme
$sampleReguler = new TiketReguler('SAMPLE-REG', $event->getHargaDasar());
$sampleVIP = new TiketVIP('SAMPLE-VIP', $event->getHargaDasar(), 35000, 'A-04');

$pageTitle = 'Detail Event — ' . $event->getNamaEvent();
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
  <a href="index.php" style="font-size: var(--text-14); font-weight: 600; color: var(--color-biru-abu); text-decoration: underline;">
    Kembali ke katalog event
  </a>
</div>

<div style="display: grid; grid-template-columns: 1fr; gap: 2rem; margin-bottom: 3rem;">
  <!-- Karcis Event di Bagian Atas / Kiri -->
  <article class="ticket-card ticket-card--hero">
    <div class="ticket-body">
      <div>
        <div style="font-family: var(--font-mono); font-size: var(--text-14); color: var(--color-biru-abu); margin-bottom: 0.5rem;">
          <?= htmlspecialchars($event->getKategori()) ?> / Kode Event: <?= htmlspecialchars($event->getIdEvent()) ?>
        </div>
        <h1 style="font-size: var(--text-28); margin-bottom: 1rem;">
          <?= htmlspecialchars($event->getNamaEvent()) ?>
        </h1>
        <p class="event-deskripsi" style="color: var(--color-biru-abu); font-size: var(--text-16); margin-bottom: 1.5rem;">
          <?= htmlspecialchars($event->getDeskripsi()) ?>
        </p>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-garis-halus);">
        <div>
          <div style="font-size: var(--text-12); color: var(--color-biru-abu); font-weight: 600; text-transform: uppercase;">Waktu & tanggal</div>
          <div style="font-size: var(--text-14); font-weight: 600;"><?= tanggalIndo($event->getTanggal()) ?></div>
        </div>
        <div>
          <div style="font-size: var(--text-12); color: var(--color-biru-abu); font-weight: 600; text-transform: uppercase;">Lokasi seminar</div>
          <div style="font-size: var(--text-14); font-weight: 600;"><?= htmlspecialchars($event->getLokasi()) ?></div>
        </div>
      </div>
    </div>

    <div class="ticket-perforation">
      <div class="ticket-notch-top"></div>
      <div class="ticket-notch-bottom"></div>
    </div>

    <div class="ticket-stub">
      <div>
        <div style="font-size: var(--text-12); color: var(--color-biru-abu); font-weight: 600; text-transform: uppercase;">Status kuota</div>
        <div style="font-family: var(--font-display); font-size: var(--text-20); font-weight: 800; margin-top: 0.25rem;">
          <?= $event->getSisaKuota() ?> kursi tersisa
        </div>
        <p style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.5rem;">
          Dari total kapasitas <?= $event->getKuota() ?> kursi di auditorium utama.
        </p>
      </div>

      <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed var(--color-garis-halus);">
        <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-bottom: 0.25rem;">Status registrasi</div>
        <?php if ($event->getStatusEvent() === 'Buka' && !$event->isPenuh()): ?>
          <div style="font-weight: 700; color: var(--color-hijau-cap); font-size: var(--text-16); font-family: var(--font-mono);">
            PENDAFTARAN DIBUKA
          </div>
        <?php elseif ($event->getStatusEvent() === 'Tutup'): ?>
          <div style="font-weight: 700; color: var(--color-merah-cap); font-size: var(--text-16); font-family: var(--font-mono);">
            PENJUALAN DITUTUP
          </div>
        <?php else: ?>
          <div style="font-weight: 700; color: var(--color-merah-cap); font-size: var(--text-16); font-family: var(--font-mono);">
            KUOTA PENUH
          </div>
        <?php endif; ?>
      </div>
    </div>
  </article>

  <!-- Pemilih Kategori Tiket: Reguler vs VIP dengan Perbandingan Fasilitas -->
  <div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
      <h2 style="font-size: var(--text-20); margin: 0;">Pilihan kategori tiket</h2>
      <?php if (!$event->cekKetersediaan(1)): ?>
        <span style="font-family: var(--font-mono); font-weight: 700; color: var(--color-merah-cap); border: 1px solid var(--color-merah-cap); padding: 4px 10px; font-size: var(--text-12);">
          <?= $event->getStatusEvent() === 'Tutup' ? 'STATUS: PENJUALAN TELAH DITUTUP' : 'STATUS: KUOTA TIKET HABIS' ?>
        </span>
      <?php endif; ?>
    </div>

    <?php if (!$event->cekKetersediaan(1)): ?>
      <div style="background-color: #FFF2F2; border: 1px solid var(--color-merah-cap); color: var(--color-merah-cap); padding: 1rem 1.25rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: var(--text-14);">
        <strong>Pemberitahuan:</strong> 
        <?= $event->getStatusEvent() === 'Tutup' 
            ? 'Penjualan tiket untuk kegiatan ini telah resmi ditutup oleh panitia. Anda tidak dapat membuat pesanan baru untuk kegiatan ini.' 
            : 'Kuota kursi peserta untuk kegiatan ini telah terisi penuh. Pembelian tiket baru tidak dapat dilakukan.' ?>
      </div>
    <?php endif; ?>

    <form method="GET" action="checkout.php">
      <input type="hidden" name="event_id" value="<?= htmlspecialchars($event->getIdEvent()) ?>">

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Opsi Tiket Reguler -->
        <label class="ticket-card" style="margin-bottom: 0; cursor: <?= $event->cekKetersediaan(1) ? 'pointer' : 'not-allowed' ?>; display: flex; flex-direction: column; padding: 1.5rem; border: 2px solid var(--color-tinta); opacity: <?= $event->cekKetersediaan(1) ? '1' : '0.65' ?>;">
          <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem;">
            <div>
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="radio" name="jenis_tiket" value="Reguler" checked <?= !$event->cekKetersediaan(1) ? 'disabled' : '' ?> style="width: 20px; height: 20px; accent-color: var(--color-tinta);">
                <span style="font-family: var(--font-display); font-size: var(--text-20); font-weight: 800;">Tiket Reguler</span>
              </div>
              <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.25rem;">Kategori umum mahasiswa</div>
            </div>
            <div style="text-align: right;">
              <div style="font-size: var(--text-12); color: var(--color-biru-abu);">Harga final</div>
              <div style="font-family: var(--font-display); font-size: var(--text-20); font-weight: 800; color: var(--color-tinta);">
                <?= rupiah($sampleReguler->hitungHarga()) ?>
              </div>
            </div>
          </div>

          <div style="border-top: 1px dashed var(--color-garis-halus); padding-top: 1rem; margin-top: 0.5rem;">
            <div style="font-size: var(--text-12); font-weight: 700; text-transform: uppercase; color: var(--color-biru-abu); margin-bottom: 0.5rem;">Fasilitas peserta:</div>
            <ul style="padding-left: 1.25rem; font-size: var(--text-14); color: var(--color-tinta); display: flex; flex-direction: column; gap: 0.4rem;">
              <?php foreach ($sampleReguler->getFasilitas() as $fasilitas): ?>
                <li><?= htmlspecialchars($fasilitas) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </label>

        <!-- Opsi Tiket VIP (Ciri Fisik Pita Bersambung) -->
        <label class="ticket-card ticket-card--vip" style="margin-bottom: 0; cursor: <?= $event->cekKetersediaan(1) ? 'pointer' : 'not-allowed' ?>; display: flex; flex-direction: column; padding: 1.5rem; border: 2px solid var(--color-tinta); position: relative; background-color: #FFFCF5; opacity: <?= $event->cekKetersediaan(1) ? '1' : '0.65' ?>;">
          <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem;">
            <div>
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="radio" name="jenis_tiket" value="VIP" <?= !$event->cekKetersediaan(1) ? 'disabled' : '' ?> style="width: 20px; height: 20px; accent-color: var(--color-tinta);">
                <span style="font-family: var(--font-display); font-size: var(--text-20); font-weight: 800;">Tiket VIP</span>
              </div>
              <div class="ticket-seat-badge">KURSI TERDAFTAR BARIS DEPAN</div>
            </div>
            <div style="text-align: right;">
              <div style="font-size: var(--text-12); color: var(--color-biru-abu);">Harga final</div>
              <div style="font-family: var(--font-display); font-size: var(--text-20); font-weight: 800; color: var(--color-tinta);">
                <?= rupiah($sampleVIP->hitungHarga()) ?>
              </div>
              <div style="font-size: var(--text-12); color: var(--color-biru-abu);">Termasuk layanan VIP</div>
            </div>
          </div>

          <div style="border-top: 1px dashed var(--color-garis-halus); padding-top: 1rem; margin-top: 0.5rem;">
            <div style="font-size: var(--text-12); font-weight: 700; text-transform: uppercase; color: var(--color-biru-abu); margin-bottom: 0.5rem;">Fasilitas eksklusif:</div>
            <ul style="padding-left: 1.25rem; font-size: var(--text-14); color: var(--color-tinta); display: flex; flex-direction: column; gap: 0.4rem;">
              <?php foreach ($sampleVIP->getFasilitas() as $fasilitas): ?>
                <li><strong><?= htmlspecialchars($fasilitas) ?></strong></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </label>

      </div>

      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background-color: var(--color-putih); border: var(--border-tegas); padding: 1.5rem; border-radius: var(--radius-karcis);">
        <div>
          <?php if ($event->cekKetersediaan(1)): ?>
            <div style="font-size: var(--text-16); font-weight: 700;">Sudah yakin dengan pilihan tiketmu?</div>
            <p style="font-size: var(--text-14); color: var(--color-biru-abu); margin: 0;">Lanjutkan ke ringkasan pesanan dan metode pembayaran.</p>
          <?php else: ?>
            <div style="font-size: var(--text-16); font-weight: 700; color: var(--color-merah-cap);">Pemesanan tidak dapat dilanjutkan</div>
            <p style="font-size: var(--text-14); color: var(--color-biru-abu); margin: 0;">Silakan kembali ke katalog untuk melihat event dan seminar lainnya.</p>
          <?php endif; ?>
        </div>

        <?php if ($event->cekKetersediaan(1)): ?>
          <button type="submit" class="btn btn-primary btn-lg">
            Pesan tiket
          </button>
        <?php else: ?>
          <div style="display: flex; gap: 0.75rem;">
            <a href="index.php" class="btn btn-secondary btn-lg">
              Kembali ke katalog
            </a>
            <button type="button" class="btn btn-secondary btn-lg" disabled style="opacity: 0.5; cursor: not-allowed; border-color: var(--color-merah-cap); color: var(--color-merah-cap);">
              <?= $event->getStatusEvent() === 'Tutup' ? 'Penjualan ditutup' : 'Tiket habis' ?>
            </button>
          </div>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
