<?php
/**
 * AccountController
 * 
 * Mengelola pengelolaan rekening/dompet penyimpanan uang.
 */
class AccountController extends Controller
{
    private $accountModel;

    public function __construct()
    {
        $this->accountModel = new Account();
    }

    /**
     * Menampilkan daftar rekening
     * GET /accounts
     */
    public function index()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $accounts = $this->accountModel->getByUser($userId);

        $this->view('accounts/index', [
            'pageTitle' => 'Kelola Rekening',
            'currentPage' => 'accounts',
            'accounts' => $accounts
        ]);
    }

    /**
     * Menampilkan form tambah rekening baru
     * GET /accounts/create
     */
    public function create()
    {
        $this->requireLogin();

        $this->view('accounts/create', [
            'pageTitle' => 'Rekening Baru',
            'currentPage' => 'accounts'
        ]);
    }

    /**
     * Menyimpan rekening baru
     * POST /accounts/store
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('accounts/create');
            return;
        }

        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? '#6366f1';
        $icon = $_POST['icon'] ?? 'account_balance_wallet';
        $type = $_POST['type'] ?? 'debit';
        
        if (!in_array($type, ['debit', 'credit'])) {
            $type = 'debit';
        }

        if (empty($name)) {
            $this->setFlash('Nama rekening tidak boleh kosong.', 'error');
            $this->redirect('accounts/create');
            return;
        }

        $result = $this->accountModel->create([
            'user_id' => $userId,
            'name' => $name,
            'color' => $color,
            'icon' => $icon,
            'type' => $type
        ]);

        if ($result) {
            $this->setFlash('Rekening berhasil ditambahkan.', 'success');
        } else {
            $this->setFlash('Gagal menambahkan rekening.', 'error');
        }

        $this->redirect('accounts');
    }

    /**
     * Menampilkan form edit rekening
     * GET /accounts/edit/{id}
     */
    public function edit($id)
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $account = $this->accountModel->findByIdAndUser($id, $userId);

        if (!$account) {
            $this->setFlash('Rekening tidak ditemukan.', 'error');
            $this->redirect('accounts');
            return;
        }

        $this->view('accounts/edit', [
            'pageTitle' => 'Edit Rekening',
            'currentPage' => 'accounts',
            'account' => $account
        ]);
    }

    /**
     * Memperbarui rekening
     * POST /accounts/update/{id}
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('accounts');
            return;
        }

        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $account = $this->accountModel->findByIdAndUser($id, $userId);

        if (!$account) {
            $this->setFlash('Rekening tidak ditemukan.', 'error');
            $this->redirect('accounts');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? '#6366f1';
        $icon = $_POST['icon'] ?? 'account_balance_wallet';
        $type = $_POST['type'] ?? 'debit';

        if (!in_array($type, ['debit', 'credit'])) {
            $type = 'debit';
        }

        if (empty($name)) {
            $this->setFlash('Nama rekening tidak boleh kosong.', 'error');
            $this->redirect('accounts/edit/' . $id);
            return;
        }

        $updated = $this->accountModel->update($id, [
            'name' => $name,
            'color' => $color,
            'icon' => $icon,
            'type' => $type
        ]);

        if ($updated) {
            $this->setFlash('Rekening berhasil diperbarui.', 'success');
        } else {
            $this->setFlash('Gagal memperbarui rekening.', 'error');
        }

        $this->redirect('accounts');
    }

    /**
     * Menghapus rekening
     * GET/POST /accounts/delete/{id}
     */
    public function delete($id)
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        
        // Cari rekening
        $account = $this->accountModel->findByIdAndUser($id, $userId);
        if (!$account) {
            $this->setFlash('Rekening tidak ditemukan.', 'error');
            $this->redirect('accounts');
            return;
        }

        // Cek jumlah rekening user saat ini
        $allAccounts = $this->accountModel->getByUser($userId);
        if (count($allAccounts) <= 1) {
            $this->setFlash('Gagal menghapus. Anda harus memiliki minimal satu rekening aktif.', 'error');
            $this->redirect('accounts');
            return;
        }

        // Hapus rekening
        $deleted = $this->accountModel->delete($id);

        if ($deleted) {
            $this->setFlash('Rekening berhasil dihapus. Transaksi terkait kini tidak memiliki rekening.', 'success');
        } else {
            $this->setFlash('Gagal menghapus rekening.', 'error');
        }

        $this->redirect('accounts');
    }
}
