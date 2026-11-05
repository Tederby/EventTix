<?php
// web/login.php
require_once __DIR__ . '/includes/init.php';

// Fitur quick switch mode untuk kemudahan pengujian antarmuka
if (isset($_GET['switch'])) {
    if ($_SESSION['user_role'] === 'Peserta') {
        $_SESSION['user_id'] = 'ADM-01';
        $_SESSION['user_role'] = 'Panitia';
        header('Location: checkin.php');
        exit;
    } else {
        $_SESSION['user_id'] = 'USR-01';
        $_SESSION['user_role'] = 'Peserta';
        header('Location: index.php');
        exit;
    }
}

$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $user = $dataManager->getUserByEmail($email);
    if ($user && $user->login($email, $password)) {
        $_SESSION['user_id'] = $user->getIdUser();
        $_SESSION['user_role'] = $user->getRole();
        if ($user->getRole() === 'Panitia') {
            header('Location: checkin.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $pesanError = 'Kombinasi email dan kata sandi tidak cocok. Masukkan data akun yang terdaftar.';
    }
}

$pageTitle = 'Masuk Pengguna';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 440px; margin: 2rem auto;">
  <div style="background-color: var(--color-putih); border: var(--border-tegas); padding: 2rem; border-radius: var(--radius-karcis);">
    <h1 style="font-size: var(--text-28); margin-bottom: 0.5rem;">Masuk akun</h1>
    <p style="color: var(--color-biru-abu); font-size: var(--text-14); margin-bottom: 1.5rem;">Gunakan akun mahasiswa atau panitia untuk melanjutkan.</p>

    <?php if ($pesanError): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($pesanError) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label class="form-label" for="email">Alamat email kampus</label>
        <input type="email" id="email" name="email" class="form-input" required placeholder="nama@mahasiswa.ac.id" value="isya@mahasiswa.ac.id">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Kata sandi</label>
        <input type="password" id="password" name="password" class="form-input" required value="password123">
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Masuk sekarang</button>
    </form>

    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--color-garis-halus); font-size: var(--text-14);">
      <p style="font-weight: 600; margin-bottom: 0.5rem;">Akun uji coba cepat:</p>
      <div style="display: flex; gap: 0.5rem; flex-direction: column;">
        <a href="login.php?switch=1" class="btn btn-secondary btn-sm" style="text-align: left;">
          Beralih langsung ke akun <?= $currentUser->getRole() === 'Peserta' ? 'Panitia (Koordinator Pintu)' : 'Peserta (Mahasiswa)' ?>
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

