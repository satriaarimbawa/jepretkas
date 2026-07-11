<?php
/**
 * User Model
 * 
 * Model untuk mengelola data pengguna.
 * Menyediakan method untuk registrasi, login, dan pencarian user.
 */
class User extends Model
{
    /** @var string Nama tabel */
    protected string $table = 'users';

    /**
     * Mencari user berdasarkan email
     * 
     * @param string $email Alamat email
     * @return object|false Data user atau false jika tidak ditemukan
     */
    public function findByEmail(string $email): object|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        return $this->queryOne($sql, [':email' => $email]);
    }

    /**
     * Mendaftarkan user baru
     * 
     * Membuat akun user baru dengan password yang di-hash,
     * kemudian membuat kategori default untuk user tersebut.
     * 
     * @param string $name     Nama lengkap user
     * @param string $email    Alamat email
     * @param string $password Password (plain text, akan di-hash)
     * @return string|false ID user yang baru dibuat, atau false jika gagal
     */
    public function register(string $name, string $email, string $password): string|false
    {
        // Hash password untuk keamanan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Buat record user baru
        $userId = $this->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $hashedPassword,
        ]);

        if ($userId) {
            // Buat kategori default untuk user baru
            $categoryModel = new Category();
            $categoryModel->createDefault((int) $userId);
        }

        return $userId;
    }

    /**
     * Verifikasi password
     * 
     * Membandingkan password plain text dengan hash yang tersimpan.
     * 
     * @param string $password Password plain text
     * @param string $hash     Hash password dari database
     * @return bool True jika password cocok
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
