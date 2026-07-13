<?php

/**
 * TransactionController
 * 
 * Menangani CRUD transaksi keuangan termasuk upload bukti foto (receipt).
 */
class TransactionController extends Controller
{
    private $transactionModel;
    private $categoryModel;
    private $accountModel;

    public function __construct()
    {
        $this->transactionModel = new Transaction();
        $this->categoryModel = new Category();
        $this->accountModel = new Account();
    }

    /**
     * Menampilkan daftar transaksi dengan filter
     * GET /transactions
     */
    public function index()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Ambil parameter filter dari query string
        $filters = [
            'type' => $_GET['type'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'account_id' => $_GET['account_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => trim($_GET['search'] ?? '')
        ];

        // Ambil transaksi berdasarkan user dan filter
        $transactions = $this->transactionModel->getByUser($userId, $filters);

        // Ambil kategori untuk dropdown filter
        $categories = $this->categoryModel->getByUser($userId);

        // Ambil rekening untuk dropdown filter
        $accounts = $this->accountModel->getByUser($userId);

        $this->view('transactions/index', [
            'pageTitle' => 'Riwayat Transaksi',
            'currentPage' => 'transactions',
            'transactions' => $transactions,
            'categories' => $categories,
            'accounts' => $accounts,
            'filters' => $filters
        ]);
    }

    /**
     * Menampilkan form tambah transaksi baru
     * GET /transactions/create
     */
    public function create()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Ambil kategori milik user, dipisahkan berdasarkan tipe
        $categories = $this->categoryModel->getByUser($userId);

        // Pisahkan kategori berdasarkan tipe untuk kemudahan di view
        $incomeCategories = array_filter($categories, function ($cat) {
            return $cat->type === 'income';
        });
        $expenseCategories = array_filter($categories, function ($cat) {
            return $cat->type === 'expense';
        });

        // Ambil semua rekening milik user
        $accounts = $this->accountModel->getByUser($userId);

        $this->view('transactions/create', [
            'pageTitle' => 'Transaksi Baru',
            'currentPage' => 'transactions_create',
            'categories' => $categories,
            'incomeCategories' => array_values($incomeCategories),
            'expenseCategories' => array_values($expenseCategories),
            'accounts' => $accounts
        ]);
    }

    /**
     * Proses simpan transaksi baru
     * POST /transactions/store
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('transactions/create');
            return;
        }

        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Ambil dan validasi input
        $amount = $_POST['amount'] ?? '';
        $type = $_POST['type'] ?? '';
        $transactionDate = $_POST['transaction_date'] ?? '';
        $categoryId = $_POST['category_id'] ?? '';
        $accountId = $_POST['account_id'] ?? '';
        $description = trim($_POST['description'] ?? '');

        // Validasi field wajib
        $errors = [];

        if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
            $errors[] = 'Jumlah harus berupa angka positif.';
        }

        if (empty($type) || !in_array($type, ['income', 'expense'])) {
            $errors[] = 'Tipe transaksi harus dipilih (pemasukan/pengeluaran).';
        }

        if (empty($transactionDate)) {
            $errors[] = 'Tanggal transaksi harus diisi.';
        } elseif (!$this->isValidDate($transactionDate)) {
            $errors[] = 'Format tanggal tidak valid.';
        }

        if (empty($categoryId)) {
            $errors[] = 'Kategori harus dipilih.';
        }

        if (empty($accountId)) {
            $errors[] = 'Rekening harus dipilih.';
        }

        // Jika ada error validasi
        if (!empty($errors)) {
            $this->setFlash(implode(' ', $errors), 'error');
            $this->redirect('transactions/create');
            return;
        }

        // Verifikasi kategori milik user yang bersangkutan
        $category = $this->categoryModel->findById($categoryId);
        if (!$category || $category->user_id != $userId) {
            $this->setFlash('Kategori tidak valid.', 'error');
            $this->redirect('transactions/create');
            return;
        }

        // Verifikasi rekening milik user yang bersangkutan
        $account = $this->accountModel->findByIdAndUser($accountId, $userId);
        if (!$account) {
            $this->setFlash('Rekening tidak valid.', 'error');
            $this->redirect('transactions/create');
            return;
        }

        // Siapkan data transaksi
        $transactionData = [
            'user_id' => $userId,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'type' => $type,
            'amount' => (float) $amount,
            'description' => $description,
            'transaction_date' => $transactionDate
        ];

        // Handle upload file bukti (receipt)
        $receiptFile = $_FILES['receipt_photo'] ?? null;

        try {
            $result = $this->transactionModel->createWithReceipt($transactionData, $receiptFile);

            if ($result) {
                $typeLabel = $type === 'income' ? 'pemasukan' : 'pengeluaran';
                $this->setFlash('Transaksi ' . $typeLabel . ' berhasil ditambahkan.', 'success');
            } else {
                $this->setFlash('Gagal menyimpan transaksi. Silakan coba lagi.', 'error');
            }
        } catch (Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'error');
            $this->redirect('transactions/create');
            return;
        }

        $this->redirect('transactions');
    }

    /**
     * Hapus transaksi
     * GET/POST /transactions/delete/{id}
     */
    public function delete($id)
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Cari transaksi
        $transaction = $this->transactionModel->findById($id);

        if (!$transaction) {
            $this->setFlash('Transaksi tidak ditemukan.', 'error');
            $this->redirect('transactions');
            return;
        }

        // Verifikasi kepemilikan transaksi
        if ($transaction->user_id != $userId) {
            $this->setFlash('Anda tidak memiliki akses ke transaksi ini.', 'error');
            $this->redirect('transactions');
            return;
        }

        // Hapus file bukti foto jika ada
        if (!empty($transaction->receipt_photo)) {
            $receiptPath = UPLOAD_PATH . '/' . $transaction->receipt_photo;
            if (file_exists($receiptPath)) {
                unlink($receiptPath);
            }
        }

        // Hapus record dari database
        $deleted = $this->transactionModel->delete($id);

        if ($deleted) {
            $this->setFlash('Transaksi berhasil dihapus.', 'success');
        } else {
            $this->setFlash('Gagal menghapus transaksi.', 'error');
        }

        $this->redirect('transactions');
    }

    /**
     * Menampilkan bukti foto transaksi (receipt preview)
     * GET /transactions/viewReceipt/{id}
     */
    public function viewReceipt($id)
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Cari transaksi
        $transaction = $this->transactionModel->findById($id);

        if (!$transaction) {
            http_response_code(404);
            echo 'Transaksi tidak ditemukan.';
            return;
        }

        // Verifikasi kepemilikan
        if ($transaction->user_id != $userId) {
            http_response_code(403);
            echo 'Akses ditolak.';
            return;
        }

        // Cek apakah ada file receipt
        if (empty($transaction->receipt_photo)) {
            http_response_code(404);
            echo 'Bukti foto tidak tersedia.';
            return;
        }

        $receiptPath = UPLOAD_PATH . '/' . $transaction->receipt_photo;

        if (!file_exists($receiptPath)) {
            http_response_code(404);
            echo 'File bukti foto tidak ditemukan.';
            return;
        }

        // Deteksi MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $receiptPath);
        finfo_close($finfo);

        // Validasi bahwa file adalah gambar
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            http_response_code(400);
            echo 'Tipe file tidak didukung.';
            return;
        }

        // Output file gambar dengan header yang sesuai
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($receiptPath));
        header('Content-Disposition: inline; filename="' . basename($transaction->receipt_photo) . '"');
        header('Cache-Control: private, max-age=3600');

        readfile($receiptPath);
        exit;
    }

    /**
     * Validasi format tanggal (YYYY-MM-DD)
     */
    private function isValidDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
