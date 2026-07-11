<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Keuangan App</title>
    <!-- Fonts & Icons CDN -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/css/logo-192.png">
</head>
<body class="auth-wrapper">

    <!-- Toast Notification Container -->
    <div class="toast-container">
        <?php if ($flash = $this->getFlash()): ?>
            <div class="toast <?= htmlspecialchars($flash['type']) ?>">
                <span class="material-icons">
                    <?= $flash['type'] === 'success' ? 'check_circle' : ($flash['type'] === 'error' ? 'cancel' : 'warning') ?>
                </span>
                <div class="toast-message"><?= htmlspecialchars($flash['message']) ?></div>
                <button class="toast-close">
                    <span class="material-icons">close</span>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Register Card -->
    <div class="glass-card auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <span class="material-icons">person_add</span>
            </div>
            <h2 class="auth-title">Daftar Akun Baru</h2>
            <p class="auth-subtitle">Buat akun untuk melacak arus keuangan Anda</p>
        </div>

        <form action="<?= BASE_URL ?>/auth/doRegister" method="POST">
            <div class="form-group">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Nama Anda" required value="<?= isset($_SESSION['old']['name']) ? htmlspecialchars($_SESSION['old']['name']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required value="<?= isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                <span class="material-icons">how_to_reg</span>
                <span>Daftar Sekarang</span>
            </button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="<?= BASE_URL ?>/auth/login">Masuk di sini</a>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>
<?php 
// Bersihkan old input session setelah digunakan
if(isset($_SESSION['old'])) unset($_SESSION['old']); 
?>
