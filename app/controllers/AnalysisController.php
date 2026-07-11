<?php
/**
 * AnalysisController
 * 
 * Mengelola fitur analisis pengeluaran terbesar dan budgeting advisor (target penghematan).
 */
class AnalysisController extends Controller
{
    private $transactionModel;
    private $categoryModel;

    public function __construct()
    {
        $this->transactionModel = new Transaction();
        $this->categoryModel = new Category();
    }

    /**
     * Menampilkan halaman analisis budgeting
     * GET /analysis
     */
    public function index()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $currentMonth = date('Y-m');
        $monthName = thisMonthIndonesian(date('n'));
        $year = date('Y');

        // Query mengambil pengeluaran per kategori untuk bulan berjalan
        // Query menggunakan LEFT JOIN agar kategori dengan transaksi 0 tetap muncul sebagai pembanding
        $sql = "SELECT 
                    c.id, 
                    c.name, 
                    c.color, 
                    c.is_fixed,
                    COALESCE(SUM(t.amount), 0) as total
                FROM categories c
                LEFT JOIN transactions t ON c.id = t.category_id 
                    AND t.type = 'expense' 
                    AND strftime('%Y-%m', t.transaction_date) = :month
                WHERE c.user_id = :user_id 
                  AND c.type = 'expense'
                GROUP BY c.id
                ORDER BY total DESC";

        $categoriesData = $this->transactionModel->query($sql, [
            ':user_id' => $userId,
            ':month' => $currentMonth
        ]);

        // Hitung total ringkasan
        $totalFixed = 0;
        $totalOptional = 0;
        $topOptionalCategory = null;
        $topOptionalAmount = 0;

        foreach ($categoriesData as $cat) {
            $totalAmount = (float)$cat->total;
            if ($cat->is_fixed == 1) {
                $totalFixed += $totalAmount;
            } else {
                $totalOptional += $totalAmount;
                // Cari kategori pengeluaran opsional terbesar
                if ($totalAmount > $topOptionalAmount) {
                    $topOptionalAmount = $totalAmount;
                    $topOptionalCategory = $cat;
                }
            }
        }

        $totalExpense = $totalFixed + $totalOptional;

        // Dapatkan tips dinamis berdasarkan kategori opsional terbesar
        $financialTip = $this->getBudgetingTip($topOptionalCategory, $topOptionalAmount);

        $this->view('analysis/index', [
            'pageTitle' => 'Analisis & Budget',
            'currentPage' => 'analysis',
            'categoriesData' => $categoriesData,
            'totalFixed' => $totalFixed,
            'totalOptional' => $totalOptional,
            'totalExpense' => $totalExpense,
            'monthName' => $monthName,
            'year' => $year,
            'financialTip' => $financialTip
        ]);
    }

    /**
     * Menghasilkan tips penghematan dinamis
     */
    private function getBudgetingTip($topCategory, $amount)
    {
        if (!$topCategory || $amount == 0) {
            return "Luar biasa! Pengeluaran opsional Anda bulan ini sangat minim (Rp 0). Pertahankan disiplin keuangan Anda dan teruslah menabung!";
        }

        $name = strtolower($topCategory->name);
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');

        if (strpos($name, 'konsumsi') !== false || strpos($name, 'makan') !== false || strpos($name, 'jajan') !== false) {
            return "Pengeluaran opsional terbesar Anda bulan ini adalah <strong>" . htmlspecialchars($topCategory->name) . "</strong> sebesar <strong>" . $formattedAmount . "</strong>. Cobalah kurangi frekuensi jajan/pesan makan online di luar, dan ganti dengan memasak sendiri di rumah. Hal ini dapat menghemat hingga 40% anggaran makan Anda!";
        }

        if (strpos($name, 'transport') !== false || strpos($name, 'bensin') !== false || strpos($name, 'kendaraan') !== false) {
            return "Anggaran transportasi non-wajib Anda (<strong>" . htmlspecialchars($topCategory->name) . "</strong>) mencapai <strong>" . $formattedAmount . "</strong>. Pertimbangkan untuk menggunakan transportasi umum atau melakukan *ride-sharing* dengan teman kerja untuk memangkas biaya bahan bakar.";
        }

        if (strpos($name, 'sewa') !== false || strpos($name, 'kost') !== false || strpos($name, 'kontrakan') !== false || strpos($name, 'tagihan') !== false || strpos($name, 'listrik') !== false) {
            return "Anda mencatat pengeluaran di <strong>" . htmlspecialchars($topCategory->name) . "</strong> sebesar <strong>" . $formattedAmount . "</strong> sebagai pengeluaran opsional. Jika ini merupakan tagihan bulanan tetap, Anda disarankan mengedit kategori ini menjadi status <strong>Tetap (Wajib)</strong> agar analisis budgeting bulan depan lebih akurat.";
        }

        // Default tips berdasarkan kata kunci lain
        return "Pengeluaran opsional pada kategori <strong>" . htmlspecialchars($topCategory->name) . "</strong> memakan porsi terbesar yaitu <strong>" . $formattedAmount . "</strong>. Cobalah terapkan aturan *30-day rule* sebelum membeli barang pada kategori ini: tunda keinginan membeli selama 30 hari untuk menilai apakah barang tersebut benar-benar Anda butuhkan.";
    }
}

/**
 * Helper untuk konversi angka bulan ke nama bulan Indonesia
 */
function thisMonthIndonesian($monthNum) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $months[$monthNum] ?? '';
}
