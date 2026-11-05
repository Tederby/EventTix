package eventtix;

import java.util.Date;

/**
 * Mencatat check-in satu tiket. Relasi: CheckIn --> Tiket (divalidasi lewat).
 * Dibuat oleh Panitia lewat lakukanCheckIn(tiket).
 */
public class CheckIn {

    private String idCheckIn;
    private Date waktuCheckIn;
    private String statusValidasi;
    private Tiket tiket;

    public CheckIn(String idCheckIn, Tiket tiket) {
        this.idCheckIn = idCheckIn;
        this.tiket = tiket;
        this.waktuCheckIn = null;
        this.statusValidasi = "BELUM_DIVALIDASI";
    }

    /** Valid jika tiket ada dan belum pernah dipakai. */
    public boolean validasiTiket() {
        boolean valid = tiket != null
                && Tiket.STATUS_TERSEDIA.equals(tiket.getStatusTiket());
        statusValidasi = valid ? "VALID" : "DITOLAK";
        return valid;
    }

    /** Mencatat check-in: tiket valid ditandai terpakai + waktu dicatat. */
    public void catat() {
        if ("VALID".equals(statusValidasi)) {
            tiket.tandaiTerpakai();
            waktuCheckIn = new Date();
            System.out.println("[CHECK-IN BERHASIL] " + idCheckIn
                    + " | Tiket " + tiket.getKodeTiket() + " | " + waktuCheckIn);
        } else {
            System.out.println("[CHECK-IN DITOLAK] " + idCheckIn
                    + " | Tiket " + (tiket != null ? tiket.getKodeTiket() : "-")
                    + " (tidak valid / sudah terpakai)");
        }
    }

    public String getIdCheckIn() { return idCheckIn; }
    public Date getWaktuCheckIn() { return waktuCheckIn; }
    public String getStatusValidasi() { return statusValidasi; }
    public Tiket getTiket() { return tiket; }
}