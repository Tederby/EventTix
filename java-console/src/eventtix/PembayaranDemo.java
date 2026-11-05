package eventtix;

/**
 * Demo/test modul Pembayaran (untuk screenshot dokumen Tahap 2).
 * Class Main milik Bintang; file ini berdiri sendiri dan boleh dihapus setelah merge.
 */
public class PembayaranDemo {

    public static void main(String[] args) {
        System.out.println("=== DEMO MODUL PEMBAYARAN EVENTTIX ===\n");

        // Polymorphism: satu tipe referensi (Pembayaran), dua perilaku berbeda
        Pembayaran[] daftar = {
            new PembayaranTransfer("PAY-001", 150000, "BCA", "1234567890"),
            new PembayaranEWallet("PAY-002", 150000, "GoPay", "081234567890")
        };

        System.out.println("[1] Biaya admin & total (polymorphism)");
        for (Pembayaran p : daftar) {
            System.out.println("  " + p);
        }

        System.out.println("\n[2] Transfer: proses SEBELUM bukti diverifikasi");
        PembayaranTransfer tf = new PembayaranTransfer("PAY-003", 200000, "Mandiri", "9876543210");
        System.out.println("  prosesPembayaran() = " + tf.prosesPembayaran()
                + " | status = " + tf.getStatusBayar());

        System.out.println("\n[3] Transfer: bukti tidak valid, lalu bukti valid");
        tf = new PembayaranTransfer("PAY-004", 200000, "Mandiri", "9876543210");
        System.out.println("  verifikasiBukti(\"bukti.txt\")  = " + tf.verifikasiBukti("bukti.txt"));
        System.out.println("  verifikasiBukti(\"bukti.jpg\")  = " + tf.verifikasiBukti("bukti.jpg")
                + " | status = " + tf.getStatusBayar());
        System.out.println("  prosesPembayaran()            = " + tf.prosesPembayaran()
                + " | status = " + tf.getStatusBayar());
        System.out.println("  waktuBayar = " + tf.getWaktuBayar());

        System.out.println("\n[4] E-wallet: proses langsung, lalu coba bayar dua kali");
        PembayaranEWallet ew = (PembayaranEWallet) daftar[1];
        System.out.println("  prosesPembayaran() #1 = " + ew.prosesPembayaran()
                + " | status = " + ew.getStatusBayar());
        System.out.println("  prosesPembayaran() #2 = " + ew.prosesPembayaran() + " (ditolak)");

        System.out.println("\n[5] Validasi input");
        try {
            new PembayaranEWallet("PAY-005", 50000, "Telegram", "081234567890");
        } catch (IllegalArgumentException e) {
            System.out.println("  Provider salah  -> " + e.getMessage());
        }
        try {
            new PembayaranTransfer("PAY-006", 50000, "BNI", "12AB");
        } catch (IllegalArgumentException e) {
            System.out.println("  Rekening salah  -> " + e.getMessage());
        }
        try {
            new PembayaranTransfer("PAY-007", 0, "BNI", "1234567890");
        } catch (IllegalArgumentException e) {
            System.out.println("  Jumlah 0        -> " + e.getMessage());
        }
    }
}
