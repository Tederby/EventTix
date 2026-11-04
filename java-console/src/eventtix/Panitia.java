package eventtix;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

/**
 * Kelas Panitia mewarisi (Inheritance) kelas User dan merepresentasikan penyelenggara
 * atau pengelola acara kampus dalam sistem EventTix.
 *
 * Menerapkan prinsip OOP:
 * - Inheritance: Memperoleh atribut autentikasi dan fungsionalitas dasar dari User.
 * - Polymorphism: Meng-override metode getRole() untuk mengembalikan "Panitia".
 * - Encapsulation: Mengelola atribut jabatan dan daftar event yang dikelola secara privat.
 *
 * @author Kelompok 6 - Modul 2 (user-event)
 */
public class Panitia extends User {
    // Atribut spesifik Panitia (Encapsulation)
    private String jabatan;
    private List<Event> daftarEventDikelola;

    /**
     * Konstruktor untuk inisialisasi entitas Panitia.
     *
     * @param idUser   ID unik pengguna
     * @param nama     Nama lengkap panitia
     * @param email    Email untuk login
     * @param password Password akun
     * @param jabatan  Posisi/jabatan kepanitiaan (misal: "Ketua Panitia", "Koordinator Ticketing")
     */
    public Panitia(String idUser, String nama, String email, String password, String jabatan) {
        super(idUser, nama, email, password);
        this.jabatan = jabatan;
        this.daftarEventDikelola = new ArrayList<>();
    }

    /**
     * Membuat objek Event baru dengan parameter lengkap dan mendaftarkannya ke daftar event yang dikelola.
     *
     * @param idEvent   Kode unik event
     * @param namaEvent Nama event
     * @param deskripsi Informasi rincian event
     * @param tanggal   Waktu pelaksanaan event
     * @param lokasi    Tempat pelaksanaan event
     * @param kuota     Kapasitas maksimum peserta
     * @return Objek Event yang berhasil dibuat
     */
    public Event buatEvent(String idEvent, String namaEvent, String deskripsi, Date tanggal, String lokasi, int kuota) {
        Event eventBaru = new Event(idEvent, namaEvent, deskripsi, tanggal, lokasi, kuota);
        this.daftarEventDikelola.add(eventBaru);
        System.out.println("[SUKSES] Panitia " + getNama() + " (" + jabatan + ") berhasil membuat event: '" + namaEvent + "'.");
        return eventBaru;
    }

    /**
     * Overload metode buatEvent menerima objek Event yang sudah terinstansiasi (sesuai spesifikasi arsitektur).
     *
     * @param data Objek Event
     * @return Objek Event yang ditambahkan
     */
    public Event buatEvent(Event data) {
        if (data != null) {
            this.daftarEventDikelola.add(data);
            System.out.println("[SUKSES] Panitia " + getNama() + " mendaftarkan event: '" + data.getNamaEvent() + "'.");
        }
        return data;
    }

    /**
     * Mengelola status operasional event: mengecek kapasitas kuota dan menutup penjualan jika tiket telah habis.
     *
     * @param event Event yang akan dikelola
     */
    public void kelolaEvent(Event event) {
        if (event == null) {
            System.out.println("[PERINGATAN] Event tidak valid untuk dikelola.");
            return;
        }

        System.out.println("\n--- [MANAJEMEN EVENT] Pengawasan oleh Panitia: " + getNama() + " (" + jabatan + ") ---");
        System.out.println("Nama Event   : " + event.getNamaEvent());
        System.out.println("Status Saat Ini : " + event.getStatusEvent());
        System.out.println("Kapasitas/Terjual : " + event.getTiketTerjual() + " / " + event.getKuota());
        System.out.println("Sisa Kuota   : " + event.getSisaKuota());

        if (event.isPenuh()) {
            event.tutupPenjualan();
            System.out.println("Tindakan     : Kuota penuh terdeteksi. Penjualan tiket DITUTUP.");
        } else {
            System.out.println("Tindakan     : Kuota masih mencukupi. Penjualan tetap BERJALAN (" + event.getStatusEvent() + ").");
        }
        System.out.println("-------------------------------------------------------------------------\n");
    }

    /**
     * Menerbitkan dan mengonfirmasi pembuatan kode promo baru untuk event.
     *
     * @param promo Objek KodePromo yang dirilis
     */
    public void buatKodePromo(KodePromo promo) {
        if (promo != null) {
            System.out.println("[PROMO DITERBITKAN] Panitia " + getNama() + " merilis promo: " + promo.toString());
        } else {
            System.out.println("[PERINGATAN] Objek promo bernilai null.");
        }
    }

    /**
     * Memproses dan memvalidasi tiket peserta saat hadir di lokasi acara (Check-In).
     *
     * @param tiket Objek tiket yang hendak divalidasi
     * @return Objek CheckIn yang mencatat riwayat validasi
     */
    public CheckIn lakukanCheckIn(Tiket tiket) {
        String idCheckIn = "CHK-" + System.currentTimeMillis();
        CheckIn checkIn = new CheckIn(idCheckIn, new Date(), tiket);

        // Eksekusi validasi bisnis tiket
        boolean statusValid = checkIn.validasiTiket();

        // Rekam catatan log
        checkIn.catat();

        if (statusValid) {
            System.out.println("[CHECK-IN SUKSES] Tiket " + tiket.getKodeTiket() +
                    " diverifikasi VALID oleh Panitia (" + getNama() + "). Silakan masuk!");
        } else {
            System.out.println("[CHECK-IN DITOLAK] Tiket " + (tiket != null ? tiket.getKodeTiket() : "null") +
                    " TIDAK DAPAT DIGUNAKAN: " + checkIn.getStatusValidasi());
        }

        return checkIn;
    }

    /**
     * Mencetak laporan rekapitulasi penjualan tiket untuk suatu event dan menghitung estimasi pendapatan.
     *
     * @param event Event yang laporannya ingin ditinjau
     * @return Total nominal pendapatan dari penjualan tiket event tersebut
     */
    public double lihatLaporanPenjualan(Event event) {
        if (event == null) {
            System.out.println("[LAPORAN] Data event tidak ditemukan.");
            return 0.0;
        }

        System.out.println("\n=========================================================================");
        System.out.println("                     LAPORAN REKAPITULASI PENJUALAN EVENT                ");
        System.out.println("=========================================================================");
        System.out.println("ID Event         : " + event.getIdEvent());
        System.out.println("Nama Event       : " + event.getNamaEvent());
        System.out.println("Lokasi           : " + event.getLokasi());
        System.out.println("Kuota Tiket      : " + event.getKuota() + " kursi");
        System.out.println("Tiket Terjual    : " + event.getTiketTerjual() + " tiket");
        System.out.println("Sisa Kuota       : " + event.getSisaKuota() + " tiket");
        System.out.println("Status Penjualan : " + event.getStatusEvent());

        // Hitung total akumulasi pendapatan dari tiket terjual
        double totalPendapatan = 0.0;
        if (event.getDaftarTiket() != null && !event.getDaftarTiket().isEmpty()) {
            for (Tiket t : event.getDaftarTiket()) {
                if ("Terjual".equalsIgnoreCase(t.getStatusTiket()) || "Terpakai".equalsIgnoreCase(t.getStatusTiket())) {
                    totalPendapatan += t.hitungHarga();
                }
            }
        } else {
            // Asumsi dasar estimasi harga tiket reguler jika daftar individual belum terpopulasi
            totalPendapatan = event.getTiketTerjual() * 50000.0;
        }

        System.out.println("Total Pendapatan : Rp " + String.format("%,.0f", totalPendapatan));
        System.out.println("=========================================================================\n");

        return totalPendapatan;
    }

    /**
     * Implementasi Polymorphism dari kelas User.
     *
     * @return "Panitia"
     */
    @Override
    public String getRole() {
        return "Panitia";
    }

    // --- Getter dan Setter (Encapsulation) ---

    public String getJabatan() {
        return jabatan;
    }

    public void setJabatan(String jabatan) {
        this.jabatan = jabatan;
    }

    public List<Event> getDaftarEventDikelola() {
        return daftarEventDikelola;
    }

    public void setDaftarEventDikelola(List<Event> daftarEventDikelola) {
        this.daftarEventDikelola = daftarEventDikelola;
    }
}

