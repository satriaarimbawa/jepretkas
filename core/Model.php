<?php
/**
 * Base Model
 * 
 * Kelas dasar untuk semua model dalam aplikasi.
 * Menyediakan method CRUD dan query helper yang umum digunakan.
 * Semua query menggunakan prepared statements untuk keamanan.
 */
class Model
{
    /** @var PDO Instance koneksi database */
    protected PDO $db;

    /** @var string Nama tabel yang digunakan model ini */
    protected string $table = '';

    /**
     * Constructor
     * 
     * Mengambil koneksi PDO dari Database singleton.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Mengambil semua data dari tabel
     * 
     * @param array  $conditions Kondisi WHERE dalam format [kolom => nilai]
     * @param string $orderBy    Pengurutan data (default: 'id DESC')
     * @return array Array of objects
     */
    public function findAll(array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        // Bangun klausa WHERE jika ada kondisi
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Tambahkan ORDER BY
        $sql .= " ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Mencari data berdasarkan ID
     * 
     * @param int $id ID record
     * @return object|false Object data atau false jika tidak ditemukan
     */
    public function findById(int $id): object|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Membuat record baru
     * 
     * @param array $data Data dalam format [kolom => nilai]
     * @return string|false ID record yang baru dibuat
     */
    public function create(array $data): string|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $params = [];
        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->db->lastInsertId();
    }

    /**
     * Memperbarui record berdasarkan ID
     * 
     * @param int   $id   ID record yang akan diperbarui
     * @param array $data Data dalam format [kolom => nilai]
     * @return bool True jika berhasil
     */
    public function update(int $id, array $data): bool
    {
        $setClauses = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Menghapus record berdasarkan ID
     * 
     * @param int $id ID record yang akan dihapus
     * @return bool True jika berhasil
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Menjalankan query SQL mentah (multiple rows)
     * 
     * @param string $sql    Query SQL dengan placeholder
     * @param array  $params Parameter untuk prepared statement
     * @return array Array of objects
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Menjalankan query SQL mentah (single row)
     * 
     * @param string $sql    Query SQL dengan placeholder
     * @param array  $params Parameter untuk prepared statement
     * @return object|false Object data atau false
     */
    public function queryOne(string $sql, array $params = []): object|false
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch();
    }

    /**
     * Menghitung jumlah record
     * 
     * @param array $conditions Kondisi WHERE dalam format [kolom => nilai]
     * @return int Jumlah record
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return (int) $result->total;
    }
}
