<!-- Summary Cards -->
<div class="stats-grid">
    <!-- Card Saldo -->
    <div class="glass-card stat-card balance">
        <div class="stat-header">
            <span class="stat-title">Total Saldo</span>
            <div class="stat-icon-wrapper">
                <span class="material-icons">account_balance</span>
            </div>
        </div>
        <div class="stat-amount" style="color: <?= $totalBalance >= 0 ? 'var(--text-primary)' : 'var(--danger)' ?>">
            Rp <?= number_format($totalBalance, 0, ',', '.') ?>
        </div>
    </div>

    <!-- Card Pemasukan -->
    <div class="glass-card stat-card income">
        <div class="stat-header">
            <span class="stat-title">Total Pemasukan</span>
            <div class="stat-icon-wrapper">
                <span class="material-icons">trending_up</span>
            </div>
        </div>
        <div class="stat-amount" style="color: var(--success)">
            Rp <?= number_format($totalIncome, 0, ',', '.') ?>
        </div>
    </div>

    <!-- Card Pengeluaran -->
    <div class="glass-card stat-card expense">
        <div class="stat-header">
            <span class="stat-title">Total Pengeluaran</span>
            <div class="stat-icon-wrapper">
                <span class="material-icons">trending_down</span>
            </div>
        </div>
        <div class="stat-amount" style="color: var(--danger)">
            Rp <?= number_format($totalExpense, 0, ',', '.') ?>
        </div>
    </div>
</div>

<!-- Chart & Recent Transactions Section -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 30px; align-items: start;">
    
    <!-- Chart Card -->
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 600; color: #fff;">Arus Kas Bulanan</h3>
            <span class="material-icons" style="color: var(--text-secondary);">bar_chart</span>
        </div>
        <div class="chart-container" style="height: 320px;">
            <canvas id="dashboardChart" data-base-url="<?= BASE_URL ?>"></canvas>
        </div>
    </div>

    <!-- Recent Transactions Card -->
    <div class="glass-card" style="height: 100%; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 600; color: #fff;">Transaksi Terbaru</h3>
            <a href="<?= BASE_URL ?>/transactions" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 600;">Lihat Semua</a>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 16px; flex-grow: 1; overflow-y: auto;">
            <?php if (empty($recentTransactions)): ?>
                <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary); display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <span class="material-icons" style="font-size: 48px; opacity: 0.3;">receipt_long</span>
                    <span style="font-size: 14px;">Belum ada transaksi.</span>
                </div>
            <?php else: ?>
                <?php foreach ($recentTransactions as $tx): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border-glass);">
                        <div style="display: flex; align-items: center; gap: 12px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 60%;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background-color: <?= htmlspecialchars($tx->category_color ?? '#6366f1') ?>; flex-shrink: 0;"></div>
                            <div style="overflow: hidden;">
                                <h4 style="font-size: 14px; font-weight: 500; color: #fff; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($tx->description) ?></h4>
                                <span style="font-size: 11px; color: var(--text-secondary);"><?= htmlspecialchars($tx->category_name ?? 'Lainnya') ?></span>
                            </div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0; display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 14px; font-weight: 600; color: <?= $tx->type === 'income' ? 'var(--success)' : 'var(--danger)' ?>">
                                <?= $tx->type === 'income' ? '+' : '-' ?> Rp <?= number_format($tx->amount, 0, ',', '.') ?>
                            </span>
                            <?php if ($tx->receipt_photo): ?>
                                <a href="#" class="view-receipt-btn" data-src="<?= BASE_URL ?>/transactions/viewReceipt/<?= $tx->id ?>" data-title="<?= htmlspecialchars($tx->description) ?>" style="color: var(--accent); display: flex; align-items: center;">
                                    <span class="material-icons" style="font-size: 18px;">image</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 1024px) {
    div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
