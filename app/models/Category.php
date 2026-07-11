<?php
/**
 * Category Model
 * 
 * Model untuk mengelola kategori transaksi.
 * Setiap user memiliki kategori masing-masing.
 */
class Category extends Model
{
    /** @var string Nama tabel */
    protected string $table = 'categories';

    /**
     * Mengambil semua kategori milik user
     * 
     * @param int $userId ID user
     * @return array Array of objects kategori, diurutkan berdasarkan nama
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY name ASC";

        return $this->query($sql, [':user_id' => $userId]);
    }

    /**
     * Mengambil kategori berdasarkan user dan tipe
     * 
     * @param int    $userId ID user
     * @param string $type   Tipe kategori (income/expense)
     * @return array Array of objects kategori
     */
    public function getByUserAndType(int $userId, string $type): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND type = :type 
                ORDER BY name ASC";

        return $this->query($sql, [
            ':user_id' => $userId,
            ':type'    => $type,
        ]);
    }

    /**
     * Mengambil kategori beserta jumlah transaksi
     * 
     * Menggunakan LEFT JOIN untuk menghitung jumlah transaksi
     * yang terkait dengan setiap kategori.
     * 
     * @param int $userId ID user
     * @return array Array of objects kategori dengan transaction_count
     */
    public function getWithTransactionCount(int $userId): array
    {
        $sql = "SELECT c.*, COUNT(t.id) as transaction_count
                FROM {$this->table} c
                LEFT JOIN transactions t ON c.id = t.category_id
                WHERE c.user_id = :user_id
                GROUP BY c.id, c.user_id, c.name, c.type, c.color, c.icon, c.created_at
                ORDER BY c.name ASC";

        return $this->query($sql, [':user_id' => $userId]);
    }

    /**
     * Membuat kategori default untuk user baru
     * 
     * Kategori default meliputi:
     * - Operasional (expense)
     * - Konsumsi (expense)
     * - Transportasi (expense)
     * - Gaji (income)
     * - Lainnya (expense)
     * 
     * @param int $userId ID user
     * @return void
     */
    public function createDefault(int $userId): void
    {
        $defaultCategories = [
            [
                'user_id'  => $userId,
                'name'     => 'Sewa Rumah / Kost',
                'type'     => 'expense',
                'color'    => '#a855f7',
                'icon'     => 'home',
                'is_fixed' => 1,
            ],
            [
                'user_id'  => $userId,
                'name'     => 'Tagihan (Listrik/Air)',
                'type'     => 'expense',
                'color'    => '#06b6d4',
                'icon'     => 'water',
                'is_fixed' => 1,
            ],
            [
                'user_id'  => $userId,
                'name'     => 'Operasional',
                'type'     => 'expense',
                'color'    => '#ef4444',
                'icon'     => 'briefcase',
                'is_fixed' => 1,
            ],
            [
                'user_id'  => $userId,
                'name'     => 'Konsumsi',
                'type'     => 'expense',
                'color'    => '#f97316',
                'icon'     => 'utensils',
                'is_fixed' => 0,
            ],
            [
                'user_id'  => $userId,
                'name'     => 'Transportasi',
                'type'     => 'expense',
                'color'    => '#eab308',
                'icon'     => 'car',
                'is_fixed' => 0,
            ],
            [
                'user_id'  => $userId,
                'name'     => 'Gaji',
                'type'     => 'income',
                'color'    => '#22c55e',
                'icon'     => 'wallet',
                'is_fixed' => 0,
            ],
            [
                'user_id'  => $userId,
                'name'     => 'Lainnya',
                'type'     => 'expense',
                'color'    => '#6366f1',
                'icon'     => 'folder',
                'is_fixed' => 0,
            ],
        ];

        // Insert setiap kategori default
        foreach ($defaultCategories as $category) {
            $this->create($category);
        }
    }
}
