package eventtix;

import java.util.List;

/**
 * Kelas abstrak Tiket (superclass TiketReguler & TiketVIP).
 * Dipakai oleh modul lain: Event (menyediakan), Pesanan (berisi), CheckIn (divalidasi lewat).
 */
public abstract class Tiket {

    // Konstanta status supaya modul lain tidak salah ketik string
    public static final String STATUS_TERSEDIA = "TERSEDIA";
    public static final String STATUS_TERPAKAI = "TERPAKAI";

    private String kodeTiket;
    private double hargaDasar;
    private String statusTiket;

    public Tiket(String kodeTiket, double hargaDasar) {
        this.kodeTiket = kodeTiket;
        this.hargaDasar = hargaDasar;
        this.statusTiket = STATUS_TERSEDIA;
    }

    // ===== Method abstrak (polimorfisme) =====
    public abstract double hitungHarga();

    public abstract List<String> getFasilitas();

    // ===== Method konkret =====
    public String getKodeTiket() {
        return kodeTiket;
    }

    public String getStatusTiket() {
        return statusTiket;
    }

    public double getHargaDasar() {
        return hargaDasar;
    }

    public void tandaiTerpakai() {
        this.statusTiket = STATUS_TERPAKAI;
    }
}