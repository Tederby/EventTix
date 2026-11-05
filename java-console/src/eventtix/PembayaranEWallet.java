package eventtix;

import java.util.Arrays;
import java.util.List;

/**
 * Pembayaran melalui dompet digital (e-wallet).
 * Biaya admin berbasis persentase, berbeda dari transfer (flat).
 */
public class PembayaranEWallet extends Pembayaran {

    private static final List<String> PROVIDER_DIDUKUNG =
            Arrays.asList("GOPAY", "OVO", "DANA", "SHOPEEPAY", "LINKAJA");
    private static final double PERSEN_ADMIN = 0.01;   // 1%
    private static final double ADMIN_MINIMAL = 1000;  // minimal Rp1.000

    private String providerWallet;
    private String noAkun;

    public PembayaranEWallet(String idPembayaran, double jumlah,
                             String providerWallet, String noAkun) {
        super(idPembayaran, jumlah);
        if (providerWallet == null
                || !PROVIDER_DIDUKUNG.contains(providerWallet.trim().toUpperCase())) {
            throw new IllegalArgumentException(
                    "Provider tidak didukung. Pilih: GoPay, OVO, DANA, ShopeePay, LinkAja.");
        }
        if (noAkun == null || !noAkun.matches("(08|62)\\d{8,12}")) {
            throw new IllegalArgumentException(
                    "Nomor akun e-wallet harus nomor HP (awalan 08 atau 62).");
        }
        this.providerWallet = providerWallet.trim();
        this.noAkun = noAkun;
    }

    /** Biaya admin e-wallet: 1% dari jumlah, minimal Rp1.000. */
    @Override
    public double hitungBiayaAdmin() {
        return Math.max(ADMIN_MINIMAL, getJumlah() * PERSEN_ADMIN);
    }

    /** Simulasi debit saldo e-wallet: langsung BERHASIL jika belum pernah dibayar. */
    @Override
    public boolean prosesPembayaran() {
        if (STATUS_BERHASIL.equals(getStatusBayar())) {
            return false; // tidak boleh dibayar dua kali
        }
        tandaiBerhasil();
        return true;
    }

    public String getProviderWallet() {
        return providerWallet;
    }

    public String getNoAkun() {
        return noAkun;
    }
}
