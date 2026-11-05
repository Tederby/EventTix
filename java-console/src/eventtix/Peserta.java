package eventtix;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

/**
 * Kelas Peserta mewarisi (Inheritance) kelas User dan merepresentasikan pengguna
 * mahasiswa/tamu yang mendaftar dan membeli tiket event di sistem EventTix.
 *
 * Menerapkan prinsip OOP:
 * - Inheritance: Memperoleh seluruh atribut dan metode dasar dari kelas abstrak User.
 * - Polymorphism: Melakukan override pada metode abstrak getRole() untuk mengembalikan "Peserta".
 * - Encapsulation: Mengelola atribut NIM, nomor telepon, dan riwayat pesanan secara aman.
 *
 * @author Kelompok 6 - Modul 2 (user-event)
 */
public class Peserta extends User {
    // Atribut spesifik Peserta (Encapsulation)
    private String nim;
    private String noTelepon;
    private List<Pesanan> riwayatPesanan;

    /**
     * Konstruktor untuk inisialisasi entitas Peserta.
     *
     * @param idUser    ID unik pengguna
     * @param nama      Nama lengkap peserta
     * @param email     Email untuk login
     * @param password  Password akun
     * @param nim       Nomor Induk Mahasiswa
     * @param noTelepon Nomor kontak aktif
     */
    public Peserta(String idUser, String nama, String email, String password, String nim, String noTelepon) {
        super(idUser, nama, email, password);
        this.nim = nim;
        this.noTelepon = noTelepon;
        this.riwayatPesanan = new ArrayList<>();
    }

    /**
     * Menampilkan katalog daftar event yang tersedia secara rapi di console.
     *
     * @param daftarEvent Koleksi event yang akan ditampilkan
     * @return Daftar event yang diperiksa
     */
    public List<Event> lihatEvent(List<Event> daftarEvent) {
        System.out.println("\n=========================================================================================");
        System.out.println("                              KATALOG EVENT KAMPUS TERSEDIA                             ");
        System.out.println("=========================================================================================");
        System.out.printf("%-12s | %-28s | %-16s | %-15s | %-8s | %-6s\n",
                "ID Event", "Nama Event", "Tanggal", "Lokasi", "Sisa/Total", "Status");
        System.out.println("-----------------------------------------------------------------------------------------");

        if (daftarEvent == null || daftarEvent.isEmpty()) {
            System.out.println("               Belum ada event yang tersedia saat ini.                    ");
        } else {
            SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm");
            for (Event evt : daftarEvent) {
                String tglStr = evt.getTanggal() != null ? sdf.format(evt.getTanggal()) : "-";
                String kuotaStr = evt.getSisaKuota() + "/" + evt.getKuota();
                System.out.printf("%-12s | %-28s | %-16s | %-15s | %-10s | %-6s\n",
                        evt.getIdEvent(),
                        evt.getNamaEvent(),
                        tglStr,
                        evt.getLokasi(),
                        kuotaStr,
                        evt.getStatusEvent());
            }
        }
        System.out.println("=========================================================================================\n");
        return daftarEvent;
    }

    /**
     * Overload lihatEvent() tanpa parameter sesuai kontrak Architecture Spec.
     *
     * @return Koleksi kosong atau null jika tidak disediakan daftar event eksternal
     */
    public List<Event> lihatEvent() {
        return new ArrayList<>();
    }

    /**
     * Membuat pesanan tiket baru untuk suatu event dengan validasi ketersediaan kuota.
     *
     * @param event  Event yang akan dipesan
     * @param jumlah Jumlah tiket yang ingin dibeli
     * @return Objek Pesanan jika kuota mencukupi, atau null jika gagal
     */
    public Pesanan buatPesanan(Event event, int jumlah) {
        if (event == null) {
            System.out.println("[GAGAL] Event yang dipilih tidak valid.");
            return null;
        }

        if (jumlah <= 0) {
            System.out.println("[GAGAL] Jumlah pemesanan tiket minimal 1.");
            return null;
        }

        // Validasi ketersediaan kuota pada event
        if (!event.cekKetersediaan(jumlah)) {
            System.out.println("[GAGAL] Maaf, sisa kuota event '" + event.getNamaEvent() +
                    "' tidak mencukupi (Sisa: " + event.getSisaKuota() + ", Diminta: " + jumlah + ").");
            return null;
        }

        // Pembuatan ID pesanan unik berbasis timestamp
        String idPesanan = "ORD-" + System.currentTimeMillis();
        Pesanan pesananBaru = new Pesanan(idPesanan, new Date(), "Pending", this, event);

        // Alokasikan tiket default reguler atau sesuai inventaris event
        for (int i = 1; i <= jumlah; i++) {
            String kodeTiket = "TIX-" + event.getIdEvent() + "-" + (event.getTiketTerjual() + i);
            TiketReguler tiket = new TiketReguler(kodeTiket, 50000.0);
            pesananBaru.tambahTiket(tiket);
        }

        // Kurangi kuota pada event
        event.kurangiKuota(jumlah);

        // Catatkan pesanan ke riwayat pesanan peserta
        this.riwayatPesanan.add(pesananBaru);

        System.out.println("[SUKSES] Pesanan " + idPesanan + " berhasil dibuat untuk " + jumlah +
                " tiket event '" + event.getNamaEvent() + "'.");
        return pesananBaru;
    }

    /**
     * Menampilkan riwayat pesanan yang pernah dibuat oleh peserta ini.
     *
     * @return Daftar seluruh pesanan milik peserta
     */
    public List<Pesanan> lihatRiwayatPesanan() {
        System.out.println("\n-----------------------------------------------------------------------------------------");
        System.out.println("                      RIWAYAT PESANAN PESERTA: " + getNama() + " (" + nim + ")");
        System.out.println("-----------------------------------------------------------------------------------------");

        if (riwayatPesanan == null || riwayatPesanan.isEmpty()) {
            System.out.println("Anda belum pernah melakukan pemesanan tiket.");
        } else {
            SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm");
            for (Pesanan p : riwayatPesanan) {
                String tgl = p.getTanggalPesan() != null ? sdf.format(p.getTanggalPesan()) : "-";
                String eventName = (p.getEvent() != null) ? p.getEvent().getNamaEvent() : "-";
                int jmlTiket = (p.getDaftarTiket() != null) ? p.getDaftarTiket().size() : 0;
                double total = p.hitungTotal();

                System.out.printf("* Order ID: %-18s | Tgl: %-16s | Event: %-22s | Tiket: %2d pcs | Total: Rp %10s | Status: %s\n",
                        p.getIdPesanan(),
                        tgl,
                        eventName,
                        jmlTiket,
                        String.format("%,.0f", total),
                        p.getStatusPesanan());
            }
        }
        System.out.println("-----------------------------------------------------------------------------------------\n");
        return this.riwayatPesanan;
    }

    /**
     * Implementasi Polymorphism dari kelas User.
     *
     * @return "Peserta"
     */
    @Override
    public String getRole() {
        return "Peserta";
    }

    // --- Getter dan Setter (Encapsulation) ---

    public String getNim() {
        return nim;
    }

    public void setNim(String nim) {
        this.nim = nim;
    }

    public String getNoTelepon() {
        return noTelepon;
    }

    public void setNoTelepon(String noTelepon) {
        this.noTelepon = noTelepon;
    }

    public List<Pesanan> getRiwayatPesanan() {
        return riwayatPesanan;
    }

    public void setRiwayatPesanan(List<Pesanan> riwayatPesanan) {
        this.riwayatPesanan = riwayatPesanan;
    }
}
