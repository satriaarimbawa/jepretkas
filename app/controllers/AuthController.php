<?php

/**
 * AuthController
 * 
 * Menangani autentikasi pengguna: login, register, dan logout.
 */
class AuthController extends Controller
{
    private $userModel;
    private $categoryModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->categoryModel = new Category();
    }

    /**
     * Menampilkan halaman login
     * GET /auth/login
     */
    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
            return;
        }

        $this->viewOnly('auth/login', [
            'pageTitle' => 'Login'
        ]);
    }

    /**
     * Proses login
     * POST /auth/login
     */
    public function doLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/login');
            return;
        }

        // Ambil dan validasi input
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi field wajib
        if (empty($email) || empty($password)) {
            $this->setFlash('Email dan password harus diisi.', 'error');
            $this->redirect('auth/login');
            return;
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('Format email tidak valid.', 'error');
            $this->redirect('auth/login');
            return;
        }

        // Cari user berdasarkan email
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->setFlash('Email atau password salah.', 'error');
            $this->redirect('auth/login');
            return;
        }

        // Verifikasi password
        if (!$this->userModel->verifyPassword($password, $user->password)) {
            $this->setFlash('Email atau password salah.', 'error');
            $this->redirect('auth/login');
            return;
        }

        // Set session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_email'] = $user->email;

        // Buat kategori default jika user belum punya kategori
        $existingCategories = $this->categoryModel->getByUser($user->id);
        if (empty($existingCategories)) {
            $this->categoryModel->createDefault($user->id);
        }

        $this->setFlash('Selamat datang kembali, ' . htmlspecialchars($user->name) . '!', 'success');
        $this->redirect('dashboard');
    }

    /**
     * Menampilkan halaman register
     * GET /auth/register
     */
    public function register()
    {
        // Jika sudah login, redirect ke dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
            return;
        }

        // Ambil old input dari session (jika ada, untuk re-populate form setelah error)
        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        $this->viewOnly('auth/register', [
            'pageTitle' => 'Register',
            'oldInput' => $oldInput
        ]);
    }

    /**
     * Proses registrasi
     * POST /auth/register
     */
    public function doRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth/register');
            return;
        }

        // Ambil input
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Simpan old input untuk re-populate form jika gagal
        $_SESSION['old_input'] = [
            'name' => $name,
            'email' => $email
        ];

        // Validasi field wajib
        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            $this->setFlash('Semua field harus diisi.', 'error');
            $this->redirect('auth/register');
            return;
        }

        // Validasi panjang nama
        if (strlen($name) < 3) {
            $this->setFlash('Nama harus minimal 3 karakter.', 'error');
            $this->redirect('auth/register');
            return;
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('Format email tidak valid.', 'error');
            $this->redirect('auth/register');
            return;
        }

        // Validasi panjang password
        if (strlen($password) < 6) {
            $this->setFlash('Password harus minimal 6 karakter.', 'error');
            $this->redirect('auth/register');
            return;
        }

        // Validasi konfirmasi password
        if ($password !== $confirmPassword) {
            $this->setFlash('Konfirmasi password tidak cocok.', 'error');
            $this->redirect('auth/register');
            return;
        }

        // Cek apakah email sudah terdaftar
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser) {
            $this->setFlash('Email sudah terdaftar. Silakan gunakan email lain.', 'error');
            $this->redirect('auth/register');
            return;
        }

        // Proses registrasi
        $userId = $this->userModel->register($name, $email, $password);

        if (!$userId) {
            $this->setFlash('Terjadi kesalahan saat mendaftar. Silakan coba lagi.', 'error');
            $this->redirect('auth/register');
            return;
        }

        // Hapus old input setelah berhasil
        unset($_SESSION['old_input']);

        // Set session
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        // Buat kategori default untuk user baru
        $this->categoryModel->createDefault($userId);

        $this->setFlash('Registrasi berhasil! Selamat datang, ' . htmlspecialchars($name) . '!', 'success');
        $this->redirect('dashboard');
    }

    /**
     * Proses logout
     * Menghancurkan session dan redirect ke halaman login
     */
    public function logout()
    {
        // Hapus semua data session
        $_SESSION = [];

        // Hapus session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Hancurkan session
        session_destroy();

        $this->redirect('auth/login');
    }
}
