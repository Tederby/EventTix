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
