package eventtix;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

/**
 * Kelas Event merepresentasikan kegiatan seminar, workshop, atau acara kampus yang dikelola dalam sistem EventTix.
 * Menerapkan prinsip Enkapsulasi secara ketat: seluruh atribut bersifat privat dan state internal
 * dikendalikan melalui metode yang memvalidasi integritas data (misalnya kuota dan status penjualan).
 *
 * @author Kelompok 6 - Modul 2 (user-event)
 */
public class Event {
    // Atribut privat (Encapsulation)
    private String idEvent;
    private String namaEvent;
    private String deskripsi;
    private Date tanggal;
    private String lokasi;
    private int kuota;
    private int tiketTerjual;
    private String statusEvent; // "Buka" atau "Tutup"
    private double hargaDasar;
    private List<Tiket> daftarTiket;

    /**
     * Konstruktor utama untuk inisialisasi Event baru dengan data master harga.
     *
     * @param idEvent    Kode unik identitas event (contoh: "EVT-2026-001")
     * @param namaEvent  Nama atau judul acara
     * @param deskripsi  Ringkasan informasi acara
     * @param tanggal    Waktu pelaksanaan event
     * @param lokasi     Tempat diselenggarakannya event
     * @param kuota      Batas maksimal kapasitas tiket peserta
     * @param hargaDasar Nominal harga dasar katalog untuk tiket event
     */
    public Event(String idEvent, String namaEvent, String deskripsi, Date tanggal, String lokasi, int kuota, double hargaDasar) {
        if (hargaDasar < 0.0) {
            throw new IllegalArgumentException("Harga dasar event tidak boleh negatif.");
        }
        this.idEvent = idEvent;
        this.namaEvent = namaEvent;
        this.deskripsi = deskripsi;
        this.tanggal = tanggal;
        this.lokasi = lokasi;
        this.kuota = kuota;
        this.tiketTerjual = 0;
        this.statusEvent = "Buka";
        this.hargaDasar = hargaDasar;
        this.daftarTiket = new ArrayList<>();
    }

    /**
     * Konstruktor overloaded untuk kompatibilitas ke belakang (default hargaDasar = 0.0).
     *
     * @param idEvent   Kode unik identitas event
     * @param namaEvent Nama acara
     * @param deskripsi Ringkasan informasi acara
     * @param tanggal   Waktu pelaksanaan event
     * @param lokasi    Tempat acara
     * @param kuota     Batas maksimal kapasitas tiket
     */
    public Event(String idEvent, String namaEvent, String deskripsi, Date tanggal, String lokasi, int kuota) {
        this(idEvent, namaEvent, deskripsi, tanggal, lokasi, kuota, 0.0);
    }

    /**
     * Memeriksa apakah event masih membuka penjualan dan sisa kuota mencukupi permintaan tiket.
     *
     * @param jumlah Jumlah tiket yang ingin dipesan
     * @return true jika status "Buka" dan sisa kuota >= jumlah, false jika sebaliknya
     */
    public boolean cekKetersediaan(int jumlah) {
        if (jumlah <= 0) {
            return false;
        }
        return "Buka".equalsIgnoreCase(this.statusEvent) && (getSisaKuota() >= jumlah);
    }

    /**
     * Mengurangi kuota dengan mencatatkan penambahan tiket yang berhasil terjual.
     * Jika total tiket terjual mencapai atau melampaui kuota, status event otomatis ditutup.
     *
     * @param jumlah Jumlah tiket terjual yang ditambahkan
     */
    public void kurangiKuota(int jumlah) {
        if (jumlah > 0) {
            this.tiketTerjual += jumlah;
            if (isPenuh()) {
                tutupPenjualan();
                System.out.println("[INFO EVENT] Kuota untuk event '" + this.namaEvent + "' telah PENUH. Penjualan otomatis DITUTUP.");
            }
        }
    }

    /**
     * Memeriksa apakah kuota tiket event sudah habis/terpenuhi.
     *
     * @return true jika tiketTerjual >= kuota
     */
    public boolean isPenuh() {
        return this.tiketTerjual >= this.kuota;
    }

    /**
     * Menghitung sisa kuota tiket yang masih tersedia untuk dipesan.
     *
     * @return Nilai selisih antara kuota total dengan tiket terjual (tidak pernah bernilai negatif)
     */
    public int getSisaKuota() {
        int sisa = this.kuota - this.tiketTerjual;
        return Math.max(0, sisa);
    }

    /**
     * Menutup sesi penjualan tiket event secara eksplisit.
     */
    public void tutupPenjualan() {
        this.statusEvent = "Tutup";
    }

    /**
     * Membuka kembali sesi penjualan tiket event (apabila kuota masih mencukupi).
     */
    public void bukaPenjualan() {
        if (!isPenuh()) {
            this.statusEvent = "Buka";
        }
    }

    /**
     * Menambahkan tiket yang tersedia untuk event ini.
     *
     * @param tiket Objek tiket yang ditambahkan ke inventaris event
     */
    public void tambahTiket(Tiket tiket) {
        if (tiket != null) {
            this.daftarTiket.add(tiket);
        }
    }

    // --- Getter dan Setter Lengkap (Encapsulation) ---

    public String getIdEvent() {
        return idEvent;
    }

    public void setIdEvent(String idEvent) {
        this.idEvent = idEvent;
    }

    public String getNamaEvent() {
        return namaEvent;
    }

    public void setNamaEvent(String namaEvent) {
        this.namaEvent = namaEvent;
    }

    public String getDeskripsi() {
        return deskripsi;
    }

    public void setDeskripsi(String deskripsi) {
        this.deskripsi = deskripsi;
    }

    public Date getTanggal() {
        return tanggal;
    }

    public void setTanggal(Date tanggal) {
        this.tanggal = tanggal;
    }

    public String getLokasi() {
        return lokasi;
    }

    public void setLokasi(String lokasi) {
        this.lokasi = lokasi;
    }

    public int getKuota() {
        return kuota;
    }

    public void setKuota(int kuota) {
        this.kuota = kuota;
        if (isPenuh()) {
            tutupPenjualan();
        }
    }

    public int getTiketTerjual() {
        return tiketTerjual;
    }

    public void setTiketTerjual(int tiketTerjual) {
        this.tiketTerjual = tiketTerjual;
        if (isPenuh()) {
            tutupPenjualan();
        }
    }

    public String getStatusEvent() {
        return statusEvent;
    }

    public void setStatusEvent(String statusEvent) {
        this.statusEvent = statusEvent;
    }

    public List<Tiket> getDaftarTiket() {
        return daftarTiket;
    }

    public void setDaftarTiket(List<Tiket> daftarTiket) {
        this.daftarTiket = daftarTiket;
    }

    public double getHargaDasar() {
        return hargaDasar;
    }

    public void setHargaDasar(double hargaDasar) {
        if (hargaDasar < 0.0) {
            throw new IllegalArgumentException("Harga dasar event tidak boleh negatif.");
        }
        this.hargaDasar = hargaDasar;
    }

    @Override
    public String toString() {
        SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm");
        String tglStr = (tanggal != null) ? sdf.format(tanggal) : "-";
        return String.format("[%s] %s | Tgl: %s | Lokasi: %s | Kuota: %d/%d (Sisa: %d) | Harga: Rp %,.0f | Status: %s",
                idEvent, namaEvent, tglStr, lokasi, tiketTerjual, kuota, getSisaKuota(), hargaDasar, statusEvent);
    }
}

