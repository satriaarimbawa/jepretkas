<?php

/**
 * DashboardController
 * 
 * Menangani halaman utama dashboard dengan ringkasan keuangan,
 * grafik bulanan, dan transaksi terbaru.
 */
class DashboardController extends Controller
{
    private $transactionModel;
    private $accountModel;

    public function __construct()
    {
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
    }

    /**
     * Menampilkan halaman dashboard utama
     * GET /dashboard
     */
    public function index()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $currentYear = date('Y');

        // Ambil total pemasukan dan pengeluaran
        $totalIncome = $this->transactionModel->getTotalByType($userId, 'income') ?? 0;
        $totalExpense = $this->transactionModel->getTotalByType($userId, 'expense') ?? 0;
        
        // Ambil data rekening dan hitung saldo total
        $accounts = $this->accountModel->getByUser($userId);
        $balance = 0;
        foreach ($accounts as $acc) {
            $balance += $acc->balance;
        }

        // Ambil 5 transaksi terbaru
        $recentTransactions = $this->transactionModel->getRecent($userId, 5);

        // Ambil data bulanan untuk grafik tahun ini
        $monthlyData = $this->transactionModel->getMonthlyData($userId, $currentYear);

        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'currentPage' => 'dashboard',
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'totalBalance' => $balance,
            'accounts' => $accounts,
            'recentTransactions' => $recentTransactions,
            'monthlyData' => $monthlyData,
            'currentYear' => $currentYear
        ]);
    }

    /**
     * Endpoint JSON untuk data grafik bulanan
     * GET /dashboard/chartData
     * 
     * Mengembalikan data dalam format JSON untuk digunakan oleh Chart.js
     * atau library grafik lainnya di frontend.
     */
    public function chartData()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $year = $_GET['year'] ?? date('Y');

        // Ambil data bulanan dari model
        $monthlyData = $this->transactionModel->getMonthlyData($userId, $year);

        // Nama bulan dalam Bahasa Indonesia
        $monthNames = [
            'Januari', 'Februari', 'Maret', 'April',
            'Mei', 'Juni', 'Juli', 'Agustus',
            'September', 'Oktober', 'November', 'Desember'
        ];

        // Siapkan array data per bulan (default 0 untuk setiap bulan)
        $incomeData = array_fill(0, 12, 0);
        $expenseData = array_fill(0, 12, 0);

        // Isi data dari hasil query
        if (!empty($monthlyData)) {
            foreach ($monthlyData as $data) {
                $monthIndex = (int)$data['month'] - 1; // Konversi ke index 0-based
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $incomeData[$monthIndex] = (float)$data['income'];
                    $expenseData[$monthIndex] = (float)$data['expense'];
                }
            }
        }

        $this->jsonResponse([
            'success' => true,
            'labels' => $monthNames,
            'datasets' => [
                'income' => $incomeData,
                'expense' => $expenseData
            ],
            'year' => $year
        ]);
    }
}
