<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Aplikasi' ?> - Keuangan App</title>
    <!-- Fonts & Icons CDN -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <!-- PWA Manifest & Icons -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/css/logo-192.png">
</head>
<body>

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

    <!-- Modal Preview Struk -->
    <div class="modal-overlay" id="receipt-modal">
        <div class="modal-content">
            <button class="modal-close" id="modal-close">
                <span class="material-icons">close</span>
            </button>
            <img src="" id="modal-receipt-img" alt="Pratinjau Struk">
            <div class="modal-caption" id="modal-receipt-caption">Pratinjau Struk</div>
        </div>
    </div>

    <!-- Main Layout Container -->
    <div id="app-layout">
        
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-top">
                <div class="sidebar-logo">
                    <span class="material-icons logo-icon">account_balance_wallet</span>
                    <h1>Keuangan</h1>
                </div>
                
                <nav class="sidebar-nav">
                    <a href="<?= BASE_URL ?>/dashboard" class="nav-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>">
                        <span class="material-icons">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= BASE_URL ?>/transactions/create" class="nav-link <?= ($currentPage === 'transactions_create') ? 'active' : '' ?>">
                        <span class="material-icons">add_circle</span>
                        <span>Transaksi Baru</span>
                    </a>
                    <a href="<?= BASE_URL ?>/transactions" class="nav-link <?= ($currentPage === 'transactions') ? 'active' : '' ?>">
                        <span class="material-icons">receipt_long</span>
                        <span>Riwayat</span>
                    </a>
                    <a href="<?= BASE_URL ?>/analysis" class="nav-link <?= ($currentPage === 'analysis') ? 'active' : '' ?>">
                        <span class="material-icons">analytics</span>
                        <span>Analisis</span>
                    </a>
                    <a href="<?= BASE_URL ?>/categories" class="nav-link <?= ($currentPage === 'categories') ? 'active' : '' ?>">
                        <span class="material-icons">category</span>
                        <span>Kategori</span>
                    </a>
                    <a href="<?= BASE_URL ?>/accounts" class="nav-link <?= ($currentPage === 'accounts') ? 'active' : '' ?>">
                        <span class="material-icons">account_balance_wallet</span>
                        <span>Rekening</span>
                    </a>
                    <a href="<?= BASE_URL ?>/reports" class="nav-link <?= ($currentPage === 'reports') ? 'active' : '' ?>">
                        <span class="material-icons">assessment</span>
                        <span>Laporan</span>
                    </a>
                </nav>
            </div>

            <div class="sidebar-footer">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            <span class="user-email"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/auth/logout" class="nav-link text-danger" style="color: var(--danger);">
                    <span class="material-icons">logout</span>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <!-- Page Content -->
        <main class="main-content">
            <!-- Top bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h2><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?></h2>
                    <p>Selamat datang kembali, <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User' ?></p>
                </div>
                <button class="mobile-menu-toggle" id="mobile-toggle">
                    <span class="material-icons" style="font-size: 32px;">menu</span>
                </button>
            </div>

            <!-- Yield content from view -->
            <div class="page-body">
                <?= $content ?>
            </div>
        </main>
    </div>

    <!-- Mobile Bottom Navigation Bar (Visible only on Mobile Screen) -->
    <nav class="mobile-bottom-nav">
        <a href="<?= BASE_URL ?>/dashboard" class="bottom-nav-item <?= ($currentPage === 'dashboard') ? 'active' : '' ?>">
            <span class="material-icons">dashboard</span>
            <span>Beranda</span>
        </a>
        <a href="<?= BASE_URL ?>/transactions" class="bottom-nav-item <?= ($currentPage === 'transactions') ? 'active' : '' ?>">
            <span class="material-icons">receipt_long</span>
            <span>Riwayat</span>
        </a>
        <div class="bottom-nav-item-center-wrapper">
            <a href="<?= BASE_URL ?>/transactions/create" class="bottom-nav-item-center <?= ($currentPage === 'transactions_create') ? 'active' : '' ?>" title="Tambah Transaksi">
                <span class="material-icons">add</span>
            </a>
        </div>
        <a href="<?= BASE_URL ?>/analysis" class="bottom-nav-item <?= ($currentPage === 'analysis') ? 'active' : '' ?>">
            <span class="material-icons">analytics</span>
            <span>Analisis</span>
        </a>
        <a href="#" class="bottom-nav-item" id="mobile-menu-trigger">
            <span class="material-icons">menu</span>
            <span>Menu</span>
        </a>
    </nav>

    <!-- JS script -->
    <script src="<?= BASE_URL ?>/js/app.js"></script>
    <!-- Register PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js')
                    .then(reg => console.log('Service Worker registered: ', reg.scope))
                    .catch(err => console.log('Service Worker registration failed: ', err));
            });
        }
    </script>
</body>
</html>
