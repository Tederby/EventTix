package eventtix;

import java.util.Date;

/**
 * Class abstrak Pembayaran (kerangka umum semua metode pembayaran).
 * Relasi: Pesanan *-- 1 Pembayaran (komposisi, "dibayar dengan" 1:1).
 *
 * Nilai statusBayar yang dipakai:
 *   MENUNGGU, BUKTI_TERVERIFIKASI (khusus transfer), BERHASIL, GAGAL
 */
public abstract class Pembayaran {

    public static final String STATUS_MENUNGGU = "MENUNGGU";
    public static final String STATUS_BUKTI_TERVERIFIKASI = "BUKTI_TERVERIFIKASI";
    public static final String STATUS_BERHASIL = "BERHASIL";
    public static final String STATUS_GAGAL = "GAGAL";

    // Atribut (semua private -> encapsulation)
    private String idPembayaran;
    private double jumlah;       // total pesanan (setelah promo), belum termasuk biaya admin
    private Date waktuBayar;     // null sampai pembayaran berhasil
    private String statusBayar;

    public Pembayaran(String idPembayaran, double jumlah) {
        if (idPembayaran == null || idPembayaran.trim().isEmpty()) {
            throw new IllegalArgumentException("ID pembayaran tidak boleh kosong.");
        }
        if (jumlah <= 0) {
            throw new IllegalArgumentException("Jumlah pembayaran harus lebih dari 0.");
        }
        this.idPembayaran = idPembayaran;
        this.jumlah = jumlah;
        this.waktuBayar = null;
        this.statusBayar = STATUS_MENUNGGU;
    }

    // ---- Method abstrak (polymorphism: isi berbeda tiap metode bayar) ----

    /** Biaya admin sesuai metode pembayaran. */
    public abstract double hitungBiayaAdmin();

    /** Memproses pembayaran. Return true jika berhasil. */
    public abstract boolean prosesPembayaran();

    // ---- Method konkret ----

    /** Total yang harus dibayar = jumlah + biaya admin (memanggil method abstrak). */
    public double getTotalBayar() {
        return jumlah + hitungBiayaAdmin();
    }

    public String getStatusBayar() {
        return statusBayar;
    }

    // ---- Getter tambahan ----

    public String getIdPembayaran() {
        return idPembayaran;
    }

    public double getJumlah() {
        return jumlah;
    }

    public Date getWaktuBayar() {
        return waktuBayar;
    }

    // ---- Setter protected: hanya boleh diubah oleh class turunan ----

    protected void setStatusBayar(String statusBayar) {
        this.statusBayar = statusBayar;
    }

    protected void setWaktuBayar(Date waktuBayar) {
        this.waktuBayar = waktuBayar;
    }

    /** Dipakai subclass setelah pembayaran sukses: status BERHASIL + catat waktu. */
    protected void tandaiBerhasil() {
        this.statusBayar = STATUS_BERHASIL;
        this.waktuBayar = new Date();
    }

    @Override
    public String toString() {
        return String.format("%s [%s] jumlah=Rp%,.0f admin=Rp%,.0f total=Rp%,.0f status=%s",
                getClass().getSimpleName(), idPembayaran, jumlah,
                hitungBiayaAdmin(), getTotalBayar(), statusBayar);
    }
}
