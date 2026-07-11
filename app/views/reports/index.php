<!-- Filter & Summary Section -->
<div class="glass-card" style="margin-bottom: 24px;">
    
    <!-- Filter Form -->
    <form action="<?= BASE_URL ?>/reports" method="GET" style="margin-bottom: 24px;">
        <div class="filter-bar">
            <!-- Dari Tanggal -->
            <div class="filter-group">
                <label for="date_from" class="form-label" style="font-size: 12px;">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="<?= htmlspecialchars($filter['date_from'] ?? '') ?>">
            </div>

            <!-- Sampai Tanggal -->
            <div class="filter-group">
                <label for="date_to" class="form-label" style="font-size: 12px;">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="<?= htmlspecialchars($filter['date_to'] ?? '') ?>">
            </div>

            <!-- Tipe -->
            <div class="filter-group" style="max-width: 150px;">
                <label for="type" class="form-label" style="font-size: 12px;">Tipe</label>
                <select id="type" name="type" class="form-control">
                    <option value="">Semua</option>
                    <option value="income" <?= ($filter['type'] ?? '') === 'income' ? 'selected' : '' ?>>Pemasukan Only</option>
                    <option value="expense" <?= ($filter['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Pengeluaran Only</option>
                </select>
            </div>

            <!-- Kategori -->
            <div class="filter-group">
                <label for="category_id" class="form-label" style="font-size: 12px;">Kategori</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= (string)($filter['category_id'] ?? '') === (string)$cat->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat->name) ?> (<?= $cat->type === 'income' ? 'Masuk' : 'Keluar' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 20px;">
                    <span class="material-icons">filter_alt</span>
                    <span>Filter</span>
                </button>
                
                <!-- CSV Export Button -->
                <a href="<?= BASE_URL ?>/reports/export?<?= http_build_query($filter) ?>" class="btn btn-secondary" style="padding: 12px 20px; border-color: var(--success); color: var(--success); display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-icons">download</span>
                    <span>Ekspor CSV</span>
                </a>
            </div>
        </div>
    </form>

    <!-- Summary Statistics Grid -->
    <div class="stats-grid" style="border-top: 1px solid var(--border-glass); padding-top: 24px;">
        <!-- Pemasukan -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: var(--success); display: flex; align-items: center; justify-content: center;">
                <span class="material-icons">trending_up</span>
            </div>
            <div>
                <p style="font-size: 12px; color: var(--text-secondary);">Total Pendapatan</p>
                <h4 style="font-size: 18px; font-weight: 700; color: var(--success); margin-top: 2px;">Rp <?= number_format($summary['totalIncome'], 0, ',', '.') ?></h4>
            </div>
        </div>

        <!-- Pengeluaran -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.1); color: var(--danger); display: flex; align-items: center; justify-content: center;">
                <span class="material-icons">trending_down</span>
            </div>
            <div>
                <p style="font-size: 12px; color: var(--text-secondary);">Total Pengeluaran</p>
                <h4 style="font-size: 18px; font-weight: 700; color: var(--danger); margin-top: 2px;">Rp <?= number_format($summary['totalExpense'], 0, ',', '.') ?></h4>
            </div>
        </div>

        <!-- Saldo Bersih -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); color: var(--text-primary); display: flex; align-items: center; justify-content: center;">
                <span class="material-icons">payments</span>
            </div>
            <div>
                <p style="font-size: 12px; color: var(--text-secondary);">Selisih (Saldo Bersih)</p>
                <h4 style="font-size: 18px; font-weight: 700; color: <?= $summary['balance'] >= 0 ? 'var(--text-primary)' : 'var(--danger)' ?>; margin-top: 2px;">Rp <?= number_format($summary['balance'], 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Transactions Table -->
<div class="glass-card">
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 18px; font-weight: 600; color: #fff;">Rincian Laporan Transaksi</h3>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kategori</th>
                    <th>Tipe</th>
                    <th style="text-align: right;">Nominal</th>
                    <th style="text-align: center;">Bukti Struk</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            <span class="material-icons" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 12px;">receipt</span>
                            Tidak ada data transaksi pada periode filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td style="color: var(--text-secondary); white-space: nowrap;">
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
                                <span class="badge <?= $tx->type === 'income' ? 'badge-income' : 'badge-expense' ?>">
                                    <?= $tx->type === 'income' ? 'Masuk' : 'Keluar' ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; white-space: nowrap; color: <?= $tx->type === 'income' ? 'var(--success)' : 'var(--danger)' ?>">
                                <?= $tx->type === 'income' ? '+' : '-' ?> Rp <?= number_format($tx->amount, 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($tx->receipt_photo): ?>
                                    <button class="view-receipt-btn btn btn-secondary btn-sm" data-src="<?= BASE_URL ?>/transactions/viewReceipt/<?= $tx->id ?>" data-title="<?= htmlspecialchars($tx->description) ?>" style="padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; color: var(--accent); border-color: rgba(6, 182, 212, 0.2);">
                                        <span class="material-icons" style="font-size: 16px;">image</span>
                                        <span>Pratinjau</span>
                                    </button>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary); font-size: 12px; font-style: italic;">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
