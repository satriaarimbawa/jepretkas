-- ============================================
-- Schema Database: Aplikasi Keuangan
-- ============================================
-- Jalankan file ini di MySQL/MariaDB untuk
-- membuat database dan tabel yang diperlukan.
-- ============================================

-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS keuangan_db 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE keuangan_db;

-- ============================================
-- Tabel: users
-- Menyimpan data pengguna aplikasi
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel: categories
-- Menyimpan kategori transaksi per pengguna
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') DEFAULT 'expense',
    color VARCHAR(7) DEFAULT '#6366f1',
    icon VARCHAR(50) DEFAULT 'folder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel: transactions
-- Menyimpan data transaksi keuangan
-- ============================================
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    receipt_photo VARCHAR(255),
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Index tambahan untuk performa query
-- ============================================
CREATE INDEX idx_transactions_user_id ON transactions(user_id);
CREATE INDEX idx_transactions_type ON transactions(type);
CREATE INDEX idx_transactions_date ON transactions(transaction_date);
CREATE INDEX idx_transactions_category ON transactions(category_id);
CREATE INDEX idx_categories_user_id ON categories(user_id);
CREATE INDEX idx_categories_type ON categories(type);

-- ============================================
-- Data Default: Kategori
-- Kategori bawaan untuk user_id = 1 (placeholder)
-- Pada aplikasi nyata, kategori dibuat saat user register
-- ============================================
INSERT INTO categories (user_id, name, type, color, icon) VALUES
    (1, 'Operasional', 'expense', '#ef4444', 'briefcase'),
    (1, 'Konsumsi', 'expense', '#f97316', 'utensils'),
    (1, 'Transportasi', 'expense', '#eab308', 'car'),
    (1, 'Gaji', 'income', '#22c55e', 'wallet'),
    (1, 'Lainnya', 'expense', '#6366f1', 'folder');
