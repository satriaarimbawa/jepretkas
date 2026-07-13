<?php
/**
 * Account Model
 * 
 * Model untuk mengelola data rekening/dompet/penyimpanan uang.
 * Menghitung saldo secara dinamis berdasarkan arus transaksi.
 */
class Account extends Model
{
    /** @var string Nama tabel */
    protected string $table = 'accounts';

    /**
     * Mengambil semua rekening milik user beserta saldonya
     * 
     * Saldo dihitung dinamis dari total pemasukan dikurangi pengeluaran.
     * 
     * @param int $userId ID user
     * @return array Array objek rekening beserta properti 'balance'
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT a.*, 
                (
                    COALESCE((SELECT SUM(t.amount) FROM transactions t WHERE t.account_id = a.id AND t.type = 'income'), 0) -
                    COALESCE((SELECT SUM(t.amount) FROM transactions t WHERE t.account_id = a.id AND t.type = 'expense'), 0)
                ) as balance
                FROM {$this->table} a
                WHERE a.user_id = :user_id
                ORDER BY a.name ASC";

        return $this->query($sql, [':user_id' => $userId]);
    }

    /**
     * Mengambil detail rekening berdasarkan ID dan verifikasi kepemilikan user
     * 
     * @param int $id ID rekening
     * @param int $userId ID user
     * @return object|false Objek rekening atau false
     */
    public function findByIdAndUser(int $id, int $userId): object|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND user_id = :user_id LIMIT 1";
        return $this->queryOne($sql, [
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }
}
