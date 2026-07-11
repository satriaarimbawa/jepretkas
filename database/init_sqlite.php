<?php
/**
 * Inisialisasi Database SQLite
 * 
 * Script ini digunakan untuk membuat berkas database SQLite dan
 * tabel-tabel yang diperlukan secara otomatis.
 */

// Definisikan ROOT_PATH agar file konfigurasi dapat dimuat dengan benar
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Muat koneksi database singleton kita
require_once ROOT_PATH . '/core/Database.php';

echo "Memulai inisialisasi database SQLite...\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // 1. Buat Tabel Users
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
    echo "Tabel 'users' berhasil dibuat.\n";

    // 2. Buat Tabel Categories
    $conn->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('income', 'expense')),
            color TEXT DEFAULT '#6366f1',
            icon TEXT DEFAULT 'folder',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");
    echo "Tabel 'categories' berhasil dibuat.\n";

    // 3. Buat Tabel Transactions
    $conn->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            category_id INTEGER,
            type TEXT NOT NULL CHECK(type IN ('income', 'expense')),
            amount REAL NOT NULL,
            description TEXT,
            receipt_photo TEXT,
            transaction_date TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        );
    ");
    echo "Tabel 'transactions' berhasil dibuat.\n";

    echo "Inisialisasi database SQLite SELESAI dengan sukses!\n";
    echo "Berkas database disimpan di: " . ROOT_PATH . "/database/keuangan.sqlite\n";

} catch (Exception $e) {
    die("Error inisialisasi database: " . $e->getMessage() . "\n");
}
