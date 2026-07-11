<?php
/**
 * Base Controller
 * 
 * Kelas dasar untuk semua controller dalam aplikasi.
 * Menyediakan method untuk rendering view, redirect,
 * autentikasi, dan flash messages.
 */
class Controller
{
    /**
     * Render view dengan layout utama
     * 
     * Menggunakan output buffering untuk menangkap konten view,
     * kemudian menyisipkannya ke dalam layout utama.
     * 
     * @param string $view Nama file view (tanpa ekstensi .php)
     * @param array  $data Data yang akan dikirim ke view
     */
    protected function view(string $view, array $data = []): void
    {
        // Ekstrak data agar tersedia sebagai variabel di view
        extract($data);

        // Pastikan $pageTitle dan $currentPage tersedia untuk layout
        $pageTitle = $pageTitle ?? 'Keuangan App';
        $currentPage = $currentPage ?? '';

        // Tangkap output view dengan output buffering
        ob_start();
        $viewFile = ROOT_PATH . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<p>View <strong>{$view}</strong> tidak ditemukan.</p>";
        }

        // Simpan konten yang sudah di-render
        $content = ob_get_clean();

        // Include layout utama (layout akan menggunakan $content, $pageTitle, $currentPage)
        require ROOT_PATH . '/app/views/layouts/main.php';
    }

    /**
     * Render view tanpa layout (untuk halaman auth, dsb.)
     * 
     * @param string $view Nama file view (tanpa ekstensi .php)
     * @param array  $data Data yang akan dikirim ke view
     */
    protected function viewOnly(string $view, array $data = []): void
    {
        // Ekstrak data agar tersedia sebagai variabel di view
        extract($data);

        $viewFile = ROOT_PATH . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<p>View <strong>{$view}</strong> tidak ditemukan.</p>";
        }
    }

    /**
     * Redirect ke URL tertentu
     * 
     * @param string $url Path relatif (akan digabung dengan BASE_URL)
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . '/' . $url);
        exit;
    }

    /**
     * Cek apakah user sudah login
     * 
     * @return bool True jika user sudah login
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Memastikan user sudah login, redirect jika belum
     */
    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }

    /**
     * Mengirim response dalam format JSON
     * 
     * @param mixed $data Data yang akan di-encode ke JSON
     */
    protected function jsonResponse(mixed $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Menyimpan flash message ke session
     * 
     * @param string $message Pesan yang akan ditampilkan
     * @param string $type    Tipe pesan (success, error, warning, info)
     */
    protected function setFlash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = compact('message', 'type');
    }

    /**
     * Mengambil dan menghapus flash message dari session
     * 
     * @return array|null Array ['message', 'type'] atau null
     */
    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }

        return null;
    }
}
