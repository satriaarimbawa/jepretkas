<?php
/**
 * Front Controller / Entry Point
 * 
 * File ini adalah titik masuk utama aplikasi.
 * Menginisialisasi session, mendefinisikan konstanta,
 * mengatur autoloader, dan menjalankan router.
 */

// Jika dijalankan via PHP Built-in Server, tangani file statis secara langsung
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Mulai session
session_start();

// --- Definisi Konstanta ---

// Path root proyek (satu level di atas /public)
define('ROOT_PATH', dirname(__DIR__));

// Muat konfigurasi database
require ROOT_PATH . '/config/database.php';

// Auto-detect BASE_URL dari $_SERVER (mendukung clean URL tanpa /index.php)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    // Hapus /public/index.php atau /index.php dari script path
    $basePath = str_replace(['/public/index.php', '/index.php'], '', $scriptName);
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}

// Hubungkan REQUEST_URI ke $_GET['url'] jika belum diset (misal pada server internal PHP)
if (!isset($_GET['url'])) {
    $urlPath = ltrim($uri, '/');
    if (strpos($urlPath, 'index.php') === 0) {
        $urlPath = substr($urlPath, 9); // Hapus 'index.php' dari depan
    }
    $_GET['url'] = ltrim($urlPath, '/');
}

// Path untuk upload bukti transaksi
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', ROOT_PATH . '/public/uploads/receipts');
}

// --- Autoloader ---

/**
 * Autoload kelas dari direktori core/, app/models/, dan app/controllers/
 * 
 * Mencari file kelas secara berurutan di beberapa direktori.
 * Nama file harus sama dengan nama kelas (case-sensitive).
 */
spl_autoload_register(function (string $className): void {
    // Direktori yang akan dicari
    $directories = [
        ROOT_PATH . '/core/',
        ROOT_PATH . '/app/models/',
        ROOT_PATH . '/app/controllers/',
    ];

    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// --- Jalankan Aplikasi ---

// Buat direktori upload jika belum ada
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Inisialisasi dan jalankan router
$app = new App();
