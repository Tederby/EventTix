package eventtix;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Date;
import java.util.List;

/**
 * File Pendukung: Stubs.java
 *
 * Berisi kelas-kelas stub/mock minimal untuk modul eksternal yang dikerjakan oleh
 * anggota tim lain di branch terpisah (branch tiket-checkin, pesanan-promo, pembayaran).
 *
 * Dengan adanya stub ini:
 * 1. Seluruh kode Modul 2 (user-event) dapat langsung di-compile (javac) dan dijalankan (java)
 *    secara mandiri tanpa error saat pengujian.
 * 2. Mengikuti arsitektur kontrak UML Class Diagram 13 Class EventTix.
 *
 * Catatan Integrasi:
 * Ketika semua branch di-merge ke branch utama ('main'), file Stubs.java ini dapat digantikan
 * secara langsung oleh file implementasi asli tanpa mengubah method signature pada modul pengguna & event.
 */

// ============================================================================
// STUB MODUL 3: TIKET & CHECK-IN (branch: tiket-checkin)
// ============================================================================

/**
 * Kelas Abstrak Tiket merepresentasikan entitas tiket umum.
 */
abstract class Tiket {
    private String kodeTiket;
    private double hargaDasar;
    private String statusTiket; // "Tersedia", "Terjual", "Terpakai"

    public Tiket(String kodeTiket, double hargaDasar) {
        this.kodeTiket = kodeTiket;
        this.hargaDasar = hargaDasar;
        this.statusTiket = "Tersedia";
    }

    public Tiket(String kodeTiket, double hargaDasar, String statusTiket) {
        this.kodeTiket = kodeTiket;
        this.hargaDasar = hargaDasar;
        this.statusTiket = statusTiket;
    }

    public abstract double hitungHarga();
    public abstract List<String> getFasilitas();

    public String getKodeTiket() {
        return kodeTiket;
    }

    public double getHargaDasar() {
        return hargaDasar;
    }

    public String getStatusTiket() {
        return statusTiket;
    }

    public void setStatusTiket(String statusTiket) {
        this.statusTiket = statusTiket;
    }

    public void tandaiTerpakai() {
        this.statusTiket = "Terpakai";
    }

    @Override
    public String toString() {
        return "[" + getKodeTiket() + "] Rp " + String.format("%,.0f", hitungHarga()) + " (" + statusTiket + ")";
    }
}

/**
 * Kelas TiketReguler mewarisi Tiket.
 */
class TiketReguler extends Tiket {

    public TiketReguler(String kodeTiket, double hargaDasar) {
        super(kodeTiket, hargaDasar);
    }

    @Override
    public double hitungHarga() {
        return getHargaDasar();
    }

    @Override
    public List<String> getFasilitas() {
        return Arrays.asList("Akses Masuk Standar", "Sertifikat Digital (E-Certificate)");
    }
}

/**
 * Kelas TiketVIP mewarisi Tiket dengan fasilitas premium dan nomor kursi.
 */
class TiketVIP extends Tiket {
    private double biayaLayananVIP;
    private String nomorKursi;

    public TiketVIP(String kodeTiket, double hargaDasar, double biayaLayananVIP, String nomorKursi) {
        super(kodeTiket, hargaDasar);
        this.biayaLayananVIP = biayaLayananVIP;
        this.nomorKursi = nomorKursi;
    }

    @Override
    public double hitungHarga() {
        return getHargaDasar() + biayaLayananVIP;
    }

    @Override
    public List<String> getFasilitas() {
        return Arrays.asList(
            "Akses Masuk Jalur VIP Fast-Track",
            "Nomor Kursi Terusan Depan: " + nomorKursi,
            "Snack Box & Welcome Drink Exclusive",
            "Sertifikat Cetak Eksklusif & E-Certificate",
            "Goodie Bag & Merchandise Event"
        );
    }

    public double getBiayaLayananVIP() {
        return biayaLayananVIP;
    }

    public String getNomorKursi() {
        return nomorKursi;
    }
}

/**
 * Kelas CheckIn mencatat proses validasi tiket peserta di pintu masuk acara.
 */
class CheckIn {
    private String idCheckIn;
    private Date waktuCheckIn;
    private String statusValidasi; // "Berhasil", "Gagal"
    private Tiket tiket;

    public CheckIn(String idCheckIn, Date waktuCheckIn, Tiket tiket) {
        this.idCheckIn = idCheckIn;
        this.waktuCheckIn = waktuCheckIn;
        this.tiket = tiket;
        this.statusValidasi = "Belum Divalidasi";
    }

    /**
     * Memvalidasi apakah tiket berstatus "Terjual" (belum terpakai).
     * Jika valid, status tiket diubah menjadi "Terpakai".
     */
    public boolean validasiTiket() {
        if (tiket == null) {
            this.statusValidasi = "Gagal (Tiket tidak ditemukan)";
            return false;
        }

        if ("Terpakai".equalsIgnoreCase(tiket.getStatusTiket())) {
            this.statusValidasi = "Gagal (Tiket sudah pernah digunakan sebelumnya)";
            return false;
        }

        if ("Terjual".equalsIgnoreCase(tiket.getStatusTiket())) {
            tiket.tandaiTerpakai();
            this.statusValidasi = "Berhasil";
            return true;
        }

        this.statusValidasi = "Gagal (Status tiket '" + tiket.getStatusTiket() + "' tidak memenuhi syarat check-in)";
        return false;
    }

    public void catat() {
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        String formattedDate = waktuCheckIn != null ? sdf.format(waktuCheckIn) : "-";
        System.out.println("[CHECK-IN LOG] ID: " + idCheckIn + " | Waktu: " + formattedDate +
                " | Tiket: " + (tiket != null ? tiket.getKodeTiket() : "null") +
                " | Hasil: " + statusValidasi);
    }

    public String getIdCheckIn() {
        return idCheckIn;
    }

    public Date getWaktuCheckIn() {
        return waktuCheckIn;
    }

    public String getStatusValidasi() {
        return statusValidasi;
    }

    public Tiket getTiket() {
        return tiket;
    }
}

// ============================================================================
// STUB MODUL 4: PESANAN & PROMO (branch: pesanan-promo)
// ============================================================================

/**
 * Kelas KodePromo menyediakan diskon potongan harga untuk pesanan.
 */
class KodePromo {
    private String kode;
    private double persenDiskon; // contoh: 0.20 untuk 20%
    private double maksPotongan;  // contoh: 25000
    private Date masaBerlaku;
    private int kuotaPemakaian;

    public KodePromo(String kode, double persenDiskon, double maksPotongan, Date masaBerlaku, int kuotaPemakaian) {
        this.kode = kode;
        this.persenDiskon = persenDiskon;
        this.maksPotongan = maksPotongan;
        this.masaBerlaku = masaBerlaku;
        this.kuotaPemakaian = kuotaPemakaian;
    }

    public boolean isValid() {
        boolean belumKedaluwarsa = (masaBerlaku == null) || (new Date().before(masaBerlaku));
        return (kuotaPemakaian > 0) && belumKedaluwarsa;
    }

    public double hitungPotongan(double subtotal) {
        if (!isValid()) {
            return 0.0;
        }
        double nominalPotongan = subtotal * persenDiskon;
        return Math.min(nominalPotongan, maksPotongan);
    }

    public void kurangiKuota() {
        if (kuotaPemakaian > 0) {
            kuotaPemakaian--;
        }
    }

    public String getKode() {
        return kode;
    }

    public double getPersenDiskon() {
        return persenDiskon;
    }

    public double getMaksPotongan() {
        return maksPotongan;
    }

    public Date getMasaBerlaku() {
        return masaBerlaku;
    }

    public int getKuotaPemakaian() {
        return kuotaPemakaian;
    }

    @Override
    public String toString() {
        return "Promo [" + kode + "] Diskon: " + (int)(persenDiskon * 100) +
               "% (Maks: Rp " + String.format("%,.0f", maksPotongan) + ") | Sisa Kuota: " + kuotaPemakaian;
    }
}

/**
 * Kelas Pesanan merepresentasikan transaksi pemesanan tiket oleh peserta.
 */
class Pesanan {
    private String idPesanan;
    private Date tanggalPesan;
    private String statusPesanan; // "Pending", "Lunas", "Dibatalkan"
    private Peserta peserta;
    private Event event;
    private List<Tiket> daftarTiket;
    private KodePromo promo;
    private Pembayaran pembayaran;

    public Pesanan(String idPesanan, Date tanggalPesan, String statusPesanan, Peserta peserta, Event event) {
        this.idPesanan = idPesanan;
        this.tanggalPesan = tanggalPesan;
        this.statusPesanan = statusPesanan;
        this.peserta = peserta;
        this.event = event;
        this.daftarTiket = new ArrayList<>();
        this.promo = null;
        this.pembayaran = null;
    }

    public void tambahTiket(Tiket tiket) {
        if (tiket != null) {
            this.daftarTiket.add(tiket);
        }
    }

    public boolean terapkanPromo(KodePromo promo) {
        if (promo != null && promo.isValid()) {
            this.promo = promo;
            promo.kurangiKuota();
            System.out.println("[PROMO] Kode '" + promo.getKode() + "' berhasil diterapkan pada pesanan " + idPesanan);
            return true;
        } else {
            System.out.println("[PROMO] Kode promo tidak valid atau kuota telah habis.");
            return false;
        }
    }

    public double hitungSubtotal() {
        double subtotal = 0.0;
        for (Tiket t : daftarTiket) {
            subtotal += t.hitungHarga();
        }
        return subtotal;
    }

    public double hitungTotal() {
        double subtotal = hitungSubtotal();
        double potongan = (promo != null) ? promo.hitungPotongan(subtotal) : 0.0;
        double total = subtotal - potongan;
        return Math.max(0.0, total);
    }

    public void konfirmasi() {
        this.statusPesanan = "Lunas";
        for (Tiket t : daftarTiket) {
            t.setStatusTiket("Terjual");
        }
        System.out.println("[PESANAN] Pesanan " + idPesanan + " telah dikonfirmasi dan berstatus LUNAS.");
    }

    public void batalkan() {
        this.statusPesanan = "Dibatalkan";
        for (Tiket t : daftarTiket) {
            t.setStatusTiket("Tersedia");
        }
        System.out.println("[PESANAN] Pesanan " + idPesanan + " telah dibatalkan.");
    }

    public String getIdPesanan() {
        return idPesanan;
    }

    public Date getTanggalPesan() {
        return tanggalPesan;
    }

    public String getStatusPesanan() {
        return statusPesanan;
    }

    public Peserta getPeserta() {
        return peserta;
    }

    public Event getEvent() {
        return event;
    }

    public List<Tiket> getDaftarTiket() {
        return daftarTiket;
    }

    public KodePromo getPromo() {
        return promo;
    }

    public Pembayaran getPembayaran() {
        return pembayaran;
    }

    public void setPembayaran(Pembayaran pembayaran) {
        this.pembayaran = pembayaran;
    }

    @Override
    public String toString() {
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm");
        return "Pesanan #" + idPesanan + " | Tanggal: " + (tanggalPesan != null ? sdf.format(tanggalPesan) : "-") +
               " | Event: " + (event != null ? event.getNamaEvent() : "-") +
               " | Tiket: " + daftarTiket.size() + " pcs" +
               " | Total: Rp " + String.format("%,.0f", hitungTotal()) +
               " | Status: " + statusPesanan;
    }
}
