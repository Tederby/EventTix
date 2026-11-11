package eventtix;

import java.text.SimpleDateFormat;
import java.util.Date;

/**
 * Kelas KodePromo merepresentasikan kode promosi dan diskon untuk pemesanan tiket pada sistem EventTix.
 *
 * Menerapkan prinsip Enkapsulasi:
 * Seluruh atribut berstatus private dan diakses melalui getter/setter serta metode bisnis
 * yang memvalidasi masa berlaku dan sisa kuota promo.
 *
 * @author Kelompok 6 - Modul 3 (pesanan-promo)
 */
public class KodePromo {

    // Atribut privat (Encapsulation)
    private String kode;
    private double persenDiskon;   // Contoh: 0.20 untuk diskon 20%
    private double maksPotongan;    // Batas maksimum nominal potongan (Rp)
    private Date masaBerlaku;       // Tanggal dan waktu kedaluwarsa promo
    private int kuotaPemakaian;     // Batas jumlah penggunaan kode promo

    /**
     * Konstruktor lengkap untuk inisialisasi entitas KodePromo.
     *
     * @param kode           String identitas kode promo (contoh: "DISKONAI20")
     * @param persenDiskon   Persentase diskon dalam desimal (0.01 s.d. 1.0)
     * @param maksPotongan   Maksimum nominal potongan rupiah (>= 0)
     * @param masaBerlaku    Batas akhir validitas promo
     * @param kuotaPemakaian Jumlah kuota pemakaian promo (>= 0)
     */
    public KodePromo(String kode, double persenDiskon, double maksPotongan, Date masaBerlaku, int kuotaPemakaian) {
        if (kode == null || kode.trim().isEmpty()) {
            throw new IllegalArgumentException("Kode promo tidak boleh kosong.");
        }
        if (persenDiskon <= 0.0 || persenDiskon > 1.0) {
            throw new IllegalArgumentException("Persentase diskon harus di antara 0.01 (1%) hingga 1.0 (100%).");
        }
        if (maksPotongan < 0.0) {
            throw new IllegalArgumentException("Maksimal potongan harga tidak boleh bernilai negatif.");
        }
        if (kuotaPemakaian < 0) {
            throw new IllegalArgumentException("Kuota pemakaian tidak boleh bernilai negatif.");
        }

        this.kode = kode.trim().toUpperCase();
        this.persenDiskon = persenDiskon;
        this.maksPotongan = maksPotongan;
        this.masaBerlaku = masaBerlaku;
        this.kuotaPemakaian = kuotaPemakaian;
    }

    /**
     * Memeriksa apakah kode promo masih memenuhi syarat untuk digunakan.
     * Syarat valid: kuota pemakaian > 0 dan masa berlaku belum terlewati.
     *
     * @return true jika promo masih aktif dan kuota tersedia, false jika tidak
     */
    public boolean isValid() {
        boolean belumKedaluwarsa = (masaBerlaku == null) || (new Date().before(masaBerlaku));
        return (kuotaPemakaian > 0) && belumKedaluwarsa;
    }

    /**
     * Menghitung nilai potongan harga yang didapat dari subtotal pesanan.
     * Nilai potongan dibatasi tidak melebihi maksPotongan.
     *
     * @param subtotal Total harga tiket sebelum dikenakan promo
     * @return Nominal potongan harga (Rp)
     */
    public double hitungPotongan(double subtotal) {
        if (!isValid() || subtotal <= 0.0) {
            return 0.0;
        }
        double nominalPotongan = subtotal * persenDiskon;
        return Math.min(nominalPotongan, maksPotongan);
    }

    /**
     * Mengurangi sisa kuota pemakaian kode promo sebanyak 1 jika promo berhasil digunakan.
     */
    public void kurangiKuota() {
        if (kuotaPemakaian > 0) {
            kuotaPemakaian--;
        }
    }

    // ---- Getter & Setter ----

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

    public void setMasaBerlaku(Date masaBerlaku) {
        this.masaBerlaku = masaBerlaku;
    }

    public int getKuotaPemakaian() {
        return kuotaPemakaian;
    }

    public void setKuotaPemakaian(int kuotaPemakaian) {
        if (kuotaPemakaian < 0) {
            throw new IllegalArgumentException("Kuota pemakaian tidak boleh negatif.");
        }
        this.kuotaPemakaian = kuotaPemakaian;
    }

    @Override
    public String toString() {
        SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm");
        String tglExp = (masaBerlaku != null) ? sdf.format(masaBerlaku) : "Tanpa Batas";
        return String.format("Promo [%s] Diskon: %d%% (Maks: Rp %,.0f) | Exp: %s | Sisa Kuota: %d",
                kode, (int) Math.round(persenDiskon * 100), maksPotongan, tglExp, kuotaPemakaian);
    }
}

