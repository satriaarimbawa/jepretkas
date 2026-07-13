<?php
/**
 * Migration Script: Tambah Kolom type ke tabel accounts
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== MEMULAI MIGRASI TIPE REKENING ===\n";
    
    // Cek apakah kolom type sudah ada di tabel accounts
    $checkAcc = $db->query("PRAGMA table_info(accounts)");
    $accColumns = $checkAcc->fetchAll(PDO::FETCH_ASSOC);
    $typeExists = false;
    
    foreach ($accColumns as $col) {
        if ($col['name'] === 'type') {
            $typeExists = true;
            break;
        }
    }
    
    if (!$typeExists) {
        $db->exec("ALTER TABLE accounts ADD COLUMN type TEXT CHECK(type IN ('debit', 'credit')) DEFAULT 'debit'");
        echo "Sukses: Kolom 'type' berhasil ditambahkan ke tabel 'accounts'.\n";
    } else {
        echo "Info: Kolom 'type' sudah terdefinisi di tabel 'accounts'.\n";
    }
    
    echo "=== MIGRASI SELESAI DENGAN SUKSES ===\n";
} catch (Exception $e) {
    echo "Gagal melakukan migrasi: " . $e->getMessage() . "\n";
}
