package eventtix;

import java.util.Arrays;
import java.util.List;

public class TiketVIP extends Tiket {

    private double biayaLayananVIP;
    private String nomorKursi;

    public TiketVIP(String kodeTiket, double hargaDasar, double biayaLayananVIP, String nomorKursi) {
        super(kodeTiket, hargaDasar);
        this.biayaLayananVIP = biayaLayananVIP;
        this.nomorKursi = nomorKursi;
    }

    @Override
    public double hitungHarga() {
        return getHargaDasar() + biayaLayananVIP;
    }

    @Override
    public List<String> getFasilitas() {
        return Arrays.asList(
                "Akses masuk event",
                "Kursi VIP nomor " + nomorKursi,
                "Jalur masuk khusus",
                "Welcome drink & merchandise");
    }

    public double getBiayaLayananVIP() {
        return biayaLayananVIP;
    }

    public String getNomorKursi() {
        return nomorKursi;
    }
}