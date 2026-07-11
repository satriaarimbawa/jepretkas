<?php
/**
 * App Router
 * 
 * Kelas router utama yang menangani URL routing.
 * Mengurai URL, memuat controller yang sesuai,
 * dan memanggil method yang diminta.
 * 
 * Format URL: controller/method/param1/param2/...
 */
class App
{
    /** @var mixed Controller yang akan dimuat (bisa nama class string atau instance object) */
    protected $controller = 'DashboardController';

    /** @var string Nama method yang akan dipanggil */
    protected $method = 'index';

    /** @var array Parameter tambahan dari URL */
    protected array $params = [];

    /**
     * Constructor
     * 
     * Parsing URL, memuat controller, dan menjalankan method.
     */
    public function __construct()
    {
        $url = $this->parseUrl();

        // --- Tentukan Controller ---
        if (isset($url[0]) && !empty($url[0])) {
            $controllerName = $url[0];
            
            // Petakan segmen URL jamak ke nama controller tunggal (singular)
            $pluralMap = [
                'transactions' => 'transaction',
                'categories'   => 'category',
                'reports'      => 'report'
            ];
            
            $lowerName = strtolower($controllerName);
            if (isset($pluralMap[$lowerName])) {
                $controllerName = $pluralMap[$lowerName];
            }
            
            $this->controller = ucfirst($controllerName) . 'Controller';
            unset($url[0]);
        }

        // --- Cek Auth: Jika bukan AuthController, user harus login ---
        if ($this->controller !== 'AuthController') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . '/auth/login');
                exit;
            }
        }

        // --- Cek apakah file controller ada ---
        $controllerFile = ROOT_PATH . '/app/controllers/' . $this->controller . '.php';

        if (!file_exists($controllerFile)) {
            $this->show404();
            return;
        }

        // Muat dan instansiasi controller
        require_once $controllerFile;

        if (!class_exists($this->controller)) {
            $this->show404();
            return;
        }

        $this->controller = new $this->controller;

        // --- Tentukan Method ---
        if (isset($url[1]) && !empty($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
            } else {
                $this->show404();
                return;
            }
            unset($url[1]);
        }

        // --- Ambil parameter yang tersisa ---
        $this->params = $url ? array_values($url) : [];

        // --- Panggil method controller dengan parameter ---
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Parsing URL dari $_GET['url']
     * 
     * Membersihkan URL, menghilangkan trailing slash,
     * dan memecah menjadi array segmen.
     * 
     * @return array Segmen URL yang sudah di-sanitize
     */
    protected function parseUrl(): array
    {
        if (isset($_GET['url'])) {
            // Bersihkan URL: trim slash, sanitize, pecah
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }

        return [];
    }

    /**
     * Menampilkan halaman 404 Not Found
     */
    protected function show404(): void
    {
        http_response_code(404);

        // Cek apakah ada view 404 kustom
        $errorView = ROOT_PATH . '/app/views/errors/404.php';

        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo '<!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>404 - Halaman Tidak Ditemukan</title>
                <style>
                    body {
                        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                        margin: 0;
                        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                        color: #e2e8f0;
                    }
                    .error-container {
                        text-align: center;
                        padding: 2rem;
                    }
                    h1 {
                        font-size: 6rem;
                        margin: 0;
                        background: linear-gradient(135deg, #6366f1, #8b5cf6);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                    }
                    p {
                        font-size: 1.25rem;
                        color: #94a3b8;
                        margin-top: 0.5rem;
                    }
                    a {
                        display: inline-block;
                        margin-top: 1.5rem;
                        padding: 0.75rem 2rem;
                        background: linear-gradient(135deg, #6366f1, #8b5cf6);
                        color: white;
                        text-decoration: none;
                        border-radius: 0.5rem;
                        transition: opacity 0.2s;
                    }
                    a:hover { opacity: 0.85; }
                </style>
            </head>
            <body>
                <div class="error-container">
                    <h1>404</h1>
                    <p>Halaman yang Anda cari tidak ditemukan.</p>
                    <a href="' . BASE_URL . '">Kembali ke Beranda</a>
                </div>
            </body>
            </html>';
        }

        exit;
    }
}
