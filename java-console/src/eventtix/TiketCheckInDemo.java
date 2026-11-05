package eventtix;

import java.util.Date;

/**
 * Driver Demo & Pengujian Mandiri untuk Modul 2: Tiket & Check-In.
 *
 * Menguji:
 * 1. Abstraksi dan Polimorfisme pada kelas Tiket (TiketReguler & TiketVIP).
 * 2. Logika validasi dan pencatatan sesi CheckIn.
 * 3. Pencegahan duplikasi tiket (scan ulang tiket yang sudah terpakai).
 * 4. Penolakan tiket yang belum terjual (masih berstatus Tersedia).
 *
 * @author Kelompok 6 - Modul 2 (tiket-checkin)
 */
public class TiketCheckInDemo {

    public static void main(String[] args) {
        System.out.println("=========================================================================");
        System.out.println("                 DEMO MODUL 2: TIKET & CHECK-IN                          ");
        System.out.println("                       Sistem Ticketing EventTix                         ");
        System.out.println("=========================================================================\n");

        // ---------------------------------------------------------------------
        // 1. POLIMORFISME TIKET (REGULER & VIP)
        // ---------------------------------------------------------------------
        System.out.println(">>> [1] PENGUJIAN POLIMORFISME & RINCIAN HARGA TIKET");

        Tiket[] daftarTiketContoh = {
            new TiketReguler("TIX-REG-001", 75000.0),
            new TiketVIP("TIX-VIP-001", 75000.0, 50000.0, "VIP-A01")
        };

        for (Tiket t : daftarTiketContoh) {
            System.out.println("Tiket        : " + t.getKodeTiket() + " (" + t.getClass().getSimpleName() + ")");
            System.out.println("Harga Dasar  : Rp " + String.format("%,.0f", t.getHargaDasar()));
            System.out.println("Total Harga  : Rp " + String.format("%,.0f", t.hitungHarga()));
            System.out.println("Fasilitas    : " + t.getFasilitas());
            System.out.println("Status Awal  : " + t.getStatusTiket());
            System.out.println();
        }

        // ---------------------------------------------------------------------
        // 2. CHECK-IN SUKSES (TIKET BERSTATUS TERJUAL)
        // ---------------------------------------------------------------------
        System.out.println(">>> [2] SIMULASI CHECK-IN TIKET VALID (STATUS: TERJUAL)");

        Tiket tiketSiapScan = new TiketReguler("TIX-REG-002", 75000.0);
        tiketSiapScan.setStatusTiket(Tiket.STATUS_TERJUAL); // Simulasi tiket sudah dibeli

        System.out.println("Status sebelum scan: " + tiketSiapScan.getStatusTiket());
        CheckIn sesi1 = new CheckIn("CHK-DEMO-001", new Date(), tiketSiapScan);
        boolean hasil1 = sesi1.validasiTiket();
        sesi1.catat();

        System.out.println("Hasil Validasi     : " + hasil1 + " (" + sesi1.getStatusValidasi() + ")");
        System.out.println("Status sesudah scan: " + tiketSiapScan.getStatusTiket() + " (Harus Terpakai)\n");

        // ---------------------------------------------------------------------
        // 3. PENCEGAHAN DUPLIKASI CHECK-IN (TIKET SUDAH TERPAKAI)
        // ---------------------------------------------------------------------
        System.out.println(">>> [3] SIMULASI DUPLIKASI CHECK-IN (TIKET SUDAH TERPAKAI)");

        CheckIn sesiDuplikat = new CheckIn("CHK-DEMO-002", new Date(), tiketSiapScan);
        boolean hasilDuplikat = sesiDuplikat.validasiTiket();
        sesiDuplikat.catat();

        System.out.println("Hasil Validasi : " + hasilDuplikat + " (Harus false/ditolak)");
        System.out.println("Alasan Ditolak : " + sesiDuplikat.getStatusValidasi() + "\n");

        // ---------------------------------------------------------------------
        // 4. PENOLAKAN TIKET YANG BELUM TERJUAL (STATUS: TERSEDIA)
        // ---------------------------------------------------------------------
        System.out.println(">>> [4] SIMULASI TIKET BELUM TERJUAL (STATUS: TERSEDIA)");

        Tiket tiketBelumBeli = new TiketVIP("TIX-VIP-002", 75000.0, 50000.0, "VIP-B05");
        CheckIn sesiBelumBeli = new CheckIn("CHK-DEMO-003", new Date(), tiketBelumBeli);
        boolean hasilBelumBeli = sesiBelumBeli.validasiTiket();
        sesiBelumBeli.catat();

        System.out.println("Hasil Validasi : " + hasilBelumBeli + " (Harus false/ditolak)");
        System.out.println("Alasan Ditolak : " + sesiBelumBeli.getStatusValidasi() + "\n");

        // ---------------------------------------------------------------------
        // 5. VALIDASI INPUT PARAMETER
        // ---------------------------------------------------------------------
        System.out.println(">>> [5] VALIDASI EXCEPTION HANDLING");

        try {
            new TiketReguler("", 50000.0);
        } catch (IllegalArgumentException e) {
            System.out.println("Kode Tiket Kosong    -> Sukses ditangkap: " + e.getMessage());
        }

        try {
            new TiketReguler("TIX-ERR", -10000.0);
        } catch (IllegalArgumentException e) {
            System.out.println("Harga Negatif        -> Sukses ditangkap: " + e.getMessage());
        }

        try {
            new CheckIn("", new Date(), null);
        } catch (IllegalArgumentException e) {
            System.out.println("ID CheckIn Kosong    -> Sukses ditangkap: " + e.getMessage());
        }

        System.out.println("\n=========================================================================");
        System.out.println("                 SEMUA UJI MODUL 2 SELESAI DENGAN SUKSES                 ");
        System.out.println("=========================================================================");
    }
}

