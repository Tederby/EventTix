<?php
// web/includes/init.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../classes/DataManager.php';

$dataManager = DataManager::getInstance();

// Default user demo jika belum login: Peserta (Muhammad Isya Asyhari)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 'USR-01';
    $_SESSION['user_role'] = 'Peserta';
}

$currentUser = $dataManager->getUserById($_SESSION['user_id']);
if (!$currentUser) {
    $currentUser = $dataManager->getUserById('USR-01');
    $_SESSION['user_id'] = 'USR-01';
    $_SESSION['user_role'] = 'Peserta';
}

function rupiah(float $angka): string {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function tanggalIndo(string $dateString): string {
    $timestamp = strtotime($dateString);
    if (!$timestamp) return $dateString;
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w', $timestamp)];
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ][(int)date('n', $timestamp)];
    return $hari . ', ' . date('j', $timestamp) . ' ' . $bulan . ' ' . date('Y', $timestamp) . ' | ' . date('H.i', $timestamp) . ' WIB';
}

function tanggalRingkas(string $dateString): string {
    $timestamp = strtotime($dateString);
    if (!$timestamp) return $dateString;
    $bulan = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
    ][(int)date('n', $timestamp)];
    return date('j', $timestamp) . ' ' . $bulan . ' ' . date('Y', $timestamp);
}

