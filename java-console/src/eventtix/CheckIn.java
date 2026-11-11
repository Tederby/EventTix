package eventtix;

import java.text.SimpleDateFormat;
import java.util.Date;

/**
 * Kelas CheckIn mencatat dan memproses validasi tiket peserta saat masuk ke lokasi acara.
 *
 * Menerapkan prinsip OOP:
 * - Association / Dependency: CheckIn bergantung pada objek Tiket untuk memeriksa dan mengubah statusnya.
 * - Encapsulation: Seluruh atribut transaksi check-in bersifat privat dan diverifikasi secara ketat.
 *
 * @author Kelompok 6 - Modul 2 (tiket-checkin)
 */
public class CheckIn {

    private String idCheckIn;
    private Date waktuCheckIn;
    private String statusValidasi; // "Berhasil", "Gagal (...)"
    private Tiket tiket;

    /**
     * Konstruktor lengkap entitas CheckIn.
     *
     * @param idCheckIn    ID unik sesi check-in
     * @param waktuCheckIn Waktu pemindaian tiket
     * @param tiket        Objek tiket yang divalidasi
     */
    public CheckIn(String idCheckIn, Date waktuCheckIn, Tiket tiket) {
        if (idCheckIn == null || idCheckIn.trim().isEmpty()) {
            throw new IllegalArgumentException("ID check-in tidak boleh kosong.");
        }
        this.idCheckIn = idCheckIn.trim();
        this.waktuCheckIn = (waktuCheckIn != null) ? waktuCheckIn : new Date();
        this.tiket = tiket;
        this.statusValidasi = "Belum Divalidasi";
    }

    /**
     * Konstruktor praktis dengan waktu check-in otomatis saat ini.
     *
     * @param idCheckIn ID unik sesi check-in
     * @param tiket     Objek tiket yang divalidasi
     */
    public CheckIn(String idCheckIn, Tiket tiket) {
        this(idCheckIn, new Date(), tiket);
    }

    /**
     * Memvalidasi apakah tiket memenuhi syarat masuk:
     * - Tiket harus berstatus "Terjual" (sudah dibayar/lunas).
     * - Tiket yang sudah berstatus "Terpakai" akan ditolak (pencegahan duplikasi tiket).
     * Jika valid, status tiket diubah menjadi "Terpakai" via tiket.tandaiTerpakai().
     *
     * @return true jika tiket valid dan berhasil check-in, false jika ditolak
     */
    public boolean validasiTiket() {
        if (tiket == null) {
            this.statusValidasi = "Gagal (Tiket tidak ditemukan)";
            return false;
        }

        if (Tiket.STATUS_TERPAKAI.equalsIgnoreCase(tiket.getStatusTiket())) {
            this.statusValidasi = "Gagal (Tiket sudah pernah digunakan sebelumnya)";
            return false;
        }

        if (Tiket.STATUS_TERJUAL.equalsIgnoreCase(tiket.getStatusTiket())) {
            tiket.tandaiTerpakai();
            this.statusValidasi = "Berhasil";
            if (this.waktuCheckIn == null) {
                this.waktuCheckIn = new Date();
            }
            return true;
        }

        this.statusValidasi = "Gagal (Status tiket '" + tiket.getStatusTiket() + "' tidak memenuhi syarat check-in)";
        return false;
    }

    /**
     * Mencatat dan mencetak log rekaman check-in ke konsol.
     */
    public void catat() {
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        String formattedDate = waktuCheckIn != null ? sdf.format(waktuCheckIn) : "-";
        System.out.println("[CHECK-IN LOG] ID: " + idCheckIn + " | Waktu: " + formattedDate +
                " | Tiket: " + (tiket != null ? tiket.getKodeTiket() : "null") +
                " | Hasil: " + statusValidasi);
    }

    // ===== Getter =====

    public String getIdCheckIn() {
        return idCheckIn;
    }

    public Date getWaktuCheckIn() {
        return waktuCheckIn;
    }

    public String getStatusValidasi() {
        return statusValidasi;
    }

    public Tiket getTiket() {
        return tiket;
    }
}
