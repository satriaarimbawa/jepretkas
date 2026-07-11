<?php
/**
 * Migration Script: Tambah Kolom is_fixed ke tabel categories
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== MEMULAI MIGRASI DATABASE ===\n";
    
    // 1. Cek apakah kolom is_fixed sudah ada
    $check = $db->query("PRAGMA table_info(categories)");
    $columns = $check->fetchAll(PDO::FETCH_ASSOC);
    $columnExists = false;
    
    foreach ($columns as $col) {
        if ($col['name'] === 'is_fixed') {
            $columnExists = true;
            break;
        }
    }
    
    if (!$columnExists) {
        // Tambahkan kolom is_fixed
        $db->exec("ALTER TABLE categories ADD COLUMN is_fixed INTEGER DEFAULT 0");
        echo "Sukses: Kolom 'is_fixed' berhasil ditambahkan ke tabel 'categories'.\n";
    } else {
        echo "Info: Kolom 'is_fixed' sudah terdefinisi di database.\n";
    }
    
    // 2. Set default kategori 'Operasional' yang sudah ada menjadi Tetap (is_fixed = 1)
    $stmt = $db->prepare("UPDATE categories SET is_fixed = 1 WHERE name = :name");
    $stmt->execute([':name' => 'Operasional']);
    echo "Sukses: Kategori 'Operasional' lama disetel menjadi Tetap (Wajib).\n";
    
    // 3. Tambahkan kategori default baru (Sewa Rumah / Kost dan Tagihan) ke semua user yang sudah terdaftar
    $usersStmt = $db->query("SELECT id FROM users");
    $users = $usersStmt->fetchAll(PDO::FETCH_OBJ);
    
    foreach ($users as $user) {
        $userId = $user->id;
        
        // Cek apakah Sewa Rumah / Kost sudah ada
        $checkSewa = $db->prepare("SELECT id FROM categories WHERE user_id = :user_id AND name = :name");
        $checkSewa->execute([':user_id' => $userId, ':name' => 'Sewa Rumah / Kost']);
        if (!$checkSewa->fetch()) {
            $ins = $db->prepare("INSERT INTO categories (user_id, name, type, color, icon, is_fixed) VALUES (:user_id, :name, 'expense', '#a855f7', 'home', 1)");
            $ins->execute([':user_id' => $userId, ':name' => 'Sewa Rumah / Kost']);
            echo "Sukses: Menambahkan kategori bawaan 'Sewa Rumah / Kost' untuk User ID $userId.\n";
        }
        
        // Cek apakah Tagihan (Listrik/Air) sudah ada
        $checkTagihan = $db->prepare("SELECT id FROM categories WHERE user_id = :user_id AND name = :name");
        $checkTagihan->execute([':user_id' => $userId, ':name' => 'Tagihan (Listrik/Air)']);
        if (!$checkTagihan->fetch()) {
            $ins = $db->prepare("INSERT INTO categories (user_id, name, type, color, icon, is_fixed) VALUES (:user_id, :name, 'expense', '#06b6d4', 'water', 1)");
            $ins->execute([':user_id' => $userId, ':name' => 'Tagihan (Listrik/Air)']);
            echo "Sukses: Menambahkan kategori bawaan 'Tagihan (Listrik/Air)' untuk User ID $userId.\n";
        }
    }
    
    echo "=== MIGRASI SELESAI DENGAN SUKSES ===\n";
} catch (Exception $e) {
    echo "Gagal melakukan migrasi: " . $e->getMessage() . "\n";
}
