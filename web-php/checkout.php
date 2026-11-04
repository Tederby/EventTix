<?php
// web/checkout.php
require_once __DIR__ . '/includes/init.php';

$idEvent = $_REQUEST['event_id'] ?? 'EVT-01';
$event = $dataManager->getEventById($idEvent);

if (!$event) {
    header('Location: index.php');
    exit;
}

// Langkah saat ini (1. Pilih tiket, 2. Promo, 3. Bayar)
$step = isset($_REQUEST['step']) ? (int)$_REQUEST['step'] : 1;
if ($step < 1 || $step > 3) $step = 1;

$jenisTiket = $_REQUEST['jenis_tiket'] ?? 'Reguler';
if (!in_array($jenisTiket, ['Reguler', 'VIP'])) {
    $jenisTiket = 'Reguler';
}

$jumlahTiket = isset($_REQUEST['jumlah']) ? max(1, min(5, (int)$_REQUEST['jumlah'])) : 1;
$kodePromoInput = trim($_REQUEST['kode_promo'] ?? '');
$metodeBayar = $_REQUEST['metode_pembayaran'] ?? 'transfer'; // 'transfer' atau 'ewallet'
$bankTransfer = $_REQUEST['bank_transfer'] ?? 'Mandiri';
$ewalletProvider = $_REQUEST['ewallet_provider'] ?? 'GoPay';

$pesanError = '';
$pesanSuksesPromo = '';

// Pengecekan Ketersediaan Event menggunakan Class Event sesuai Class Diagram
$isEventTersedia = $event->cekKetersediaan($jumlahTiket);
if (!$isEventTersedia) {
    if ($event->getStatusEvent() === 'Tutup') {
        $pesanError = 'Penjualan tiket untuk kegiatan ini telah resmi ditutup oleh panitia. Pemesanan baru tidak dapat diproses.';
    } elseif ($event->isPenuh()) {
        $pesanError = 'Kuota tiket untuk kegiatan ini telah terisi penuh. Pembelian tiket baru tidak dapat dilakukan.';
    } else {
        $pesanError = 'Sisa kuota kegiatan tidak mencukupi untuk ' . $jumlahTiket . ' tiket yang dipilih. Sisa kuota saat ini: ' . $event->getSisaKuota() . ' kursi.';
    }
}

// Buat objek tiket untuk kalkulasi berbasis objek (Polymorphism)
$dummyKode = 'ETX-PREVIEW';
if ($jenisTiket === 'VIP') {
    $tiketObj = new TiketVIP($dummyKode, $event->getHargaDasar(), 35000, 'A-' . rand(1, 20));
} else {
    $tiketObj = new TiketReguler($dummyKode, $event->getHargaDasar());
}

// Inisiasi Pesanan Objek
$pesanan = new Pesanan('ORD-TEMP', $currentUser->getIdUser(), $event->getIdEvent());
for ($i = 0; $i < $jumlahTiket; $i++) {
    $pesanan->tambahTiket($tiketObj);
}

// Evaluasi Promo jika dimasukkan
$promoObj = null;
if (!empty($kodePromoInput)) {
    $promoObj = $dataManager->getPromoByCode($kodePromoInput);
    if ($promoObj) {
        if ($promoObj->isValid()) {
            $pesanan->terapkanPromo($promoObj);
            $pesanSuksesPromo = 'Kode promo ' . htmlspecialchars($promoObj->getKode()) . ' berhasil diterapkan.';
        } else {
            $pesanError = 'Kode promo sudah kedaluwarsa atau kuotanya telah habis. Periksa kode promo atau lanjutkan tanpa promo.';
        }
    } else {
        $pesanError = 'Kode promo tidak ditemukan dalam sistem. Periksa kembali penulisan kode promo atau lanjutkan tanpa promo.';
    }
}

// Buat Objek Pembayaran
$subtotal = $pesanan->getSubtotal();
if ($metodeBayar === 'ewallet') {
    $pembayaranObj = new PembayaranEWallet('PAY-TEMP', $subtotal, $ewalletProvider, '0812-3456-7890');
} else {
    $pembayaranObj = new PembayaranTransfer('PAY-TEMP', $subtotal, 'Bank ' . $bankTransfer, '137-00-2819201-9');
}
$pesanan->setPembayaran($pembayaranObj);

// EKSEKUSI PEMBAYARAN FINAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bayar_sekarang') {
    if (!$event->cekKetersediaan($jumlahTiket)) {
        if ($event->getStatusEvent() === 'Tutup') {
            $pesanError = 'Penjualan tiket untuk kegiatan ini telah resmi ditutup oleh panitia. Transaksi pemesanan dibatalkan.';
        } elseif ($event->isPenuh()) {
            $pesanError = 'Kuota tiket untuk kegiatan ini telah terisi penuh. Transaksi pemesanan dibatalkan.';
        } else {
            $pesanError = 'Sisa kuota event tidak mencukupi untuk ' . $jumlahTiket . ' tiket yang dipesan. Silakan kurangi jumlah tiket.';
        }
    } else {
        // Konfirmasi pesanan & kurangi kuota
        $idPesananBaru = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
        $pesananFinal = new Pesanan($idPesananBaru, $currentUser->getIdUser(), $event->getIdEvent(), date('Y-m-d H:i:s'));
        
        $daftarKodeTiket = [];
        for ($i = 0; $i < $jumlahTiket; $i++) {
            $kategoriSingkat = ($jenisTiket === 'VIP') ? 'VIP' : 'REG';
            $kodeUnik = 'ETX-26-' . $kategoriSingkat . '-' . rand(100, 999);
            if ($jenisTiket === 'VIP') {
                $tFinal = new TiketVIP($kodeUnik, $event->getHargaDasar(), 35000, 'A-0' . ($i + 1), 'AKTIF', $event->getIdEvent(), $idPesananBaru);
            } else {
                $tFinal = new TiketReguler($kodeUnik, $event->getHargaDasar(), 'AKTIF', $event->getIdEvent(), $idPesananBaru);
            }
            $pesananFinal->tambahTiket($tFinal);
            $dataManager->saveTicket($tFinal);
            $daftarKodeTiket[] = $kodeUnik;
        }

        if ($promoObj && $promoObj->isValid()) {
            $pesananFinal->terapkanPromo($promoObj);
            $dataManager->savePromo($promoObj);
        }

        $idPay = 'PAY-' . strtoupper(bin2hex(random_bytes(4)));
        if ($metodeBayar === 'ewallet') {
            $payFinal = new PembayaranEWallet($idPay, $pesananFinal->getSubtotal(), $ewalletProvider, '0812-3456-7890');
        } else {
            $payFinal = new PembayaranTransfer($idPay, $pesananFinal->getSubtotal(), 'Bank ' . $bankTransfer, '137-00-2819201-9');
        }
        $pesananFinal->setPembayaran($payFinal);
        $pesananFinal->konfirmasi();

        // Update kuota event
        $event->kurangiKuota($jumlahTiket);
        $dataManager->saveEvent($event);

        // Simpan catatan order
        $dataManager->saveOrderRecord([
            'idPesanan' => $idPesananBaru,
            'idPeserta' => $currentUser->getIdUser(),
            'idEvent' => $event->getIdEvent(),
            'tanggalPesan' => date('Y-m-d H:i:s'),
            'statusPesanan' => 'KONFIRMASI',
            'kodePromo' => $promoObj ? $promoObj->getKode() : null,
            'metodeBayar' => $metodeBayar,
            'namaMetode' => $payFinal->getMetodeNama(),
            'biayaAdmin' => $pesananFinal->getBiayaAdmin(),
            'subtotal' => $pesananFinal->getSubtotal(),
            'potongan' => $pesananFinal->getPotonganPromo(),
            'totalBayar' => $pesananFinal->hitungTotal(),
            'statusBayar' => 'LUNAS',
            'tiket' => $daftarKodeTiket
        ]);

        // Redirect ke tiket digital pertama dengan pesan sukses
        $_SESSION['flash_success'] = 'Pembayaran berhasil. Tiket elektronik resmi telah diterbitkan.';
        header('Location: tiket.php?kode=' . urlencode($daftarKodeTiket[0]));
        exit;
    }
}

$pageTitle = 'Pemesanan Tiket — ' . $event->getNamaEvent();
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 2rem;">
  <h1 style="font-size: var(--text-28); margin-bottom: 0.5rem;">Pemesanan tiket</h1>
  <p style="color: var(--color-biru-abu); font-size: var(--text-14); margin: 0;">
    Acara: <strong><?= htmlspecialchars($event->getNamaEvent()) ?></strong>
  </p>
</div>

<!-- Stepper Tiga Langkah (Hanya ini yang memakai nomor 1, 2, 3) -->
<nav class="stepper-nav" aria-label="Langkah pemesanan tiket">
  <div class="stepper-step <?= $step === 1 ? 'active' : ($step > 1 ? 'completed' : '') ?>">
    <span class="stepper-num">1</span>
    <span>Pilih tiket</span>
  </div>
  <div class="stepper-step <?= $step === 2 ? 'active' : ($step > 2 ? 'completed' : '') ?>">
    <span class="stepper-num">2</span>
    <span>Promo</span>
  </div>
  <div class="stepper-step <?= $step === 3 ? 'active' : '' ?>">
    <span class="stepper-num">3</span>
    <span>Bayar</span>
  </div>
</nav>

<?php if ($pesanError): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($pesanError) ?></div>
<?php endif; ?>

<?php if ($pesanSuksesPromo): ?>
  <div class="alert alert-success"><?= htmlspecialchars($pesanSuksesPromo) ?></div>
<?php endif; ?>

<div class="checkout-grid">
  <!-- Kolom Langkah Interaktif -->
  <div>
    <form method="POST" action="checkout.php" id="checkout-form">
      <input type="hidden" name="event_id" value="<?= htmlspecialchars($event->getIdEvent()) ?>">

      <!-- ==================== LANGKAH 1: PILIH TIKET ==================== -->
      <?php if ($step === 1): ?>
        <div style="background-color: var(--color-putih); border: var(--border-tegas); padding: 1.5rem; border-radius: var(--radius-karcis);">
          <h2 style="font-size: var(--text-20); margin-bottom: 1.5rem;">Langkah 1: Konfirmasi jumlah dan kategori</h2>

          <div class="form-group">
            <label class="form-label">Kategori tiket</label>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
              <label style="border: 2px solid var(--color-tinta); padding: 1rem; border-radius: 2px; flex: 1; min-width: 200px; cursor: pointer; background-color: <?= $jenisTiket === 'Reguler' ? '#FFF' : '#F9FBFE' ?>;">
                <input type="radio" name="jenis_tiket" value="Reguler" class="ticket-option-input" data-price="<?= $event->getHargaDasar() ?>" <?= $jenisTiket === 'Reguler' ? 'checked' : '' ?>>
                <div style="font-weight: 700; margin-top: 0.25rem;">Tiket Reguler</div>
                <div style="font-family: var(--font-mono); font-size: var(--text-14); margin-top: 0.25rem;"><?= rupiah($event->getHargaDasar()) ?> / tiket</div>
              </label>

              <label style="border: 2px solid var(--color-tinta); padding: 1rem; border-radius: 2px; flex: 1; min-width: 200px; cursor: pointer; background-color: <?= $jenisTiket === 'VIP' ? '#FFF9EA' : '#F9FBFE' ?>; border-right: 8px solid var(--color-kuning);">
                <input type="radio" name="jenis_tiket" value="VIP" class="ticket-option-input" data-price="<?= $event->getHargaDasar() + 35000 ?>" <?= $jenisTiket === 'VIP' ? 'checked' : '' ?>>
                <div style="font-weight: 700; margin-top: 0.25rem;">Tiket VIP (Kursi Depan)</div>
                <div style="font-family: var(--font-mono); font-size: var(--text-14); margin-top: 0.25rem;"><?= rupiah($event->getHargaDasar() + 35000) ?> / tiket</div>
              </label>
            </div>
          </div>

          <div class="form-group" style="max-width: 240px;">
            <label class="form-label" for="ticket-qty">Jumlah tiket</label>
            <input type="number" id="ticket-qty" name="jumlah" min="1" max="5" value="<?= $jumlahTiket ?>" class="form-input" style="font-family: var(--font-mono); font-size: var(--text-16);">
            <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.25rem;">Maksimal 5 tiket per pesanan mahasiswa.</div>
          </div>

          <div style="margin-top: 2rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="index.php" class="btn btn-secondary">
              Kembali ke katalog
            </a>
            <?php if ($isEventTersedia): ?>
              <button type="submit" name="step" value="2" class="btn btn-primary" id="btn-next-step1">
                Lanjut ke promo
              </button>
            <?php else: ?>
              <button type="button" class="btn btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed; border-color: var(--color-merah-cap); color: var(--color-merah-cap);">
                <?= $event->getStatusEvent() === 'Tutup' ? 'Penjualan ditutup' : 'Tiket tidak tersedia' ?>
              </button>
            <?php endif; ?>
          </div>
        </div>

      <!-- ==================== LANGKAH 2: PROMO ==================== -->
      <?php elseif ($step === 2): ?>
        <div style="background-color: var(--color-putih); border: var(--border-tegas); padding: 1.5rem; border-radius: var(--radius-karcis);">
          <input type="hidden" name="jenis_tiket" value="<?= htmlspecialchars($jenisTiket) ?>">
          <input type="hidden" name="jumlah" value="<?= $jumlahTiket ?>">

          <h2 style="font-size: var(--text-20); margin-bottom: 1.5rem;">Langkah 2: Terapkan kode promo</h2>
          <p style="color: var(--color-biru-abu); font-size: var(--text-14); margin-bottom: 1.5rem;">
            Masukkan kode voucher diskon mahasiswa jika memilikinya, atau lewati langkah ini untuk lanjut ke pembayaran.
          </p>

          <div style="display: flex; gap: 0.75rem; align-items: flex-start; max-width: 480px; margin-bottom: 1.5rem;">
            <div style="flex: 1;">
              <input type="text" id="promo-code-input" name="kode_promo" placeholder="Contoh: MHSHEMAT" value="<?= htmlspecialchars($kodePromoInput) ?>" class="form-input" style="text-transform: uppercase; font-family: var(--font-mono); font-weight: 700;">
            </div>
            <button type="submit" name="step" value="2" class="btn btn-secondary" style="white-space: nowrap;">
              Terapkan promo
            </button>
          </div>

          <div style="background-color: #F8FAFD; border: 1px dashed var(--color-biru-abu); padding: 1rem; border-radius: 2px; margin-bottom: 2rem;">
            <div style="font-size: var(--text-12); font-weight: 700; color: var(--color-biru-abu); text-transform: uppercase; margin-bottom: 0.5rem;">Promo tersedia untuk dicoba:</div>
            <div style="font-family: var(--font-mono); font-size: var(--text-14); display: flex; flex-direction: column; gap: 0.25rem;">
              <div><strong>MHSHEMAT</strong> &mdash; Diskon 20% (maksimal potongan Rp 20.000)</div>
              <div><strong>SEMINARTEKNO</strong> &mdash; Diskon 15% (maksimal potongan Rp 15.000)</div>
            </div>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center;">
            <button type="submit" name="step" value="1" class="btn btn-secondary">
              Kembali ke pilih tiket
            </button>
            <?php if ($isEventTersedia): ?>
              <button type="submit" name="step" value="3" class="btn btn-primary">
                Lanjut ke pembayaran
              </button>
            <?php else: ?>
              <button type="button" class="btn btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed; border-color: var(--color-merah-cap); color: var(--color-merah-cap);">
                <?= $event->getStatusEvent() === 'Tutup' ? 'Penjualan ditutup' : 'Tiket tidak tersedia' ?>
              </button>
            <?php endif; ?>
          </div>
        </div>

      <!-- ==================== LANGKAH 3: BAYAR ==================== -->
      <?php elseif ($step === 3): ?>
        <div style="background-color: var(--color-putih); border: var(--border-tegas); padding: 1.5rem; border-radius: var(--radius-karcis);">
          <input type="hidden" name="jenis_tiket" value="<?= htmlspecialchars($jenisTiket) ?>">
          <input type="hidden" name="jumlah" value="<?= $jumlahTiket ?>">
          <input type="hidden" name="kode_promo" value="<?= htmlspecialchars($kodePromoInput) ?>">

          <h2 style="font-size: var(--text-20); margin-bottom: 1.5rem;">Langkah 3: Pilih metode bayar</h2>

          <div class="form-group">
            <label class="form-label">Metode pembayaran resmi</label>
            
            <!-- Transfer Bank -->
            <label style="display: block; border: 2px solid var(--color-tinta); padding: 1rem; border-radius: 2px; margin-bottom: 1rem; cursor: pointer;">
              <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <input type="radio" name="metode_pembayaran" value="transfer" <?= $metodeBayar === 'transfer' ? 'checked' : '' ?> style="accent-color: var(--color-tinta);">
                  <strong style="font-size: var(--text-16);">Transfer Bank Otomatis</strong>
                </div>
                <span style="font-family: var(--font-mono); font-size: var(--text-12); color: var(--color-biru-abu);">Biaya admin Rp 2.500</span>
              </div>
              <div style="margin-left: 1.75rem; font-size: var(--text-14); color: var(--color-biru-abu); margin-top: 0.5rem;">
                Tersedia Mandiri (137-00-2819201-9), BCA, BNI. Verifikasi otomatis dalam 1 menit.
              </div>
            </label>

            <!-- E-Wallet -->
            <label style="display: block; border: 2px solid var(--color-tinta); padding: 1rem; border-radius: 2px; cursor: pointer;">
              <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <input type="radio" name="metode_pembayaran" value="ewallet" <?= $metodeBayar === 'ewallet' ? 'checked' : '' ?> style="accent-color: var(--color-tinta);">
                  <strong style="font-size: var(--text-16);">Dompet Digital (E-Wallet)</strong>
                </div>
                <span style="font-family: var(--font-mono); font-size: var(--text-12); color: var(--color-biru-abu);">Biaya admin Rp 1.000</span>
              </div>
              <div style="margin-left: 1.75rem; font-size: var(--text-14); color: var(--color-biru-abu); margin-top: 0.5rem;">
                Mendukung GoPay, OVO, Dana, ShopeePay melalui QRIS atau notifikasi aplikasi.
              </div>
            </label>
          </div>

          <div style="background-color: #F8FAFD; border: 1px solid var(--color-garis-halus); padding: 1rem; border-radius: 2px; margin-top: 1.5rem; margin-bottom: 2rem;">
            <div style="font-size: var(--text-12); font-weight: 700; color: var(--color-biru-abu); text-transform: uppercase;">Data pemesan tiket:</div>
            <div style="font-size: var(--text-14); margin-top: 0.25rem;">
              <strong><?= htmlspecialchars($currentUser->getNama()) ?></strong>, NIM <?= htmlspecialchars($currentUser instanceof Peserta ? $currentUser->getNim() : '21120124130051') ?>, <?= htmlspecialchars($currentUser->getEmail()) ?>
            </div>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center;">
            <button type="submit" name="step" value="2" class="btn btn-secondary">
              Kembali ke promo
            </button>
            <?php if ($isEventTersedia): ?>
              <button type="submit" name="action" value="bayar_sekarang" class="btn btn-primary btn-lg">
                Bayar sekarang
              </button>
            <?php else: ?>
              <button type="button" class="btn btn-secondary btn-lg" disabled style="opacity: 0.5; cursor: not-allowed; border-color: var(--color-merah-cap); color: var(--color-merah-cap);">
                <?= $event->getStatusEvent() === 'Tutup' ? 'Penjualan ditutup' : 'Tiket tidak tersedia' ?>
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    </form>
  </div>

  <!-- Kolom Ringkasan Harga (Selalu Terlihat) -->
  <aside class="price-summary-card">
    <h2 style="font-size: var(--text-20); margin-bottom: 0.75rem; border-bottom: 2px solid var(--color-tinta); padding-bottom: 0.5rem;">
      Ringkasan pesanan
    </h2>

    <div style="margin-bottom: 1rem;">
      <div style="font-size: var(--text-14); font-weight: 700;"><?= htmlspecialchars($event->getNamaEvent()) ?></div>
      <div style="font-size: var(--text-12); color: var(--color-biru-abu); margin-top: 0.25rem;">
        <?= $jumlahTiket ?> x Tiket <?= htmlspecialchars($jenisTiket) ?>
      </div>
    </div>

    <!-- Data kalkulasi untuk sinkronisasi client-side JavaScript -->
    <div id="active-discount-val" data-rate="<?= $promoObj ? $promoObj->getPersenDiskon() : 0 ?>" data-max="<?= $promoObj ? $promoObj->getMaksPotongan() : 0 ?>" style="display: none;"></div>

    <table class="summary-table">
      <tbody>
        <tr>
          <td>Subtotal (<?= $jumlahTiket ?> tiket)</td>
          <td class="text-right" id="display-subtotal" data-subtotal="<?= $pesanan->getSubtotal() ?>"><?= rupiah($pesanan->getSubtotal()) ?></td>
        </tr>
        <tr>
          <td>
            Potongan promo
            <?php if ($promoObj && $promoObj->isValid()): ?>
              <br><small style="color: var(--color-hijau-cap); font-family: var(--font-mono);">(<?= htmlspecialchars($promoObj->getKode()) ?>)</small>
            <?php endif; ?>
          </td>
          <td class="text-right" id="display-promo" data-discount="<?= $pesanan->getPotonganPromo() ?>" style="color: var(--color-hijau-cap);">
            <?= $pesanan->getPotonganPromo() > 0 ? '- ' . rupiah($pesanan->getPotonganPromo()) : 'Rp 0' ?>
          </td>
        </tr>
        <tr>
          <td>Biaya admin sistem</td>
          <td class="text-right" id="display-admin"><?= rupiah($pesanan->getBiayaAdmin()) ?></td>
        </tr>
        <tr class="total-row">
          <td>Total bayar</td>
          <td class="text-right" id="display-total"><?= rupiah($pesanan->hitungTotal()) ?></td>
        </tr>
      </tbody>
    </table>

    <div style="font-size: var(--text-12); color: var(--color-biru-abu); line-height: 1.4;">
      Tiket digital resmi dengan kode batang unik langsung diterbitkan segera setelah pembayaran diselesaikan.
    </div>
  </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
