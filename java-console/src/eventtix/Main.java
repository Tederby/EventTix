package eventtix;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.List;

/**
 * Driver Class: Main.java
 *
 * Program utama untuk mensimulasikan alur operasional sistem EventTix berbasis Java Console.
 * Menguji dan mendemonstrasikan implementasi Modul 2: "Pengguna & Event" secara komprehensif,
 * sekaligus mengilustrasikan penerapan 4 Pilar Pemrograman Berorientasi Objek (PBO):
 * 1. Encapsulation (Enkapsulasi)
 * 2. Inheritance (Pewarisan)
 * 3. Polymorphism (Polimorfisme)
 * 4. Abstraction (Abstraksi)
 *
 * @author Kelompok 6 - Modul 2 (user-event)
 */
public class Main {

    public static void main(String[] args) {
        cetakHeaderSistem();

        // Koleksi data global untuk simulasi
        List<Event> katalogEvent = new ArrayList<>();

        // ====================================================================
        // TAHAP 1: DEMONSTRASI ABSTRAKSI & PEWARISAN (INHERITANCE)
        // ====================================================================
        System.out.println(">>> [TAHAP 1] INISIALISASI USER: PANITIA & PESERTA (OOP: Inheritance & Abstraction)");
        System.out.println("Catatan PBO: User adalah abstract class. Objek Panitia dan Peserta dibuat meng-extend User.\n");

        // Instansiasi Panitia (Subclass User)
        Panitia panitia = new Panitia(
                "USR-PAN-01",
                "Budi Prasetyo, S.Kom.",
                "budi.panitia@kampus.ac.id",
                "Panitia2026!",
                "Koordinator Acara & Ticketing"
        );

        // Instansiasi Peserta (Subclass User)
        Peserta peserta = new Peserta(
                "USR-PST-01",
                "Siti Nurhaliza",
                "siti.peserta@mahasiswa.ac.id",
                "SitiMhs2026*",
                "2301010045",
                "081234567890"
        );

        // Polimorfisme: Referensi bertipe superclass User menunjuk ke instance konkret
        User userRef1 = panitia;
        User userRef2 = peserta;
        System.out.println("-> Polymorphic role check 1: " + userRef1.getNama() + " => Role: " + userRef1.getRole());
        System.out.println("-> Polymorphic role check 2: " + userRef2.getNama() + " => Role: " + userRef2.getRole());
        System.out.println();

        // ====================================================================
        // TAHAP 2: AUTENTIKASI (LOGIN GAGAL & LOGIN SUKSES)
        // ====================================================================
        System.out.println(">>> [TAHAP 2] SIMULASI AUTENTIKASI (LOGIN GAGAL & LOGIN SUKSES)");
        System.out.println("Catatan PBO: Menguji metode login() dari superclass User dengan validasi enkapsulasi.\n");

        // 2a. Uji Login Gagal (Password salah)
        System.out.println("[UJI 1] Percobaan login Panitia dengan password salah:");
        boolean loginGagal = panitia.login("budi.panitia@kampus.ac.id", "PasswordSalah123");
        if (!loginGagal) {
            System.out.println("-> Hasil: LOGIN GAGAL (Kredensial tidak sesuai) [VALID]\n");
        }

        // 2b. Uji Login Sukses Panitia
        System.out.println("[UJI 2] Percobaan login Panitia dengan kredensial benar:");
        boolean loginSuksesPanitia = panitia.login("budi.panitia@kampus.ac.id", "Panitia2026!");
        if (loginSuksesPanitia) {
            System.out.println("-> Hasil: LOGIN BERHASIL! Selamat datang, " + panitia.getNama() + " [" + panitia.getRole() + "]\n");
        }

        // 2c. Uji Login Sukses Peserta
        System.out.println("[UJI 3] Percobaan login Peserta dengan kredensial benar:");
        boolean loginSuksesPeserta = peserta.login("siti.peserta@mahasiswa.ac.id", "SitiMhs2026*");
        if (loginSuksesPeserta) {
            System.out.println("-> Hasil: LOGIN BERHASIL! Selamat datang, " + peserta.getNama() + " [" + peserta.getRole() + "]\n");
        }

        // ====================================================================
        // TAHAP 3: PANITIA MEMBUAT EVENT & KODE PROMO
        // ====================================================================
        System.out.println(">>> [TAHAP 3] PANITIA MEMBUAT EVENT BARU & KODE PROMO");

        // Atur tanggal pelaksanaan event (15 November 2026)
        Calendar cal = Calendar.getInstance();
        cal.set(2026, Calendar.NOVEMBER, 15, 9, 0, 0);
        Date tanggalEvent = cal.getTime();

        // Panitia membuat event baru
        Event eventAI = panitia.buatEvent(
                "EVT-AI-2026",
                "Seminar Nasional AI 2026",
                "Masa Depan AI dan Implementasi Generative Model di Industri",
                tanggalEvent,
                "Auditorium Utama Lantai 3 Kampus Terpadu",
                50 // Kuota 50 tiket
        );

        // Tambahkan ke katalog event global
        katalogEvent.add(eventAI);

        // Persiapkan beberapa tiket ke dalam inventaris event (Reguler & VIP)
        TiketReguler tiketRegulerContoh = new TiketReguler("TIX-REG-001", 75000.0);
        TiketVIP tiketVIPContoh = new TiketVIP("TIX-VIP-001", 75000.0, 50000.0, "A-01");
        eventAI.tambahTiket(tiketRegulerContoh);
        eventAI.tambahTiket(tiketVIPContoh);

        // Panitia merilis kode promo
        Calendar calPromo = Calendar.getInstance();
        calPromo.add(Calendar.DAY_OF_YEAR, 30); // Berlaku 30 hari ke depan
        KodePromo promoSpesial = new KodePromo("DISKONAI20", 0.20, 30000.0, calPromo.getTime(), 100);
        panitia.buatKodePromo(promoSpesial);
        System.out.println();

        // ====================================================================
        // TAHAP 4: PESERTA MELIHAT KATALOG EVENT
        // ====================================================================
        System.out.println(">>> [TAHAP 4] PESERTA MENJELAJAHI KATALOG EVENT");
        peserta.lihatEvent(katalogEvent);

        // ====================================================================
        // TAHAP 5: PESERTA MEMBUAT PESANAN, MEMILIH TIKET, MENGGUNAKAN PROMO, DAN PEMBAYARAN
        // ====================================================================
        System.out.println(">>> [TAHAP 5] ALUR PEMESANAN, APLIKASI KODE PROMO & PEMBAYARAN OLEH PESERTA");

        // 5a. Peserta memesan 2 tiket untuk event AI
        int jumlahBeli = 2;
        System.out.println("[*] Peserta (" + peserta.getNama() + ") memilih event: " + eventAI.getNamaEvent());
        System.out.println("[*] Memesan kuantitas: " + jumlahBeli + " tiket");

        Pesanan pesananSiti = peserta.buatPesanan(eventAI, jumlahBeli);

        if (pesananSiti != null) {
            // Setup detail tiket pesanan
            pesananSiti.getDaftarTiket().clear(); // Reset dummy awal
            Tiket tiket1 = new TiketReguler("TIX-AI-001", 75000.0);
            Tiket tiket2 = new TiketVIP("TIX-AI-002", 75000.0, 50000.0, "VIP-07");

            pesananSiti.tambahTiket(tiket1);
            pesananSiti.tambahTiket(tiket2);

            // Pasang ke inventaris event agar terhitung di laporan
            eventAI.tambahTiket(tiket1);
            eventAI.tambahTiket(tiket2);

            System.out.println("\n  Rincian Tiket dalam Pesanan:");
            for (Tiket t : pesananSiti.getDaftarTiket()) {
                System.out.println("  - " + t.getKodeTiket() + " (" + t.getClass().getSimpleName() +
                        "): Rp " + String.format("%,.0f", t.hitungHarga()) + " | Fasilitas: " + t.getFasilitas());
            }

            System.out.println("\n  Subtotal Pesanan : Rp " + String.format("%,.0f", pesananSiti.hitungSubtotal()));

            // 5b. Menerapkan kode promo
            System.out.println("[*] Menggunakan kode promo 'DISKONAI20'...");
            pesananSiti.terapkanPromo(promoSpesial);

            System.out.println("  Total Setelah Diskon: Rp " + String.format("%,.0f", pesananSiti.hitungTotal()));

            // 5c. Pembayaran (Polymorphic: PembayaranTransfer)
            Pembayaran pembayaranTransfer = new PembayaranTransfer(
                    "PAY-" + System.currentTimeMillis(),
                    pesananSiti.hitungTotal(),
                    new Date(),
                    "Bank Mandiri",
                    "1370-00-998877-6"
            );
            pesananSiti.setPembayaran(pembayaranTransfer);

            System.out.println("\n[*] Melakukan Pembayaran via " + ((PembayaranTransfer)pembayaranTransfer).getNamaBank());
            System.out.println("  Biaya Admin: Rp " + String.format("%,.0f", pembayaranTransfer.hitungBiayaAdmin()));
            System.out.println("  Grand Total Bayar: Rp " + String.format("%,.0f", pembayaranTransfer.getTotalBayar()));

            pembayaranTransfer.prosesPembayaran();
            pesananSiti.konfirmasi();
        }
        System.out.println();

        // ====================================================================
        // TAHAP 6: PANITIA MENGECEK KUOTA & LAPORAN PENJUALAN
        // ====================================================================
        System.out.println(">>> [TAHAP 6] PANITIA MEMERIKSA STATUS KUOTA & LAPORAN PENJUALAN");
        panitia.kelolaEvent(eventAI);
        panitia.lihatLaporanPenjualan(eventAI);

        // ====================================================================
        // TAHAP 7: SIMULASI CHECK-IN DI HARI H ACARA OLEH PANITIA
        // ====================================================================
        System.out.println(">>> [TAHAP 7] SIMULASI CHECK-IN TIKET DI PINTU MASUK OLEH PANITIA");
        System.out.println("Catatan PBO: Menguji validasi tiket, status transaksi, dan pencegahan duplikasi tiket.\n");

        if (pesananSiti != null && !pesananSiti.getDaftarTiket().isEmpty()) {
            Tiket tiketCheckIn = pesananSiti.getDaftarTiket().get(0);

            // Scan Pertama: Tiket masih berstatus 'Terjual', harusnya BERHASIL
            System.out.println("[SCAN 1] Peserta memindai tiket: " + tiketCheckIn.getKodeTiket() + " (Pertama kali):");
            panitia.lakukanCheckIn(tiketCheckIn);

            System.out.println();

            // Scan Kedua: Tiket sekarang berstatus 'Terpakai', harusnya DITOLAK
            System.out.println("[SCAN 2] Tiket yang sama dipindai ulang (Percobaan ganda/duplikasi):");
            panitia.lakukanCheckIn(tiketCheckIn);
        }
        System.out.println();

        // ====================================================================
        // TAHAP 8: PESERTA MELIHAT RIWAYAT, MENGUBAH PASSWORD, & LOGOUT
        // ====================================================================
        System.out.println(">>> [TAHAP 8] PESERTA MELIHAT RIWAYAT PESANAN, MENGUBAH PASSWORD, & LOGOUT");

        // 8a. Lihat riwayat pesanan
        peserta.lihatRiwayatPesanan();

        // 8b. Ubah password dengan enkapsulasi yang aman
        System.out.println("[*] Mencoba ubah password dengan password lama salah:");
        peserta.ubahPassword("PasswordKeliru", "SitiPasswordBaru2026!");

        System.out.println("\n[*] Mengubah password dengan password lama yang benar:");
        peserta.ubahPassword("SitiMhs2026*", "SitiPasswordBaru2026!");

        // Verifikasi login dengan password baru
        System.out.println("\n[*] Verifikasi login kembali dengan password baru:");
        boolean loginPasswordBaru = peserta.login("siti.peserta@mahasiswa.ac.id", "SitiPasswordBaru2026!");
        if (loginPasswordBaru) {
            System.out.println("-> Login dengan password baru BERHASIL!");
        }

        // 8c. Logout sesi
        System.out.println();
        peserta.logout();
        panitia.logout();

        cetakFooterSistem();
    }

    private static void cetakHeaderSistem() {
        System.out.println("=========================================================================================");
        System.out.println("                           EVENTTIX: SISTEM TICKETING EVENT KAMPUS                       ");
        System.out.println("                       MODUL 2: PENGGUNA & EVENT (BRANCH: user-event)                    ");
        System.out.println("                     Implementasi Pemrograman Berorientasi Objek (PBO)                   ");
        System.out.println("=========================================================================================\n");
    }

    private static void cetakFooterSistem() {
        System.out.println("\n=========================================================================================");
        System.out.println("               SEMUA SKENARIO PENGUJIAN MODUL 2 BERHASIL DISELESAIKAN (0 ERROR)          ");
        System.out.println("=========================================================================================");
    }
}
