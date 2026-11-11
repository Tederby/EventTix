package eventtix;

import java.util.List;

/**
 * Kelas abstrak Tiket (Superclass untuk TiketReguler dan TiketVIP).
 * Merepresentasikan entitas tiket umum dalam sistem EventTix.
 *
 * Menerapkan prinsip OOP:
 * - Abstraction: Menyediakan kerangka umum tiket dan metode abstrak hitungHarga() & getFasilitas().
 * - Encapsulation: Seluruh atribut bersifat privat dan diakses melalui getter/setter/metode status.
 *
 * @author Kelompok 6 - Modul 2 (tiket-checkin)
 */
public abstract class Tiket {

    // Konstanta status tiket standar sistem
    public static final String STATUS_TERSEDIA = "Tersedia";
    public static final String STATUS_TERJUAL = "Terjual";
    public static final String STATUS_TERPAKAI = "Terpakai";

    private String kodeTiket;
    private double hargaDasar;
    private String statusTiket;

    /**
     * Konstruktor default dengan status awal "Tersedia".
     *
     * @param kodeTiket  Kode unik tiket (contoh: "TIX-REG-001")
     * @param hargaDasar Nominal harga dasar tiket
     */
    public Tiket(String kodeTiket, double hargaDasar) {
        this(kodeTiket, hargaDasar, STATUS_TERSEDIA);
    }

    /**
     * Konstruktor lengkap tiket dengan status kustom.
     *
     * @param kodeTiket   Kode unik tiket
     * @param hargaDasar  Nominal harga dasar tiket
     * @param statusTiket Status awal tiket ("Tersedia", "Terjual", "Terpakai")
     */
    public Tiket(String kodeTiket, double hargaDasar, String statusTiket) {
        if (kodeTiket == null || kodeTiket.trim().isEmpty()) {
            throw new IllegalArgumentException("Kode tiket tidak boleh kosong.");
        }
        if (hargaDasar < 0.0) {
            throw new IllegalArgumentException("Harga dasar tiket tidak boleh negatif.");
        }
        this.kodeTiket = kodeTiket.trim();
        this.hargaDasar = hargaDasar;
        this.statusTiket = (statusTiket != null && !statusTiket.trim().isEmpty()) ? statusTiket : STATUS_TERSEDIA;
    }

    // ===== Metode Abstrak (Polimorfisme) =====

    /**
     * Menghitung total harga tiket berdasarkan jenis dan biaya tambahannya.
     *
     * @return Nilai nominal harga tiket
     */
    public abstract double hitungHarga();

    /**
     * Mengembalikan daftar fasilitas dan keuntungan yang didapatkan pemilik tiket.
     *
     * @return List deskripsi fasilitas tiket
     */
    public abstract List<String> getFasilitas();

    // ===== Metode Konkret & Encapsulation =====

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

    /**
     * Menandai tiket telah dipakai saat proses check-in di lokasi acara.
     */
    public void tandaiTerpakai() {
        this.statusTiket = STATUS_TERPAKAI;
    }

    @Override
    public String toString() {
        return String.format("[%s] Rp %,.0f (%s)", getKodeTiket(), hitungHarga(), statusTiket);
    }
}
