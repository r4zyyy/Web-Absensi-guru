<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$nip = $_SESSION['nip'];
$role = $_SESSION['role'];

// Handle absensi masuk
if (isset($_POST['absen_masuk'])) {
    $tanggal = date('Y-m-d');
    $jam_masuk = date('H:i:s');
    
    $stmt = $pdo->prepare("SELECT * FROM absensi WHERE nip = ? AND tanggal = ?");
    $stmt->execute([$nip, $tanggal]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO absensi (user_id, nip, tanggal, jam_masuk) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $nip, $tanggal, $jam_masuk]);
        $success = "Absen masuk berhasil pada " . date('H:i');
    } else {
        $warning = "Anda sudah absen masuk hari ini!";
    }
}

// Handle absensi keluar
if (isset($_POST['absen_keluar'])) {
    $tanggal = date('Y-m-d');
    $jam_keluar = date('H:i:s');
    
    $stmt = $pdo->prepare("UPDATE absensi SET jam_keluar = ? WHERE nip = ? AND tanggal = ? AND jam_keluar IS NULL");
    $result = $stmt->execute([$jam_keluar, $nip, $tanggal]);
    
    if ($result) {
        $success = "Absen keluar berhasil pada " . date('H:i');
    } else {
        $warning = "Belum absen masuk atau sudah absen keluar!";
    }
}

// Cek status absensi hari ini
$tanggal = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM absensi WHERE nip = ? AND tanggal = ?");
$stmt->execute([$nip, $tanggal]);
$absensi_hari_ini = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Absensi Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-graduation-cap"></i> Absensi Guru</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <strong><?php echo $_SESSION['nama']; ?></strong> (<?php echo $_SESSION['nip']; ?>)
                </span>
                <?php if ($role == 'admin'): ?>
                    <a class="nav-link" href="laporan.php">Laporan</a>
                <?php endif; ?>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($warning)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php echo $warning; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock"></i> Absensi Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-6">
                                <?php if (!$absensi_hari_ini || !$absensi_hari_ini['jam_masuk']): ?>
                                    <form method="POST" class="p-3 border rounded">
                                        <i class="fas fa-clock fa-3x text-success mb-3"></i>
                                        <h4>Masuk</h4>
                                        <button type="submit" name="absen_masuk" class="btn btn-success btn-lg">
                                            <i class="fas fa-play"></i> Absen Masuk
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="p-3 border rounded bg-success bg-opacity-10">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <h4>✅ Sudah Absen</h4>
                                        <p class="mb-0"><strong><?php echo $absensi_hari_ini['jam_masuk']; ?></strong></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <?php if ($absensi_hari_ini && $absensi_hari_ini['jam_masuk'] && !$absensi_hari_ini['jam_keluar']): ?>
                                    <form method="POST" class="p-3 border rounded">
                                        <i class="fas fa-clock fa-3x text-danger mb-3"></i>
                                        <h4>Keluar</h4>
                                        <button type="submit" name="absen_keluar" class="btn btn-danger btn-lg">
                                            <i class="fas fa-stop"></i> Absen Keluar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="p-3 border rounded bg-secondary bg-opacity-10">
                                        <i class="fas fa-lock fa-3x text-secondary mb-3"></i>
                                        <h4>⏰ Belum Absen Masuk</h4>
                                        <p class="mb-0 text-muted">Absen keluar setelah absen masuk</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-calendar"></i> Statistik Bulan Ini</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $bulan_ini = date('Y-m');
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total, 
                            SUM(CASE WHEN jam_masuk <= '07:30:00' THEN 1 ELSE 0 END) as tepat_waktu,
                            SUM(CASE WHEN jam_masuk > '07:30:00' THEN 1 ELSE 0 END) as telat
                            FROM absensi WHERE nip = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ?");
                        $stmt->execute([$nip, $bulan_ini]);
                        $stats = $stmt->fetch();
                        ?>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h5"><?php echo $stats['total']; ?></div>
                                    <small>Hari Hadir</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h5 text-success"><?php echo $stats['tepat_waktu']; ?></div>
                                    <small>Tepat Waktu</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="h5 text-warning"><?php echo $stats['telat']; ?></div>
                                <small>Telat</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-history"></i> Riwayat Absensi 7 Hari Terakhir</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM absensi WHERE nip = ? 
                                                         ORDER BY tanggal DESC LIMIT 7");
                                    $stmt->execute([$nip]);
                                    while ($row = $stmt->fetch()):
                                    ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                        <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                                        <td><?php echo $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-'; ?></td>
                                        <td>
                                            <?php 
                                            $status = 'hadir';
                                            if ($row['jam_masuk'] > '07:30:00') $status = 'telat';
                                            echo '<span class="badge bg-' . ($status == 'telat' ? 'warning' : 'success') . '">' . ucfirst($status) . '</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>