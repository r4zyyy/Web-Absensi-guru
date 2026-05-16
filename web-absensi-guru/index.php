<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_POST) {
    $nip = $_POST['nip'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE nip = ?");
    $stmt->execute([$nip]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nip'] = $user['nip'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'NIP atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .login-container { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .login-card { box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: none; }
    </style>
</head>
<body>
    <div class="login-container d-flex align-items-center justify-content-center">
        <div class="card login-card" style="width: 400px;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                    <h3>Sistem Absensi Guru</h3>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control" name="nip" required 
                               value="<?php echo isset($_POST['nip']) ? $_POST['nip'] : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Demo: NIP: 196701121994031001 / 197802251998021002<br>
                        Password: password
                    </small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>