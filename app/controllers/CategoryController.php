<?php

/**
 * CategoryController
 * 
 * Menangani manajemen kategori transaksi (CRUD).
 * Setiap user memiliki kategori masing-masing.
 */
class CategoryController extends Controller
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    /**
     * Menampilkan daftar kategori milik user
     * GET /categories
     */
    public function index()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Ambil kategori beserta jumlah transaksi yang menggunakannya
        $categories = $this->categoryModel->getWithTransactionCount($userId);

        $this->view('categories/index', [
            'pageTitle' => 'Kategori',
            'currentPage' => 'categories',
            'categories' => $categories
        ]);
    }

    /**
     * Proses tambah kategori baru
     * POST /categories/store
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
            return;
        }

        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Ambil dan validasi input
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';
        $color = $_POST['color'] ?? '#6366f1'; // Warna default

        // Validasi field wajib
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Nama kategori harus diisi.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Nama kategori maksimal 100 karakter.';
        }

        if (empty($type) || !in_array($type, ['income', 'expense'])) {
            $errors[] = 'Tipe kategori harus dipilih (pemasukan/pengeluaran).';
        }

        // Validasi format warna hex
        if (!empty($color) && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $errors[] = 'Format warna tidak valid.';
        }

        if (!empty($errors)) {
            $this->setFlash(implode(' ', $errors), 'error');
            $this->redirect('categories');
            return;
        }

        // Simpan kategori baru
        $isFixed = isset($_POST['is_fixed']) ? 1 : 0;
        if ($type === 'income') {
            $isFixed = 0;
        }

        $created = $this->categoryModel->create([
            'user_id' => $userId,
            'name' => $name,
            'type' => $type,
            'color' => $color,
            'is_fixed' => $isFixed
        ]);

        if ($created) {
            $this->setFlash('Kategori "' . htmlspecialchars($name) . '" berhasil ditambahkan.', 'success');
        } else {
            $this->setFlash('Gagal menambahkan kategori. Silakan coba lagi.', 'error');
        }

        $this->redirect('categories');
    }

    /**
     * Proses update kategori
     * POST /categories/update/{id}
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
            return;
        }

        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Cari kategori
        $category = $this->categoryModel->findById($id);

        if (!$category) {
            $this->setFlash('Kategori tidak ditemukan.', 'error');
            $this->redirect('categories');
            return;
        }

        // Verifikasi kepemilikan
        if ($category->user_id != $userId) {
            $this->setFlash('Anda tidak memiliki akses ke kategori ini.', 'error');
            $this->redirect('categories');
            return;
        }

        // Ambil dan validasi input
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';
        $color = $_POST['color'] ?? $category->color;

        // Validasi field wajib
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Nama kategori harus diisi.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Nama kategori maksimal 100 karakter.';
        }

        if (empty($type) || !in_array($type, ['income', 'expense'])) {
            $errors[] = 'Tipe kategori harus dipilih (pemasukan/pengeluaran).';
        }

        if (!empty($color) && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $errors[] = 'Format warna tidak valid.';
        }

        if (!empty($errors)) {
            $this->setFlash(implode(' ', $errors), 'error');
            $this->redirect('categories');
            return;
        }

        // Update kategori
        $isFixed = isset($_POST['is_fixed']) ? 1 : 0;
        if ($type === 'income') {
            $isFixed = 0;
        }

        $updated = $this->categoryModel->update($id, [
            'name' => $name,
            'type' => $type,
            'color' => $color,
            'is_fixed' => $isFixed
        ]);

        if ($updated) {
            $this->setFlash('Kategori "' . htmlspecialchars($name) . '" berhasil diperbarui.', 'success');
        } else {
            $this->setFlash('Gagal memperbarui kategori.', 'error');
        }

        $this->redirect('categories');
    }

    /**
     * Proses hapus kategori
     * POST/GET /categories/delete/{id}
     */
    public function delete($id)
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Cari kategori
        $category = $this->categoryModel->findById($id);

        if (!$category) {
            $this->setFlash('Kategori tidak ditemukan.', 'error');
            $this->redirect('categories');
            return;
        }

        // Verifikasi kepemilikan
        if ($category->user_id != $userId) {
            $this->setFlash('Anda tidak memiliki akses ke kategori ini.', 'error');
            $this->redirect('categories');
            return;
        }

        // Cek apakah kategori masih digunakan oleh transaksi
        $categoriesWithCount = $this->categoryModel->getWithTransactionCount($userId);
        $transactionCount = 0;
        foreach ($categoriesWithCount as $cat) {
            if ($cat->id == $id) {
                $transactionCount = $cat->transaction_count ?? 0;
                break;
            }
        }

        if ($transactionCount > 0) {
            $this->setFlash(
                'Kategori "' . htmlspecialchars($category->name) . '" tidak dapat dihapus karena masih digunakan oleh ' 
                . $transactionCount . ' transaksi. Pindahkan transaksi ke kategori lain terlebih dahulu.',
                'warning'
            );
            $this->redirect('categories');
            return;
        }

        // Hapus kategori
        $deleted = $this->categoryModel->delete($id);

        if ($deleted) {
            $this->setFlash('Kategori "' . htmlspecialchars($category->name) . '" berhasil dihapus.', 'success');
        } else {
            $this->setFlash('Gagal menghapus kategori.', 'error');
        }

        $this->redirect('categories');
    }
}
