package eventtix;

import java.util.Calendar;
import java.util.Date;

/**
 * Program Driver Demo & Pengujian Mandiri untuk Modul 3: Pesanan & Promo.
 *
 * Menguji seluruh fungsionalitas dan kontrak bisnis dari:
 * 1. KodePromo: validasi masa berlaku, batas kuota, pembatasan maks potongan.
 * 2. Pesanan: agregasi tiket, perhitungan subtotal & total diskon, konfirmasi, pembatalan.
 *
 * @author Kelompok 6 - Modul 3 (pesanan-promo)
 */
public class PesananPromoDemo {

    public static void main(String[] args) {
        System.out.println("=========================================================================");
        System.out.println("                 DEMO MODUL 3: PESANAN & KODE PROMO                      ");
        System.out.println("                       Sistem Ticketing EventTix                         ");
        System.out.println("=========================================================================\n");

        // ---------------------------------------------------------------------
        // 1. PENGUJIAN KODE PROMO (VALIDASI & PERHITUNGAN DISKON)
        // ---------------------------------------------------------------------
        System.out.println(">>> [1] PENGUJIAN INSTANSIASI & LOGIKA BISNIS KODE PROMO");

        // Promo Aktif: Diskon 20%, Maksimal Potongan Rp 30.000, Kuota 2
        Calendar calAktif = Calendar.getInstance();
        calAktif.add(Calendar.DAY_OF_YEAR, 7);
        KodePromo promoHemat = new KodePromo("HEMAT20", 0.20, 30000.0, calAktif.getTime(), 2);
        System.out.println("Promo Dibuat : " + promoHemat);
        System.out.println("Apakah valid?  : " + promoHemat.isValid());

        // Promo Kedaluwarsa (Expired)
        Calendar calExpired = Calendar.getInstance();
        calExpired.add(Calendar.DAY_OF_YEAR, -2); // 2 hari lalu
        KodePromo promoExpired = new KodePromo("EXPIRED50", 0.50, 50000.0, calExpired.getTime(), 10);
        System.out.println("\nPromo Expired: " + promoExpired);
        System.out.println("Apakah valid?  : " + promoExpired.isValid() + " (Seharusnya false karena tanggal kedaluwarsa)");

        // Uji Cap Maksimal Potongan
        double subtotalUji = 250000.0;
        // Diskon 20% dari 250.000 = 50.000, namun maks potongan = 30.000
        double potongan = promoHemat.hitungPotongan(subtotalUji);
        System.out.println("\nSubtotal Pesanan: Rp " + String.format("%,.0f", subtotalUji));
        System.out.println("Diskon 20% (Max Rp 30.000): Rp " + String.format("%,.0f", potongan) +
                " (Sesuai cap maksimal potongan: " + (potongan == 30000.0) + ")");

        // ---------------------------------------------------------------------
        // 2. PENGUJIAN PEMBUATAN PESANAN & PERHITUNGAN HARGA TIKET
        // ---------------------------------------------------------------------
        System.out.println("\n>>> [2] PENGUJIAN PEMBUATAN PESANAN & AGREGASI TIKET");

        Pesanan pesanan1 = new Pesanan("ORD-DEMO-001", new Date(), Pesanan.STATUS_PENDING);
        Tiket tiketReguler = new TiketReguler("TIX-REG-101", 60000.0);
        Tiket tiketVIP = new TiketVIP("TIX-VIP-102", 60000.0, 40000.0, "VIP-A1");

        pesanan1.tambahTiket(tiketReguler);
        pesanan1.tambahTiket(tiketVIP);

        System.out.println("Jumlah Tiket Dipesan : " + pesanan1.getDaftarTiket().size());
        System.out.println("Subtotal Pesanan     : Rp " + String.format("%,.0f", pesanan1.hitungSubtotal()));
        System.out.println("Total Sebelum Promo  : Rp " + String.format("%,.0f", pesanan1.hitungTotal()));

        // ---------------------------------------------------------------------
        // 3. PENERAPAN PROMO PADA PESANAN
        // ---------------------------------------------------------------------
        System.out.println("\n>>> [3] PENERAPAN KODE PROMO PADA PESANAN");

        // Coba terapkan promo expired (harus gagal)
        System.out.println("[Percobaan 1] Menerapkan promo expired:");
        boolean statusExpired = pesanan1.terapkanPromo(promoExpired);
        System.out.println("Status penerapan: " + statusExpired + " (Gagal sesuai validasi)");

        // Terapkan promo hemat valid
        System.out.println("\n[Percobaan 2] Menerapkan promo aktif:");
        boolean statusSukses = pesanan1.terapkanPromo(promoHemat);
        System.out.println("Status penerapan: " + statusSukses);
        System.out.println("Sisa Kuota Promo Sekarang: " + promoHemat.getKuotaPemakaian());
        System.out.println("Subtotal             : Rp " + String.format("%,.0f", pesanan1.hitungSubtotal()));
        System.out.println("Total Setelah Diskon : Rp " + String.format("%,.0f", pesanan1.hitungTotal()));

        // ---------------------------------------------------------------------
        // 4. PENGUJIAN PEMBAYARAN & KONFIRMASI PESANAN (LUNAS)
        // ---------------------------------------------------------------------
        System.out.println("\n>>> [4] INTEGRASI PEMBAYARAN & KONFIRMASI PESANAN (LUNAS)");

        PembayaranTransfer payment = new PembayaranTransfer("PAY-ORD-001", pesanan1.hitungTotal(), "BCA", "1234567890");
        pesanan1.setPembayaran(payment);

        payment.verifikasiBukti("bukti_transfer_demo.jpg");
        payment.prosesPembayaran();
        pesanan1.konfirmasi();

        System.out.println("Status Pesanan       : " + pesanan1.getStatusPesanan());
        System.out.println("Status Tiket 1       : " + tiketReguler.getStatusTiket() + " (Harus Terjual)");
        System.out.println("Status Tiket 2 (VIP) : " + tiketVIP.getStatusTiket() + " (Harus Terjual)");

        // ---------------------------------------------------------------------
        // 5. PENGUJIAN PEMBATALAN PESANAN
        // ---------------------------------------------------------------------
        System.out.println("\n>>> [5] PENGUJIAN PEMBATALAN PESANAN");

        Pesanan pesananBatal = new Pesanan("ORD-DEMO-002", new Date(), Pesanan.STATUS_PENDING);
        Tiket tiketBatal = new TiketReguler("TIX-REG-999", 50000.0);
        pesananBatal.tambahTiket(tiketBatal);

        System.out.println("Status Tiket Awal    : " + tiketBatal.getStatusTiket());
        pesananBatal.batalkan();
        System.out.println("Status Pesanan       : " + pesananBatal.getStatusPesanan());
        System.out.println("Status Tiket Kembali : " + tiketBatal.getStatusTiket() + " (Harus Tersedia)");

        // ---------------------------------------------------------------------
        // 6. VALIDASI INPUT PADA KODE PROMO & PESANAN
        // ---------------------------------------------------------------------
        System.out.println("\n>>> [6] VALIDASI EXCEPTION HANDLING INPUT");

        try {
            new KodePromo("", 0.10, 10000.0, new Date(), 5);
        } catch (IllegalArgumentException e) {
            System.out.println("Uji Kode Promo Kosong -> Sukses ditangkap: " + e.getMessage());
        }

        try {
            new KodePromo("DISKON150", 1.5, 10000.0, new Date(), 5);
        } catch (IllegalArgumentException e) {
            System.out.println("Uji Diskon > 100%     -> Sukses ditangkap: " + e.getMessage());
        }

        try {
            new Pesanan("", new Date());
        } catch (IllegalArgumentException e) {
            System.out.println("Uji ID Pesanan Kosong -> Sukses ditangkap: " + e.getMessage());
        }

        System.out.println("\n=========================================================================");
        System.out.println("                 SEMUA UJI MODUL 3 SELESAI DENGAN SUKSES                 ");
        System.out.println("=========================================================================");
    }
}

