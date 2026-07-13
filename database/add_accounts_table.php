<?php
/**
 * Migration Script: Tambah Tabel accounts dan Hubungkan ke transactions
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== MEMULAI MIGRASI REKENING DATABASE ===\n";
    
    // 1. Buat Tabel accounts
    $db->exec("
        CREATE TABLE IF NOT EXISTS accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            color TEXT DEFAULT '#6366f1',
            icon TEXT DEFAULT 'account_balance_wallet',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");
    echo "Sukses: Tabel 'accounts' berhasil dibuat atau sudah ada.\n";
    
    // 2. Cek apakah kolom account_id sudah ada di tabel transactions
    $checkTx = $db->query("PRAGMA table_info(transactions)");
    $txColumns = $checkTx->fetchAll(PDO::FETCH_ASSOC);
    $accountIdExists = false;
    
    foreach ($txColumns as $col) {
        if ($col['name'] === 'account_id') {
            $accountIdExists = true;
            break;
        }
    }
    
    if (!$accountIdExists) {
        // Tambahkan kolom account_id
        $db->exec("ALTER TABLE transactions ADD COLUMN account_id INTEGER REFERENCES accounts(id) ON DELETE SET NULL");
        echo "Sukses: Kolom 'account_id' berhasil ditambahkan ke tabel 'transactions'.\n";
    } else {
        echo "Info: Kolom 'account_id' sudah terdefinisi di tabel 'transactions'.\n";
    }
    
    // 3. Tambahkan rekening default 'Dompet Utama' untuk setiap user dan kaitkan transaksi yang ada ke rekening tersebut
    $usersStmt = $db->query("SELECT id FROM users");
    $users = $usersStmt->fetchAll(PDO::FETCH_OBJ);
    
    foreach ($users as $user) {
        $userId = $user->id;
        
        // Cek apakah user sudah punya rekening
        $checkAcc = $db->prepare("SELECT id FROM accounts WHERE user_id = :user_id LIMIT 1");
        $checkAcc->execute([':user_id' => $userId]);
        $existingAcc = $checkAcc->fetch();
        
        if (!$existingAcc) {
            // Buat rekening default
            $ins = $db->prepare("INSERT INTO accounts (user_id, name, color, icon) VALUES (:user_id, :name, '#6366f1', 'account_balance_wallet')");
            $ins->execute([':user_id' => $userId, ':name' => 'Dompet Utama']);
            $defaultAccountId = $db->lastInsertId();
            echo "Sukses: Menambahkan rekening 'Dompet Utama' (ID: $defaultAccountId) untuk User ID $userId.\n";
        } else {
            $defaultAccountId = $existingAcc->id;
            echo "Info: User ID $userId sudah memiliki rekening dengan ID $defaultAccountId.\n";
        }
        
        // Update transaksi lama yang memiliki account_id null
        $upd = $db->prepare("UPDATE transactions SET account_id = :account_id WHERE user_id = :user_id AND account_id IS NULL");
        $upd->execute([
            ':account_id' => $defaultAccountId,
            ':user_id' => $userId
        ]);
        $updatedCount = $upd->rowCount();
        if ($updatedCount > 0) {
            echo "Sukses: Mengupdate $updatedCount transaksi lama milik User ID $userId ke rekening default.\n";
        }
    }
    
    echo "=== MIGRASI SELESAI DENGAN SUKSES ===\n";
} catch (Exception $e) {
    echo "Gagal melakukan migrasi: " . $e->getMessage() . "\n";
}
