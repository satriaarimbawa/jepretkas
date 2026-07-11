<?php
/**
 * Konfigurasi Database Aplikasi (SQLite)
 * 
 * Menggunakan SQLite sebagai penyimpan data lokal tanpa dependensi server MySQL.
 */

return [
    'driver' => 'sqlite',
    'path'   => ROOT_PATH . '/database/keuangan.sqlite',
];
