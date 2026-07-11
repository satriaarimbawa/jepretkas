<?php
/**
 * Transaction Model
 * 
 * Model untuk mengelola data transaksi keuangan.
 * Mendukung filter, statistik bulanan, dan upload bukti transaksi.
 */
class Transaction extends Model
{
    /** @var string Nama tabel */
    protected string $table = 'transactions';

    /**
     * Mengambil transaksi berdasarkan user dengan filter
     * 
     * Mendukung filter berdasarkan tipe, kategori, rentang tanggal, dan pencarian.
     * Hasil di-JOIN dengan tabel categories untuk mendapatkan nama kategori.
     * 
     * @param int   $userId  ID user
     * @param array $filters Filter opsional: type, category_id, date_from, date_to, search
     * @return array Array of objects transaksi
     */
    public function getByUser(int $userId, array $filters = []): array
    {
        $sql = "SELECT t.*, c.name as category_name, c.color as category_color, c.icon as category_icon 
                FROM {$this->table} t 
                LEFT JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = :user_id";

        $params = [':user_id' => $userId];

        // Filter berdasarkan tipe (income/expense)
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $params[':type'] = $filters['type'];
        }

        // Filter berdasarkan kategori
        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        // Filter berdasarkan tanggal mulai
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.transaction_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        // Filter berdasarkan tanggal akhir
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.transaction_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        // Filter pencarian (di deskripsi)
        if (!empty($filters['search'])) {
            $sql .= " AND t.description LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Urutkan berdasarkan tanggal terbaru
        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";

        return $this->query($sql, $params);
    }

    /**
     * Mengambil data bulanan untuk chart
     * 
     * Mengembalikan total pemasukan dan pengeluaran per bulan
     * untuk tahun yang ditentukan.
     * 
     * @param int      $userId ID user
     * @param int|null $year   Tahun (default: tahun ini)
     * @return array Array data bulanan
     */
    public function getMonthlyData(int $userId, ?int $year = null): array
    {
        // Gunakan tahun sekarang jika tidak ditentukan
        if ($year === null) {
            $year = (int) date('Y');
        }

        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $sql = "SELECT 
                        CAST(strftime('%m', transaction_date) as INTEGER) as month,
                        type,
                        SUM(amount) as total
                    FROM {$this->table}
                    WHERE user_id = :user_id 
                        AND CAST(strftime('%Y', transaction_date) as INTEGER) = :year
                    GROUP BY strftime('%m', transaction_date), type
                    ORDER BY strftime('%m', transaction_date) ASC";
        } else {
            $sql = "SELECT 
                        MONTH(transaction_date) as month,
                        type,
                        SUM(amount) as total
                    FROM {$this->table}
                    WHERE user_id = :user_id 
                        AND YEAR(transaction_date) = :year
                    GROUP BY MONTH(transaction_date), type
                    ORDER BY MONTH(transaction_date) ASC";
        }

        $results = $this->query($sql, [
            ':user_id' => $userId,
            ':year'    => (int)$year,
        ]);

        // Format data menjadi array per bulan (1-12)
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[$i] = [
                'month'   => $i,
                'income'  => 0,
                'expense' => 0,
            ];
        }

        foreach ($results as $row) {
            $month = (int) $row->month;
            $monthlyData[$month][$row->type] = (float) $row->total;
        }

        return array_values($monthlyData);
    }

    /**
     * Mengambil total jumlah berdasarkan tipe
     * 
     * @param int    $userId ID user
     * @param string $type   Tipe transaksi (income/expense)
     * @return float Total amount
     */
    public function getTotalByType(int $userId, string $type): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM {$this->table} 
                WHERE user_id = :user_id AND type = :type";

        $result = $this->queryOne($sql, [
            ':user_id' => $userId,
            ':type'    => $type,
        ]);

        return $result ? (float) $result->total : 0.0;
    }

    /**
     * Mengambil transaksi terbaru
     * 
     * @param int $userId ID user
     * @param int $limit  Jumlah maksimal transaksi
     * @return array Array of objects transaksi terbaru
     */
    public function getRecent(int $userId, int $limit = 5): array
    {
        $sql = "SELECT t.*, c.name as category_name, c.color as category_color, c.icon as category_icon
                FROM {$this->table} t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = :user_id
                ORDER BY t.transaction_date DESC, t.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Membuat transaksi dengan upload bukti (receipt)
     * 
     * Menangani upload file gambar bukti transaksi:
     * - Validasi tipe file (JPG/PNG)
     * - Validasi ukuran (max 2MB)
     * - Generate nama unik
     * - Pindahkan ke UPLOAD_PATH
     * 
     * @param array      $data Data transaksi
     * @param array|null $file Data file upload ($_FILES['receipt_photo'])
     * @return string|false ID transaksi baru atau false
     */
    public function createWithReceipt(array $data, ?array $file = null): string|false
    {
        // Jika ada file yang di-upload
        if ($file && !empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
            // Validasi tipe file (hanya JPG dan PNG)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $fileType = mime_content_type($file['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                return false; // Tipe file tidak diizinkan
            }

            // Validasi ukuran file (max 2MB)
            $maxSize = 2 * 1024 * 1024; // 2MB dalam bytes
            if ($file['size'] > $maxSize) {
                return false; // File terlalu besar
            }

            // Generate nama file unik
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueName = uniqid('receipt_', true) . '.' . strtolower($extension);

            // Pastikan direktori upload ada
            if (!is_dir(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0755, true);
            }

            // Pindahkan file ke direktori upload
            $destination = UPLOAD_PATH . '/' . $uniqueName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $data['receipt_photo'] = $uniqueName;
            } else {
                return false; // Gagal memindahkan file
            }
        }

        // Buat record transaksi
        return $this->create($data);
    }
}
