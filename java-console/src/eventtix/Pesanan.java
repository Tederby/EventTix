package eventtix;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

/**
 * Kelas Pesanan merepresentasikan transaksi pemesanan tiket oleh peserta untuk suatu event.
 *
 * Menerapkan prinsip OOP:
 * - Encapsulation: Mengelola atribut transaksi secara privat dan terjaga konsistensinya.
 * - Composition & Association:
 *     Pesanan *-- 1..* Tiket       (Komposisi: pesanan memiliki satu atau lebih tiket)
 *     Pesanan *-- 1 Pembayaran     (Komposisi: pesanan dibayar melalui 1 objek pembayaran)
 *     Pesanan --> 0..1 KodePromo   (Asosiasi: pesanan dapat menggunakan 1 kode promo opsional)
 *
 * @author Kelompok 6 - Modul 3 (pesanan-promo)
 */
public class Pesanan {

    // Konstanta Status Pesanan
    public static final String STATUS_PENDING = "Pending";
    public static final String STATUS_LUNAS = "Lunas";
    public static final String STATUS_DIBATALKAN = "Dibatalkan";

    // Atribut privat (Encapsulation)
    private String idPesanan;
    private Date tanggalPesan;
    private String statusPesanan; // "Pending", "Lunas", "Dibatalkan"

    // Relasi Objek
    private Peserta peserta;
    private Event event;
    private List<Tiket> daftarTiket;
    private KodePromo promo;
    private Pembayaran pembayaran;

    /**
     * Konstruktor lengkap entitas Pesanan.
     *
     * @param idPesanan     ID unik transaksi pesanan (contoh: "ORD-1715000000")
     * @param tanggalPesan  Waktu transaksi dibuat
     * @param statusPesanan Status awal pesanan (default: "Pending")
     * @param peserta       Objek Peserta yang melakukan pemesanan
     * @param event         Objek Event yang dipesan
     */
    public Pesanan(String idPesanan, Date tanggalPesan, String statusPesanan, Peserta peserta, Event event) {
        if (idPesanan == null || idPesanan.trim().isEmpty()) {
            throw new IllegalArgumentException("ID pesanan tidak boleh kosong.");
        }
        this.idPesanan = idPesanan.trim();
        this.tanggalPesan = (tanggalPesan != null) ? tanggalPesan : new Date();
        this.statusPesanan = (statusPesanan != null && !statusPesanan.trim().isEmpty()) ? statusPesanan : STATUS_PENDING;
        this.peserta = peserta;
        this.event = event;
        this.daftarTiket = new ArrayList<>();
        this.promo = null;
        this.pembayaran = null;
    }

    /**
     * Konstruktor overloaded tanpa relasi eksplisit peserta dan event (sesuai spesifikasi UML).
     *
     * @param idPesanan     ID unik pesanan
     * @param tanggalPesan  Waktu pemesanan
     * @param statusPesanan Status pesanan
     */
    public Pesanan(String idPesanan, Date tanggalPesan, String statusPesanan) {
        this(idPesanan, tanggalPesan, statusPesanan, null, null);
    }

    /**
     * Konstruktor praktis dengan status default "Pending".
     *
     * @param idPesanan    ID unik pesanan
     * @param tanggalPesan Waktu pemesanan
     */
    public Pesanan(String idPesanan, Date tanggalPesan) {
        this(idPesanan, tanggalPesan, STATUS_PENDING, null, null);
    }

    /**
     * Menambahkan tiket ke dalam inventaris pesanan.
     *
     * @param tiket Objek Tiket yang dibeli (TiketReguler atau TiketVIP)
     */
    public void tambahTiket(Tiket tiket) {
        if (tiket != null) {
            this.daftarTiket.add(tiket);
        }
    }

    /**
     * Menerapkan kode promo pada pesanan jika promo valid dan kuota mencukupi.
     * Mengurangi kuota promo jika berhasil diterapkan.
     *
     * @param promo Objek KodePromo yang ingin digunakan
     * @return true jika berhasil diterapkan, false jika promo tidak valid atau pesanan sudah lunas
     */
    public boolean terapkanPromo(KodePromo promo) {
        if (STATUS_LUNAS.equalsIgnoreCase(this.statusPesanan)) {
            System.out.println("[PROMO] Pesanan sudah berstatus LUNAS, tidak dapat mengubah promo.");
            return false;
        }

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

    /**
     * Menghitung subtotal akumulasi harga seluruh tiket sebelum potongan diskon.
     *
     * @return Subtotal harga tiket
     */
    public double hitungSubtotal() {
        double subtotal = 0.0;
        for (Tiket t : daftarTiket) {
            if (t != null) {
                subtotal += t.hitungHarga();
            }
        }
        return subtotal;
    }

    /**
     * Menghitung grand total pembayaran pesanan setelah dikurangi potongan kode promo.
     * Nilai tidak pernah negatif.
     *
     * @return Total biaya pesanan setelah diskon
     */
    public double hitungTotal() {
        double subtotal = hitungSubtotal();
        double potongan = (promo != null) ? promo.hitungPotongan(subtotal) : 0.0;
        double total = subtotal - potongan;
        return Math.max(0.0, total);
    }

    /**
     * Mengonfirmasi pesanan saat transaksi pembayaran telah berhasil diselesaikan.
     * Mengubah status pesanan menjadi "Lunas" dan status seluruh tiket menjadi "Terjual".
     */
    public void konfirmasi() {
        this.statusPesanan = STATUS_LUNAS;
        for (Tiket t : daftarTiket) {
            if (t != null) {
                t.setStatusTiket("Terjual");
            }
        }
        System.out.println("[PESANAN] Pesanan " + idPesanan + " telah dikonfirmasi dan berstatus LUNAS.");
    }

    /**
     * Membatalkan pesanan.
     * Mengubah status pesanan menjadi "Dibatalkan" dan mengembalikan status tiket menjadi "Tersedia".
     */
    public void batalkan() {
        this.statusPesanan = STATUS_DIBATALKAN;
        for (Tiket t : daftarTiket) {
            if (t != null) {
                t.setStatusTiket("Tersedia");
            }
        }
        System.out.println("[PESANAN] Pesanan " + idPesanan + " telah dibatalkan.");
    }

    // ---- Getter & Setter ----

    public String getIdPesanan() {
        return idPesanan;
    }

    public Date getTanggalPesan() {
        return tanggalPesan;
    }

    public void setTanggalPesan(Date tanggalPesan) {
        this.tanggalPesan = tanggalPesan;
    }

    public String getStatusPesanan() {
        return statusPesanan;
    }

    public void setStatusPesanan(String statusPesanan) {
        this.statusPesanan = statusPesanan;
    }

    public Peserta getPeserta() {
        return peserta;
    }

    public void setPeserta(Peserta peserta) {
        this.peserta = peserta;
    }

    public Event getEvent() {
        return event;
    }

    public void setEvent(Event event) {
        this.event = event;
    }

    public List<Tiket> getDaftarTiket() {
        return daftarTiket;
    }

    public void setDaftarTiket(List<Tiket> daftarTiket) {
        this.daftarTiket = daftarTiket;
    }

    public KodePromo getPromo() {
        return promo;
    }

    public void setPromo(KodePromo promo) {
        this.promo = promo;
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
        String tgl = (tanggalPesan != null) ? sdf.format(tanggalPesan) : "-";
        String namaEvt = (event != null) ? event.getNamaEvent() : "-";
        int jmlTiket = (daftarTiket != null) ? daftarTiket.size() : 0;
        return String.format("Pesanan #%s | Tanggal: %s | Event: %s | Tiket: %d pcs | Total: Rp %,.0f | Status: %s",
                idPesanan, tgl, namaEvt, jmlTiket, hitungTotal(), statusPesanan);
    }
}

