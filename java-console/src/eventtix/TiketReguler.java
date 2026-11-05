package eventtix;

import java.util.Arrays;
import java.util.List;

public class TiketReguler extends Tiket {

    public TiketReguler(String kodeTiket, double hargaDasar) {
        super(kodeTiket, hargaDasar);
    }

    @Override
    public double hitungHarga() {
        return getHargaDasar(); // reguler: tanpa biaya tambahan
    }

    @Override
    public List<String> getFasilitas() {
        return Arrays.asList("Akses masuk event", "Tempat duduk/area umum");
    }
}