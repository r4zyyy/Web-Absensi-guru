<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query("SELECT u.nama, u.nip, COUNT(a.id) as total_hadir,
    SUM(CASE WHEN a.jam_masuk <= '07:30:00' THEN 1 ELSE 0 END) as tepat_waktu
    FROM users u 
    LEFT JOIN absensi a ON u.nip = a.nip AND DATE_FORMAT(a.tanggal, '%Y-%m') = '" . date('Y-m') . "'
    WHERE u.role = 'guru'
    GROUP BY u.id, u.nip, u.nama
    ORDER BY total_hadir DESC");
$statistik = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-graduation-cap"></i> Absensi Guru</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3"><?php echo $_SESSION['nama']; ?></span>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
           