<?php
// web/includes/header.php
require_once __DIR__ . '/init.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$userRole = $currentUser->getRole();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | EventTix' : 'EventTix — Tiket Event & Seminar Kampus' ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="container">
    <div class="brand-wrapper">
      <a href="index.php" class="brand-logo">
        EventTix
        <span class="brand-tag">KAMPUS</span>
      </a>
    </div>

    <nav class="site-nav">
      <a href="index.php" class="<?= $currentPage === 'index.php' || $currentPage === 'event-detail.php' ? 'active' : '' ?>">Katalog event</a>
      
      <?php if ($userRole === 'Peserta'): ?>
        <a href="tiket.php" class="<?= $currentPage === 'tiket.php' ? 'active' : '' ?>">Tiket saya</a>
      <?php endif; ?>

      <?php if ($userRole === 'Panitia'): ?>
        <a href="checkin.php" class="<?= $currentPage === 'checkin.php' ? 'active' : '' ?>">Check-in pintu</a>
        <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Kelola event & laporan</a>
      <?php endif; ?>
    </nav>

    <div class="user-nav-actions">
      <span class="role-badge"><?= htmlspecialchars($currentUser->getNama()) ?> (<?= htmlspecialchars($userRole) ?>)</span>
      
      <!-- Opsi Ganti Peran Cepat untuk Kebutuhan Demonstrasi Pengujian -->
      <a href="login.php?switch=1" class="btn-keluar" title="Ganti mode pengguna">Ganti ke <?= $userRole === 'Peserta' ? 'Panitia' : 'Peserta' ?></a>
      
      <a href="logout.php" class="btn-keluar">Keluar</a>
    </div>
  </div>
</header>

<main class="main-content" style="flex: 1; padding: 2rem 0;">
  <div class="container">

