<?php
/**
 * Database Singleton
 * 
 * Kelas wrapper PDO menggunakan pola Singleton
 * untuk memastikan hanya satu koneksi database yang aktif.
 */
class Database
{
    /** @var Database|null Instance singleton */
    private static ?Database $instance = null;

    /** @var PDO Koneksi PDO */
    private PDO $pdo;

    /**
     * Constructor (private untuk Singleton)
     * 
     * Membaca konfigurasi dari config/database.php
     * dan membuat koneksi PDO dengan pengaturan yang sesuai.
     */
    private function __construct()
    {
        // Baca konfigurasi database
        $config = require ROOT_PATH . '/config/database.php';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,       // Mode error: exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,               // Default fetch: object
            PDO::ATTR_EMULATE_PREPARES   => false,                        // Gunakan prepared statement asli
        ];

        try {
            if (isset($config['driver']) && $config['driver'] === 'sqlite') {
                $dsn = "sqlite:" . $config['path'];
                // Buat folder database jika belum ada
                $dbDir = dirname($config['path']);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                $this->pdo = new PDO($dsn, null, null, $options);
                // Aktifkan dukungan foreign keys untuk SQLite
                $this->pdo->exec("PRAGMA foreign_keys = ON;");
            } else {
                $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
                $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
            }
        } catch (PDOException $e) {
            // Tampilkan pesan error yang aman
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    /**
     * Mencegah cloning instance
     */
    private function __clone() {}

    /**
     * Mencegah unserialization instance
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Mendapatkan instance singleton Database
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Mendapatkan koneksi PDO
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
