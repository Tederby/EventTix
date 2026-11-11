package eventtix;

/**
 * Pembayaran melalui transfer bank.
 * Alur: verifikasiBukti(bukti) -> status BUKTI_TERVERIFIKASI -> prosesPembayaran() -> BERHASIL.
 */
public class PembayaranTransfer extends Pembayaran {

    private static final double BIAYA_ADMIN_TRANSFER = 2500;

    private String namaBank;
    private String noRekening;

    public PembayaranTransfer(String idPembayaran, double jumlah,
                              String namaBank, String noRekening) {
        super(idPembayaran, jumlah);
        if (namaBank == null || namaBank.trim().isEmpty()) {
            throw new IllegalArgumentException("Nama bank tidak boleh kosong.");
        }
        if (noRekening == null || !noRekening.matches("\\d{8,16}")) {
            throw new IllegalArgumentException("Nomor rekening harus 8-16 digit angka.");
        }
        this.namaBank = namaBank;
        this.noRekening = noRekening;
    }

    /** Biaya admin transfer: flat Rp2.500. */
    @Override
    public double hitungBiayaAdmin() {
        return BIAYA_ADMIN_TRANSFER;
    }

    /**
     * Pembayaran hanya bisa diproses setelah bukti transfer terverifikasi.
     * Jika belum, status menjadi GAGAL dan return false.
     */
    @Override
    public boolean prosesPembayaran() {
        if (STATUS_BERHASIL.equals(getStatusBayar())) {
            return false; // sudah dibayar, tidak boleh diproses dua kali
        }
        if (!STATUS_BUKTI_TERVERIFIKASI.equals(getStatusBayar())) {
            setStatusBayar(STATUS_GAGAL);
            return false;
        }
        tandaiBerhasil();
        return true;
    }

    /**
     * Verifikasi bukti transfer (simulasi): nama file bukti harus berekstensi
     * jpg/jpeg/png/pdf. Jika valid, status menjadi BUKTI_TERVERIFIKASI.
     */
    public boolean verifikasiBukti(String bukti) {
        if (STATUS_BERHASIL.equals(getStatusBayar())) {
            return false;
        }
        boolean valid = bukti != null
                && bukti.trim().toLowerCase().matches(".+\\.(jpg|jpeg|png|pdf)");
        if (valid) {
            setStatusBayar(STATUS_BUKTI_TERVERIFIKASI);
        }
        return valid;
    }

    public String getNamaBank() {
        return namaBank;
    }

    public String getNoRekening() {
        return noRekening;
    }
}
