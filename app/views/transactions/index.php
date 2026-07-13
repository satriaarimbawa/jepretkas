<?php $filter = $filters; ?>
<div class="glass-card" style="margin-bottom: 24px;">
    <!-- Filter Form -->
    <form action="<?= BASE_URL ?>/transactions" method="GET">
        <div class="filter-bar">
            <!-- Pencarian -->
            <div class="filter-group">
                <label for="search" class="form-label" style="font-size: 12px;">Cari Keterangan</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="Kata kunci..." value="<?= htmlspecialchars($filter['search'] ?? '') ?>">
            </div>

            <!-- Tipe -->
            <div class="filter-group" style="max-width: 150px;">
                <label for="type" class="form-label" style="font-size: 12px;">Tipe</label>
                <select id="type" name="type" class="form-control">
                    <option value="">Semua</option>
                    <option value="income" <?= ($filter['type'] ?? '') === 'income' ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="expense" <?= ($filter['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>

            <!-- Kategori -->
            <div class="filter-group">
                <label for="category_id_filter" class="form-label" style="font-size: 12px;">Kategori</label>
                <select id="category_id_filter" name="category_id" class="form-control">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= (string)($filter['category_id'] ?? '') === (string)$cat->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat->name) ?> (<?= $cat->type === 'income' ? 'Masuk' : 'Keluar' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Rekening -->
            <div class="filter-group" style="min-width: 150px;">
                <label for="account_id_filter" class="form-label" style="font-size: 12px;">Rekening</label>
                <select id="account_id_filter" name="account_id" class="form-control">
                    <option value="">Semua Rekening</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc->id ?>" <?= (string)($filter['account_id'] ?? '') === (string)$acc->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($acc->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div class="filter-group" style="max-width: 150px;">
                <label for="date_from" class="form-label" style="font-size: 12px;">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="<?= htmlspecialchars($filter['date_from'] ?? '') ?>">
            </div>

            <!-- Sampai Tanggal -->
            <div class="filter-group" style="max-width: 150px;">
                <label for="date_to" class="form-label" style="font-size: 12px;">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="<?= htmlspecialchars($filter['date_to'] ?? '') ?>">
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 20px;">
                    <span class="material-icons">filter_alt</span>
                </button>
                <a href="<?= BASE_URL ?>/transactions" class="btn btn-secondary" style="padding: 12px 20px;" title="Reset Filter">
                    <span class="material-icons">restart_alt</span>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Transactions List Card -->
<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 18px; font-weight: 600; color: #fff;">Daftar Transaksi</h3>
        <a href="<?= BASE_URL ?>/transactions/create" class="btn btn-primary btn-sm">
            <span class="material-icons" style="font-size: 18px;">add</span>
            <span>Tambah</span>
        </a>
    </div>

    <!-- Desktop Table View -->
    <div class="desktop-table-view table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kategori</th>
                    <th>Rekening</th>
                    <th style="text-align: right;">Nominal</th>
                    <th style="text-align: center;">Bukti Struk</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            <span class="material-icons" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 12px;">search_off</span>
                            Tidak ada transaksi yang cocok dengan filter pencarian.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td style="white-space: nowrap; color: var(--text-secondary);">
                                <?= date('d M Y', strtotime($tx->transaction_date)) ?>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: #fff;"><?= htmlspecialchars($tx->description) ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); color: #fff; display: inline-flex; align-items: center; gap: 8px;">
                                    <span class="category-dot" style="background-color: <?= htmlspecialchars($tx->category_color ?? '#6366f1') ?>"></span>
                                    <?= htmlspecialchars($tx->category_name ?? 'Lainnya') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background-color: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); color: #fff; display: inline-flex; align-items: center; gap: 8px;">
                                    <span class="material-icons" style="font-size: 14px; color: <?= htmlspecialchars($tx->account_color ?? '#6366f1') ?>; vertical-align: middle;">
                                        <?= htmlspecialchars($tx->account_icon ?: 'account_balance_wallet') ?>
                                    </span>
                                    <?= htmlspecialchars($tx->account_name ?? 'Dompet Utama') ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; white-space: nowrap; color: <?= $tx->type === 'income' ? 'var(--success)' : 'var(--danger)' ?>">
                                <?= $tx->type === 'income' ? '+' : '-' ?> Rp <?= number_format($tx->amount, 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($tx->receipt_photo): ?>
                                    <button class="view-receipt-btn btn btn-secondary btn-sm" data-src="<?= BASE_URL ?>/transactions/viewReceipt/<?= $tx->id ?>" data-title="<?= htmlspecialchars($tx->description) ?>" style="padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; color: var(--accent); border-color: rgba(6, 182, 212, 0.2);">
                                        <span class="material-icons" style="font-size: 16px;">image</span>
                                        <span>Lihat</span>
                                    </button>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary); font-size: 12px; font-style: italic;">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <a href="<?= BASE_URL ?>/transactions/delete/<?= $tx->id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Bukti foto struk terkait juga akan dihapus permanen.')" style="padding: 6px 10px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;">
                                    <span class="material-icons" style="font-size: 16px;">delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards View -->
    <div class="mobile-cards-view">
        <?php if (empty($transactions)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-secondary); background: rgba(255,255,255,0.01); border-radius: 12px; border: 1px dashed var(--border-glass);">
                <span class="material-icons" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 12px;">search_off</span>
                Tidak ada transaksi yang cocok.
            </div>
        <?php else: ?>
            <?php foreach ($transactions as $tx): ?>
                <div class="transaction-mobile-card">
                    <div class="tx-card-header">
                        <span class="tx-card-date"><?= date('d M Y', strtotime($tx->transaction_date)) ?></span>
                        <div style="display: flex; gap: 6px;">
                            <span class="badge" style="background-color: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); color: #fff; display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                                <span class="category-dot" style="background-color: <?= htmlspecialchars($tx->category_color ?? '#6366f1') ?>"></span>
                                <?= htmlspecialchars($tx->category_name ?? 'Lainnya') ?>
                            </span>
                            <span class="badge" style="background-color: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); color: #fff; display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                                <span class="material-icons" style="font-size: 12px; color: <?= htmlspecialchars($tx->account_color ?? '#6366f1') ?>; vertical-align: middle;">
                                    <?= htmlspecialchars($tx->account_icon ?: 'account_balance_wallet') ?>
                                </span>
                                <?= htmlspecialchars($tx->account_name ?? 'Dompet Utama') ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="tx-card-body">
                        <div class="tx-card-title"><?= htmlspecialchars($tx->description) ?></div>
                        <div class="tx-card-amount <?= $tx->type === 'income' ? 'income' : 'expense' ?>">
                            <?= $tx->type === 'income' ? '+' : '-' ?> Rp <?= number_format($tx->amount, 0, ',', '.') ?>
                        </div>
                    </div>
                    
                    <div class="tx-card-footer">
                        <div class="tx-card-receipt">
                            <?php if ($tx->receipt_photo): ?>
                                <button class="view-receipt-btn btn btn-secondary btn-sm" data-src="<?= BASE_URL ?>/transactions/viewReceipt/<?= $tx->id ?>" data-title="<?= htmlspecialchars($tx->description) ?>">
                                    <span class="material-icons" style="font-size: 16px;">image</span>
                                    <span>Lihat Struk</span>
                                </button>
                            <?php else: ?>
                                <span class="no-receipt">
                                    <span class="material-icons" style="font-size: 16px; vertical-align: middle; margin-right: 4px; opacity: 0.5;">image_not_supported</span>
                                    Tanpa Struk
                                </span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= BASE_URL ?>/transactions/delete/<?= $tx->id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Bukti foto struk terkait juga akan dihapus permanen.')" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; border-radius: 6px;">
                            <span class="material-icons" style="font-size: 14px;">delete</span>
                            <span>Hapus</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
