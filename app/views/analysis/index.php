<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; align-items: start;">
    
    <!-- Kolom Kiri: Visualisasi Grafik & Tabel Kategori -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Grafik Pengeluaran -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #fff;">Distribusi Pengeluaran Kategori</h3>
                <span class="material-icons" style="color: var(--text-secondary);">pie_chart</span>
            </div>
            
            <?php if ($totalExpense == 0): ?>
                <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary); display: flex; flex-direction: column; align-items: center; gap: 12px;">
                    <span class="material-icons" style="font-size: 48px; opacity: 0.3;">donut_large</span>
                    <span>Belum ada transaksi pengeluaran di bulan <?= htmlspecialchars($monthName) ?> <?= $year ?>.</span>
                </div>
            <?php else: ?>
                <div style="display: flex; justify-content: center; align-items: center; height: 280px; position: relative;">
                    <canvas id="analysisDonutChart"></canvas>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabel Rincian Pengeluaran Kategori -->
        <div class="glass-card">
            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #fff;">Rincian Pengeluaran <?= htmlspecialchars($monthName) ?></h3>
            </div>
            
            <div class="table-responsive">
                <table class="custom-table" style="font-size: 14px;">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Sifat</th>
                            <th style="text-align: right;">Total Pengeluaran</th>
                            <th style="text-align: right;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categoriesData as $cat): ?>
                            <tr>
                                <td style="display: flex; align-items: center; gap: 10px; border-bottom: none;">
                                    <span class="category-dot" style="background-color: <?= htmlspecialchars($cat->color) ?>; width: 10px; height: 10px; flex-shrink: 0;"></span>
                                    <span style="font-weight: 500; color: #fff;"><?= htmlspecialchars($cat->name) ?></span>
                                </td>
                                <td>
                                    <span class="badge" style="font-size: 10px; padding: 2px 8px; background-color: <?= $cat->is_fixed ? 'rgba(6, 182, 212, 0.1)' : 'rgba(255,255,255,0.03)' ?>; border: 1px solid <?= $cat->is_fixed ? 'var(--accent)' : 'var(--border-glass)' ?>; color: <?= $cat->is_fixed ? 'var(--accent)' : 'var(--text-secondary)' ?>;">
                                        <?= $cat->is_fixed ? 'Tetap' : 'Opsional' ?>
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #fff;">
                                    Rp <?= number_format($cat->total, 0, ',', '.') ?>
                                </td>
                                <td style="text-align: right; color: var(--text-secondary); font-size: 13px;">
                                    <?= $totalExpense > 0 ? number_format(($cat->total * 100) / $totalExpense, 1, ',', '.') : '0,0' ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Budgeting Advisor (Target Penghematan) -->
    <div class="glass-card" style="display: flex; flex-direction: column; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-glass); padding-bottom: 14px;">
            <span class="material-icons" style="color: var(--warning); font-size: 26px;">insights</span>
            <h3 style="font-size: 18px; font-weight: 600; color: #fff; margin: 0;">Budgeting Advisor</h3>
        </div>

        <!-- Ringkasan Sifat Anggaran -->
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); border-radius: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="color: var(--accent); font-size: 20px;">lock</span>
                    <span style="font-size: 13px; color: var(--text-secondary);">Pengeluaran Tetap (Wajib)</span>
                </div>
                <span style="font-weight: 700; color: #fff;" id="fixed-total-raw" data-val="<?= $totalFixed ?>">
                    Rp <?= number_format($totalFixed, 0, ',', '.') ?>
                </span>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); border-radius: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons" style="color: var(--primary); font-size: 20px;">tune</span>
                    <span style="font-size: 13px; color: var(--text-secondary);">Pengeluaran Opsional (Variabel)</span>
                </div>
                <span style="font-weight: 700; color: #fff;" id="optional-total-raw" data-val="<?= $totalOptional ?>">
                    Rp <?= number_format($totalOptional, 0, ',', '.') ?>
                </span>
            </div>
        </div>

        <!-- Slider Target Hemat -->
        <div style="margin-top: 10px; padding: 16px; background: rgba(99, 102, 241, 0.03); border: 1px solid rgba(99, 102, 241, 0.15); border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-weight: 600; color: #fff; font-size: 14px;">Target Hemat Bulan Depan</span>
                <span style="font-weight: 700; color: var(--primary); font-size: 16px;" id="saving-percent-display">20%</span>
            </div>
            
            <input type="range" id="saving-slider" min="10" max="50" step="5" value="20" style="width: 100%; height: 6px; border-radius: 3px; background: rgba(255,255,255,0.1); outline: none; cursor: pointer; accent-color: var(--primary); font-size: 16px; touch-action: manipulation;">
            
            <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-secondary); margin-top: 6px;">
                <span>10% (Santai)</span>
                <span>30% (Sedang)</span>
                <span>50% (Agresif)</span>
            </div>
        </div>

        <!-- Hasil Kalkulator Target -->
        <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                <span style="color: var(--text-secondary);">Nominal yang harus dihemat:</span>
                <span style="font-weight: 700; color: var(--success);" id="saving-amount-display">Rp 0</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                <span style="color: var(--text-secondary);">Batas aman belanja opsional baru:</span>
                <span style="font-weight: 700; color: #fff;" id="new-optional-limit-display">Rp 0</span>
            </div>
            
            <!-- Bilah Anggaran Total Masa Depan -->
            <div style="border-top: 1px dashed var(--border-glass); padding-top: 12px; margin-top: 4px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: 600; color: #fff; font-size: 13px;">Total Batas Anggaran Baru:</span>
                    <span style="font-weight: 800; color: var(--accent); font-size: 16px;" id="new-total-budget-display">Rp 0</span>
                </div>
                <!-- Progress bar visual -->
                <div style="width: 100%; height: 10px; background: rgba(255,255,255,0.05); border-radius: 5px; overflow: hidden; display: flex; border: 1px solid var(--border-glass);">
                    <div id="progress-fixed" style="height: 100%; background: var(--accent); width: 40%; transition: width 0.3s;"></div>
                    <div id="progress-optional" style="height: 100%; background: var(--primary); width: 40%; transition: width 0.3s;"></div>
                </div>
                <div style="display: flex; gap: 16px; margin-top: 8px; font-size: 11px; color: var(--text-secondary);">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 8px; height: 8px; background: var(--accent); border-radius: 50%;"></span>
                        <span>Porsi Tetap (Wajib)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%;"></span>
                        <span>Batas Opsional Baru</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips Keuangan -->
        <div style="margin-top: 10px; padding: 16px; background: rgba(6, 182, 212, 0.04); border: 1px solid rgba(6, 182, 212, 0.15); border-radius: 12px; display: flex; gap: 12px; align-items: start;">
            <span class="material-icons" style="color: var(--accent); font-size: 24px; margin-top: 2px;">lightbulb</span>
            <div style="font-size: 13px; color: var(--text-primary); line-height: 1.5;">
                <p style="font-weight: 600; color: #fff; margin-bottom: 4px;">Rekomendasi Tindakan</p>
                <span id="advisor-tip-text"><?= $financialTip ?></span>
            </div>
        </div>

    </div>
</div>

<!-- JS Inisialisasi Chart Donat & Logika Slider Real-time -->
<?php if ($totalExpense > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart Donat Distribusi Kategori
    const ctx = document.getElementById('analysisDonutChart').getContext('2d');
    
    const chartLabels = [];
    const chartData = [];
    const chartColors = [];
    
    <?php foreach ($categoriesData as $cat): ?>
        <?php if ($cat->total > 0): ?>
            chartLabels.push('<?= htmlspecialchars($cat->name) ?>');
            chartData.push(<?= $cat->total ?>);
            chartColors.push('<?= htmlspecialchars($cat->color) ?>');
        <?php endif; ?>
    <?php endforeach; ?>

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartLabels,
            datasets: [{
                data: chartData,
                backgroundColor: chartColors,
                borderWidth: 2,
                borderColor: '#1a1a2e',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#94a3b8',
                        font: { family: 'Inter', size: 11 },
                        padding: 14
                    }
                },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    titleColor: '#fff',
                    bodyColor: '#e2e8f0',
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const val = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percent = ((val * 100) / total).toFixed(1);
                            return ' ' + context.label + ': Rp ' + parseInt(val).toLocaleString('id-ID') + ' (' + percent + '%)';
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // 2. Logika Real-time Kalkulator Budgeting Advisor
    const fixedTotal = parseFloat(document.getElementById('fixed-total-raw').getAttribute('data-val')) || 0;
    const optionalTotal = parseFloat(document.getElementById('optional-total-raw').getAttribute('data-val')) || 0;
    
    const slider = document.getElementById('saving-slider');
    const percentDisplay = document.getElementById('saving-percent-display');
    const savingAmountDisplay = document.getElementById('saving-amount-display');
    const newOptionalLimitDisplay = document.getElementById('new-optional-limit-display');
    const newTotalBudgetDisplay = document.getElementById('new-total-budget-display');
    
    const progFixed = document.getElementById('progress-fixed');
    const progOptional = document.getElementById('progress-optional');

    function calculateBudget() {
        const percent = parseInt(slider.value);
        percentDisplay.textContent = percent + '%';
        
        // Hitung nominal hemat
        const savingAmount = optionalTotal * (percent / 100);
        savingAmountDisplay.textContent = 'Rp ' + Math.round(savingAmount).toLocaleString('id-ID');
        
        // Batas opsional baru
        const newOptionalLimit = optionalTotal - savingAmount;
        newOptionalLimitDisplay.textContent = 'Rp ' + Math.round(newOptionalLimit).toLocaleString('id-ID');
        
        // Total anggaran baru
        const newTotalBudget = fixedTotal + newOptionalLimit;
        newTotalBudgetDisplay.textContent = 'Rp ' + Math.round(newTotalBudget).toLocaleString('id-ID');
        
        // Update Progress Bar
        const totalOverallBudget = fixedTotal + optionalTotal;
        if (totalOverallBudget > 0) {
            const fixedPercent = (fixedTotal / totalOverallBudget) * 100;
            const optionalPercent = (newOptionalLimit / totalOverallBudget) * 100;
            
            progFixed.style.width = fixedPercent + '%';
            progOptional.style.width = optionalPercent + '%';
        } else {
            progFixed.style.width = '0%';
            progOptional.style.width = '0%';
        }
    }

    if (slider) {
        slider.addEventListener('input', calculateBudget);
        // Jalankan kalkulasi pertama kali
        calculateBudget();
    }
});
</script>
<?php endif; ?>

<style>
@media (max-width: 1024px) {
    div[style*="grid-template-columns: 1.2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
