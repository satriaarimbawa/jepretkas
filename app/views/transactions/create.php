<div class="glass-card" style="max-width: 700px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--border-glass); padding-bottom: 16px;">
        <span class="material-icons" style="font-size: 28px; color: var(--primary);">add_circle</span>
        <h3 style="font-size: 18px; font-weight: 600; color: #fff; margin: 0;">Tambah Transaksi Baru</h3>
    </div>

    <form action="<?= BASE_URL ?>/transactions/store" method="POST" enctype="multipart/form-data">
        
        <!-- Toggle Tipe Transaksi -->
        <div class="form-group">
            <label class="form-label">Tipe Transaksi</label>
            <div class="type-toggle-wrapper">
                <div class="type-toggle-btn active" data-type="expense">Pengeluaran</div>
                <div class="type-toggle-btn" data-type="income">Pemasukan</div>
            </div>
            <input type="hidden" name="type" id="transaction-type" value="expense">
        </div>

        <!-- Input Nominal -->
        <div class="form-group">
            <label for="amount-input" class="form-label">Nominal (Rp)</label>
            <div style="position: relative; display: flex; align-items: center;">
                <span style="position: absolute; left: 16px; font-weight: 600; color: var(--text-secondary);">Rp</span>
                <input type="text" id="amount-input" name="amount" class="form-control" placeholder="0" required style="padding-left: 45px; font-size: 18px; font-weight: 700; color: #fff;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
            <!-- Tanggal -->
            <div class="form-group">
                <label for="transaction_date" class="form-label">Tanggal</label>
                <input type="date" id="transaction_date" name="transaction_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>

            <!-- Rekening -->
            <div class="form-group">
                <label for="account-id" class="form-label">Penyimpanan / Rekening</label>
                <select id="account-id" name="account_id" class="form-control" required>
                    <option value="" disabled selected>Pilih Rekening</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc->id ?>">
                            <?= htmlspecialchars($acc->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kategori -->
            <div class="form-group">
                <label for="category-id" class="form-label">Kategori</label>
                <select id="category-id" name="category_id" class="form-control" required>
                    <option value="" disabled selected>Pilih Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>" data-type="<?= htmlspecialchars($cat->type) ?>">
                            <?= htmlspecialchars($cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="form-group">
            <label for="description" class="form-label">Keterangan / Deskripsi</label>
            <textarea id="description" name="description" class="form-control" placeholder="Contoh: Beli makan siang, Gaji bulanan, dll." rows="3"></textarea>
        </div>

        <!-- Upload Foto Struk -->
        <div class="form-group">
            <label class="form-label">Upload Bukti Foto Struk / Nota (Opsional untuk Pemasukan, Sangat Disarankan untuk Pengeluaran)</label>
            
            <div class="upload-zone" id="upload-zone">
                <span class="material-icons upload-icon">photo_camera</span>
                <div>
                    <p style="font-weight: 600; font-size: 14px; margin-bottom: 4px;">Klik untuk mengambil foto atau pilih file</p>
                    <p style="font-size: 12px; color: var(--text-secondary);">Mendukung seret & taruh file (JPG, PNG maks 2MB)</p>
                </div>
                <input type="file" id="receipt-photo" name="receipt_photo" accept="image/png, image/jpeg, image/jpg" style="display: none;">
            </div>

            <!-- Image Preview Area -->
            <div class="image-preview-container" id="preview-container">
                <button type="button" class="remove-preview" id="remove-preview">
                    <span class="material-icons">close</span>
                </button>
                <img id="preview-img" src="" alt="Pratinjau Foto">
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px;">
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-icons">save</span>
                <span>Simpan Transaksi</span>
            </button>
        </div>
    </form>
</div>
