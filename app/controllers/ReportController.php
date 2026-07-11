<?php

/**
 * ReportController
 * 
 * Menangani halaman laporan keuangan dan ekspor data ke CSV.
 */
class ReportController extends Controller
{
    private $transactionModel;
    private $categoryModel;

    public function __construct()
    {
        $this->transactionModel = new Transaction();
        $this->categoryModel = new Category();
    }

    /**
     * Menampilkan halaman laporan keuangan
     * GET /reports
     */
    public function index()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Ambil parameter filter dari query string
        // Default: dari awal bulan ini sampai hari ini
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
            'type' => $_GET['type'] ?? '',
            'category_id' => $_GET['category_id'] ?? ''
        ];

        // Ambil transaksi dengan filter
        $transactions = $this->transactionModel->getByUser($userId, $filters);

        // Hitung ringkasan total
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->type === 'income') {
                $totalIncome += (float) $transaction->amount;
            } elseif ($transaction->type === 'expense') {
                $totalExpense += (float) $transaction->amount;
            }
        }

        $balance = $totalIncome - $totalExpense;

        // Ambil kategori untuk dropdown filter
        $categories = $this->categoryModel->getByUser($userId);

        $this->view('reports/index', [
            'pageTitle' => 'Laporan Keuangan',
            'currentPage' => 'reports',
            'transactions' => $transactions,
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'balance' => $balance
            ],
            'categories' => $categories,
            'filter' => $filters
        ]);
    }

    /**
     * Ekspor laporan ke file CSV
     * GET /reports/export
     * 
     * Menghasilkan file CSV yang bisa diunduh berdasarkan filter yang sama
     * dengan halaman laporan.
     */
    public function export()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Ambil parameter filter (sama dengan halaman laporan)
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
            'type' => $_GET['type'] ?? '',
            'category_id' => $_GET['category_id'] ?? ''
        ];

        // Ambil transaksi berdasarkan filter
        $transactions = $this->transactionModel->getByUser($userId, $filters);

        // Buat nama file berdasarkan rentang tanggal
        $fileName = 'laporan_keuangan_' . $filters['date_from'] . '_sd_' . $filters['date_to'] . '.csv';

        // Set header HTTP untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Buka output stream
        $output = fopen('php://output', 'w');

        // Tulis BOM UTF-8 agar Excel membaca karakter Indonesia dengan benar
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Tulis header kolom
        fputcsv($output, [
            'No',
            'Tanggal',
            'Tipe',
            'Kategori',
            'Deskripsi',
            'Jumlah (Rp)',
            'Bukti Foto'
        ]);

        // Tulis data transaksi
        $no = 1;
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($transactions as $transaction) {
            $typeLabel = $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
            $categoryName = $transaction->category_name ?? '-';
            $hasReceipt = !empty($transaction->receipt_photo) ? 'Ya' : 'Tidak';
            $amount = (float) $transaction->amount;

            // Hitung total untuk ringkasan
            if ($transaction->type === 'income') {
                $totalIncome += $amount;
            } else {
                $totalExpense += $amount;
            }

            fputcsv($output, [
                $no++,
                $transaction->transaction_date,
                $typeLabel,
                $categoryName,
                $transaction->description ?? '-',
                number_format($amount, 0, ',', '.'),
                $hasReceipt
            ]);
        }

        // Tulis baris kosong dan ringkasan di akhir
        fputcsv($output, []);
        fputcsv($output, ['', '', '', '', 'Total Pemasukan:', number_format($totalIncome, 0, ',', '.'), '']);
        fputcsv($output, ['', '', '', '', 'Total Pengeluaran:', number_format($totalExpense, 0, ',', '.'), '']);
        fputcsv($output, ['', '', '', '', 'Saldo:', number_format($totalIncome - $totalExpense, 0, ',', '.'), '']);
        fputcsv($output, []);
        fputcsv($output, ['Laporan dibuat pada: ' . date('d/m/Y H:i:s')]);
        fputcsv($output, ['Periode: ' . $filters['date_from'] . ' s/d ' . $filters['date_to']]);

        fclose($output);
        exit;
    }
}
