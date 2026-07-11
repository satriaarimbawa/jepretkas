<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
    
    <!-- Form Tambah Kategori -->
    <div class="glass-card">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-glass); padding-bottom: 12px;">
            <span class="material-icons" style="color: var(--primary);">playlist_add</span>
            <h3 style="font-size: 16px; font-weight: 600; color: #fff; margin: 0;">Tambah Kategori Baru</h3>
        </div>

        <form action="<?= BASE_URL ?>/categories/store" method="POST">
            <div class="form-group">
                <label for="name" class="form-label">Nama Kategori</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Transportasi, Bonus..." required>
            </div>

            <div class="form-group">
                <label for="type" class="form-label">Tipe Kategori</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="expense">Pengeluaran (Cash Out)</option>
                    <option value="income">Pemasukan (Cash In)</option>
                </select>
            </div>

            <div class="form-group" id="fixed-expense-group">
                <label class="form-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; margin-bottom: 0;">
                    <input type="checkbox" id="is_fixed" name="is_fixed" value="1" style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="font-size: 14px; font-weight: 500; color: #fff;">Kategori Pengeluaran Tetap (Wajib)</span>
                </label>
                <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-top: 6px; margin-left: 26px; line-height: 1.4;">Centang jika kategori ini berupa tagihan rutin/wajib bulanan yang tidak bisa dihemat secara mudah (seperti bayar kost, listrik, air).</span>
            </div>

            <div class="form-group">
                <label for="color" class="form-label">Warna Penanda</label>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="color" id="color" name="color" class="form-control" value="#6366f1" style="width: 60px; height: 42px; padding: 2px; cursor: pointer; border: none; background: transparent;">
                    <span style="font-size: 13px; color: var(--text-secondary);">Pilih warna representasi untuk grafik</span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                <span class="material-icons">save</span>
                <span>Simpan Kategori</span>
            </button>
        </form>
    </div>

    <!-- Grid Kategori -->
    <div class="glass-card">
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 600; color: #fff; margin: 0;">Daftar Kategori Anda</h3>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">Kategori yang Anda gunakan untuk mengklasifikasikan pengeluaran dan pemasukan</p>
        </div>

        <div class="categories-grid">
            <?php if (empty($categories)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-secondary);">
                    <span class="material-icons" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 12px;">category</span>
                    Belum ada kategori yang dibuat.
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="glass-card category-card" style="padding: 16px; border-left: 4px solid <?= htmlspecialchars($cat->color) ?>;">
                        <div class="category-left">
                            <div class="category-icon-bg" style="background-color: <?= htmlspecialchars($cat->color) ?>15; color: <?= htmlspecialchars($cat->color) ?>;">
                                <span class="material-icons">
                                    <?= $cat->type === 'income' ? 'arrow_downward' : 'arrow_upward' ?>
                                </span>
                            </div>
                            <div class="category-details">
                                <h4><?= htmlspecialchars($cat->name) ?></h4>
                                <span class="badge <?= $cat->type === 'income' ? 'badge-income' : 'badge-expense' ?>" style="font-size: 10px; padding: 2px 8px; margin-top: 4px; display: inline-flex;">
                                    <?= $cat->type === 'income' ? 'Masuk' : 'Keluar' ?>
                                </span>
                                <?php if ($cat->type === 'expense'): ?>
                                    <span class="badge" style="font-size: 10px; padding: 2px 8px; margin-top: 4px; display: inline-flex; background-color: <?= $cat->is_fixed ? 'rgba(6, 182, 212, 0.1)' : 'rgba(255,255,255,0.03)' ?>; border: 1px solid <?= $cat->is_fixed ? 'var(--accent)' : 'var(--border-glass)' ?>; color: <?= $cat->is_fixed ? 'var(--accent)' : 'var(--text-secondary)' ?>;">
                                        <?= $cat->is_fixed ? 'Tetap (Wajib)' : 'Opsional (Variabel)' ?>
                                    </span>
                                <?php endif; ?>
                                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                    <?= $cat->transaction_count ?> transaksi terkait
                                </div>
                            </div>
                        </div>

                        <div class="category-actions" style="display: flex; gap: 12px; align-items: center;">
                            <button type="button" class="category-action-btn edit-category-btn" 
                                    data-id="<?= $cat->id ?>" 
                                    data-name="<?= htmlspecialchars($cat->name) ?>" 
                                    data-type="<?= $cat->type ?>" 
                                    data-color="<?= $cat->color ?>" 
                                    data-fixed="<?= $cat->is_fixed ?>" 
                                    title="Edit Kategori">
                                <span class="material-icons" style="font-size: 18px; color: var(--accent);">edit</span>
                            </button>
                            <a href="<?= BASE_URL ?>/categories/delete/<?= $cat->id ?>" class="category-action-btn" title="Hapus Kategori" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Kategori pada transaksi yang sudah ada akan diubah menjadi kosong (Set Null).')">
                                <span class="material-icons" style="font-size: 18px; color: var(--danger);">delete</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal-overlay" id="edit-category-modal">
    <div class="glass-card" style="width: 100%; max-width: 450px; padding: 24px; position: relative;">
        <button type="button" class="modal-close" id="edit-modal-close" style="top: 15px; right: 15px; position: absolute;">
            <span class="material-icons">close</span>
        </button>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-glass); padding-bottom: 12px;">
            <span class="material-icons" style="color: var(--accent);">edit</span>
            <h3 style="font-size: 16px; font-weight: 600; color: #fff; margin: 0;">Edit Kategori</h3>
        </div>
        <form id="edit-category-form" action="" method="POST">
            <div class="form-group">
                <label for="edit-name" class="form-label">Nama Kategori</label>
                <input type="text" id="edit-name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-type" class="form-label">Tipe Kategori</label>
                <select id="edit-type" name="type" class="form-control" required>
                    <option value="expense">Pengeluaran (Cash Out)</option>
                    <option value="income">Pemasukan (Cash In)</option>
                </select>
            </div>
            <div class="form-group" id="edit-fixed-expense-group">
                <label class="form-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="edit-is-fixed" name="is_fixed" value="1" style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="font-size: 14px; font-weight: 500; color: #fff;">Kategori Pengeluaran Tetap (Wajib)</span>
                </label>
            </div>
            <div class="form-group">
                <label for="edit-color" class="form-label">Warna Penanda</label>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="color" id="edit-color" name="color" class="form-control" style="width: 60px; height: 42px; padding: 2px; cursor: pointer; border: none; background: transparent;">
                    <span style="font-size: 13px; color: var(--text-secondary);">Warna grafik representasi</span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; background: linear-gradient(135deg, var(--accent), #6366f1); border: none;">
                <span class="material-icons">save</span>
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>
</div>

<style>
@media (max-width: 1024px) {
    div[style*="grid-template-columns: 1fr 2fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
