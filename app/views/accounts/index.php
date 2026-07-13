<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
    
    <!-- Form Tambah Rekening -->
    <div class="glass-card">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-glass); padding-bottom: 12px;">
            <span class="material-icons" style="color: var(--primary);">playlist_add</span>
            <h3 style="font-size: 16px; font-weight: 600; color: #fff; margin: 0;">Tambah Rekening Baru</h3>
        </div>

        <form action="<?= BASE_URL ?>/accounts/store" method="POST">
            <div class="form-group">
                <label for="name" class="form-label">Nama Rekening / Penyimpanan</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Bank BCA, Mandiri, Cash..." required>
            </div>

            <div class="form-group">
                <label for="icon" class="form-label">Ikon Rekening</label>
                <select id="icon" name="icon" class="form-control" required>
                    <option value="account_balance_wallet" selected>Dompet / E-Wallet</option>
                    <option value="account_balance">Bank / Rekening Utama</option>
                    <option value="credit_card">Kartu Kredit / Debit</option>
                    <option value="payments">Uang Tunai / Cash</option>
                    <option value="wallet">Dompet Kecil</option>
                    <option value="savings">Celengan / Tabungan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="type" class="form-label">Tipe Rekening / Penyimpanan</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="debit" selected>Tabungan / Dompet / Kas (Saldo Positif)</option>
                    <option value="credit">PayLater / Kredit (Utang/Liabilitas)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="color" class="form-label">Warna Representasi</label>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="color" id="color" name="color" class="form-control" value="#6366f1" style="width: 60px; height: 42px; padding: 2px; cursor: pointer; border: none; background: transparent;">
                    <span style="font-size: 13px; color: var(--text-secondary);">Warna visual untuk membedakan saldo</span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                <span class="material-icons">save</span>
                <span>Simpan Rekening</span>
            </button>
        </form>
    </div>

    <!-- Grid Rekening -->
    <div class="glass-card">
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 600; color: #fff; margin: 0;">Daftar Rekening & Dompet Anda</h3>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">Penyimpanan terpisah untuk mengelola alokasi saldo keuangan Anda</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
            <?php if (empty($accounts)): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <span class="material-icons" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 12px;">account_balance_wallet</span>
                    Belum ada rekening yang dibuat.
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $acc): ?>
                    <div class="glass-card" style="padding: 16px; border-left: 4px solid <?= htmlspecialchars($acc->color) ?>; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="background-color: <?= htmlspecialchars($acc->color) ?>15; color: <?= htmlspecialchars($acc->color) ?>; width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <span class="material-icons">
                                    <?= htmlspecialchars($acc->icon ?: 'account_balance_wallet') ?>
                                </span>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <h4 style="font-size: 15px; font-weight: 600; color: #fff; margin: 0;"><?= htmlspecialchars($acc->name) ?></h4>
                                    <?php if (($acc->type ?? 'debit') === 'credit'): ?>
                                        <span class="badge" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 500;">PayLater / Kredit</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 500;">Tabungan / Dompet</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 16px; font-weight: 700; color: <?= $acc->balance >= 0 ? 'var(--text-primary)' : 'var(--danger)' ?>; margin-top: 4px;">
                                    <?= (($acc->type ?? 'debit') === 'credit' && $acc->balance < 0) ? 'Tagihan:' : 'Saldo:' ?> Rp <?= number_format($acc->balance, 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 12px; align-items: center;">
                            <button type="button" class="btn btn-secondary btn-sm edit-account-btn" 
                                    data-id="<?= $acc->id ?>" 
                                    data-name="<?= htmlspecialchars($acc->name) ?>" 
                                    data-icon="<?= htmlspecialchars($acc->icon) ?>" 
                                    data-color="<?= htmlspecialchars($acc->color) ?>" 
                                    data-type="<?= htmlspecialchars($acc->type ?? 'debit') ?>" 
                                    style="padding: 6px 10px;" title="Edit Rekening">
                                <span class="material-icons" style="font-size: 18px; color: var(--accent);">edit</span>
                            </button>
                            <a href="<?= BASE_URL ?>/accounts/delete/<?= $acc->id ?>" class="btn btn-danger btn-sm" title="Hapus Rekening" onclick="return confirm('Apakah Anda yakin ingin menghapus rekening ini? Transaksi terkait rekening ini akan memiliki nilai kosong (NULL), tetapi tidak dihapus.')" style="padding: 6px 10px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
                                <span class="material-icons" style="font-size: 18px; color: var(--danger);">delete</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Edit Rekening -->
<div class="modal-overlay" id="edit-account-modal">
    <div class="glass-card" style="width: 100%; max-width: 450px; padding: 24px; position: relative;">
        <button type="button" class="modal-close" id="edit-modal-close" style="top: 15px; right: 15px; position: absolute;">
            <span class="material-icons">close</span>
        </button>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border-glass); padding-bottom: 12px;">
            <span class="material-icons" style="color: var(--accent);">edit</span>
            <h3 style="font-size: 16px; font-weight: 600; color: #fff; margin: 0;">Edit Rekening</h3>
        </div>
        <form id="edit-account-form" action="" method="POST">
            <div class="form-group">
                <label for="edit-name" class="form-label">Nama Rekening / Penyimpanan</label>
                <input type="text" id="edit-name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit-icon" class="form-label">Ikon Rekening</label>
                <select id="edit-icon" name="icon" class="form-control" required>
                    <option value="account_balance_wallet">Dompet / E-Wallet</option>
                    <option value="account_balance">Bank / Rekening Utama</option>
                    <option value="credit_card">Kartu Kredit / Debit</option>
                    <option value="payments">Uang Tunai / Cash</option>
                    <option value="wallet">Dompet Kecil</option>
                    <option value="savings">Celengan / Tabungan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit-type" class="form-label">Tipe Rekening / Penyimpanan</label>
                <select id="edit-type" name="type" class="form-control" required>
                    <option value="debit">Tabungan / Dompet / Kas (Saldo Positif)</option>
                    <option value="credit">PayLater / Kredit (Utang/Liabilitas)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit-color" class="form-label">Warna Representasi</label>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="color" id="edit-color" name="color" class="form-control" style="width: 60px; height: 42px; padding: 2px; cursor: pointer; border: none; background: transparent;">
                    <span style="font-size: 13px; color: var(--text-secondary);">Warna visual baru</span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; background: linear-gradient(135deg, var(--accent), #6366f1); border: none;">
                <span class="material-icons">save</span>
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editAccountBtns = document.querySelectorAll('.edit-account-btn');
    const editAccountModal = document.getElementById('edit-account-modal');
    const editModalClose = document.getElementById('edit-modal-close');
    const editAccountForm = document.getElementById('edit-account-form');

    if (editAccountBtns.length > 0 && editAccountModal && editAccountForm && editModalClose) {
        const editNameInput = document.getElementById('edit-name');
        const editIconSelect = document.getElementById('edit-icon');
        const editColorInput = document.getElementById('edit-color');
        const editTypeSelect = document.getElementById('edit-type');

        editAccountBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const icon = this.getAttribute('data-icon');
                const color = this.getAttribute('data-color');
                const type = this.getAttribute('data-type') || 'debit';

                // Populate modal
                editNameInput.value = name;
                editIconSelect.value = icon;
                editColorInput.value = color;
                editTypeSelect.value = type;

                // Set dynamic action
                editAccountForm.action = '<?= BASE_URL ?>/accounts/update/' + id;

                // Show modal
                editAccountModal.classList.add('active');
            });
        });

        // Close modal actions
        const closeEditModal = function() {
            editAccountModal.classList.remove('active');
        };

        editModalClose.addEventListener('click', closeEditModal);
        editAccountModal.addEventListener('click', function(e) {
            if (e.target === editAccountModal) {
                closeEditModal();
            }
        });
    }
});
</script>

<style>
@media (max-width: 1024px) {
    div[style*="grid-template-columns: 1fr 2fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
