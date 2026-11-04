package eventtix;

/**
 * Kelas Abstrak User merepresentasikan entitas pengguna umum dalam sistem EventTix.
 * Menerapkan pilar Abstraction dan Encapsulation:
 * - Abstraction: Kelas ini tidak dapat diinstansiasi secara langsung dan mendefinisikan
 *   metode abstrak getRole() yang wajib diimplementasikan oleh setiap subclass konkret.
 * - Encapsulation: Seluruh atribut dideklarasikan private dan diakses/dimodifikasi
 *   melalui metode pengakses (getter/setter) serta operasi bisnis yang tervalidasi.
 *
 * @author Kelompok 6 - Modul 2 (user-event)
 */
public abstract class User {
    // Atribut privat (Encapsulation)
    private String idUser;
    private String nama;
    private String email;
    private String password;

    /**
     * Konstruktor berparameter untuk inisialisasi entitas User.
     *
     * @param idUser   Identitas unik pengguna
     * @param nama     Nama lengkap pengguna
     * @param email    Alamat surel pengguna untuk autentikasi
     * @param password Kata sandi akun
     */
    public User(String idUser, String nama, String email, String password) {
        this.idUser = idUser;
        this.nama = nama;
        this.email = email;
        this.password = password;
    }

    /**
     * Memvalidasi kecocokan email dan password untuk proses autentikasi login.
     *
     * @param inputEmail    Email yang dimasukkan saat login
     * @param inputPassword Password yang dimasukkan saat login
     * @return true jika email dan password cocok, false jika tidak
     */
    public boolean login(String inputEmail, String inputPassword) {
        if (inputEmail != null && inputPassword != null) {
            return this.email.equalsIgnoreCase(inputEmail.trim()) && this.password.equals(inputPassword);
        }
        return false;
    }

    /**
     * Menandai berakhirnya sesi login pengguna.
     */
    public void logout() {
        System.out.println("[LOGOUT] Pengguna '" + this.nama + "' (" + getRole() + ") berhasil keluar dari sesi sistem.");
    }

    /**
     * Mengubah kata sandi pengguna dengan validasi kata sandi lama terlebih dahulu.
     *
     * @param passwordLama Kata sandi saat ini
     * @param passwordBaru Kata sandi baru yang diinginkan
     * @return true jika password lama valid dan perubahan berhasil, false jika sebaliknya
     */
    public boolean ubahPassword(String passwordLama, String passwordBaru) {
        if (passwordLama == null || passwordBaru == null || passwordBaru.trim().isEmpty()) {
            System.out.println("[GAGAL] Password baru tidak boleh kosong.");
            return false;
        }

        if (this.password.equals(passwordLama)) {
            this.password = passwordBaru;
            System.out.println("[SUKSES] Password untuk pengguna '" + this.nama + "' berhasil diperbarui.");
            return true;
        } else {
            System.out.println("[GAGAL] Password lama yang dimasukkan tidak sesuai.");
            return false;
        }
    }

    /**
     * Metode abstrak untuk mendapatkan peran spesifik pengguna (Polymorphism).
     *
     * @return Nama peran pengguna (misal: "Panitia", "Peserta")
     */
    public abstract String getRole();

    // --- Getter dan Setter (Encapsulation) ---

    public String getIdUser() {
        return idUser;
    }

    public void setIdUser(String idUser) {
        this.idUser = idUser;
    }

    public String getNama() {
        return nama;
    }

    public void setNama(String nama) {
        this.nama = nama;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    /**
     * Getter untuk password dengan proteksi akses (package-private/internal jika diperlukan).
     *
     * @return String password
     */
    protected String getPassword() {
        return password;
    }

    @Override
    public String toString() {
        return "[" + getRole() + "] ID: " + idUser + " | Nama: " + nama + " | Email: " + email;
    }
}

