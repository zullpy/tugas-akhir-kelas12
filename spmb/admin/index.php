<?php
// spmb/admin/index.php
// Dashboard Utama Panitia SPMB SMKS SUKAPURA
$adminPageTitle = 'Dashboard Admin';
$adminPageHeading = 'Dashboard Ikhtisar SPMB';

require_once __DIR__ . '/header.php';

// Hitung Statistik
$totalAll      = $pdo->query("SELECT COUNT(*) FROM `spmb_pendaftar`")->fetchColumn();
$totalPending  = $pdo->query("SELECT COUNT(*) FROM `spmb_pendaftar` WHERE `status` = 'Menunggu Verifikasi'")->fetchColumn();
$totalAccepted = $pdo->query("SELECT COUNT(*) FROM `spmb_pendaftar` WHERE `status` = 'Diterima'")->fetchColumn();
$totalRejected = $pdo->query("SELECT COUNT(*) FROM `spmb_pendaftar` WHERE `status` IN ('Ditolak', 'Cadangan')")->fetchColumn();

// Distribusi per Jurusan
$stmtJurusan = $pdo->query("SELECT `jurusan_1`, COUNT(*) as jumlah FROM `spmb_pendaftar` GROUP BY `jurusan_1`");
$jurusanCounts = [];
while ($r = $stmtJurusan->fetch()) {
    $jurusanCounts[$r['jurusan_1']] = (int)$r['jumlah'];
}

// Distribusi per Jalur
$stmtJalur = $pdo->query("SELECT `jalur`, COUNT(*) as jumlah FROM `spmb_pendaftar` GROUP BY `jalur`");
$jalurCounts = [];
while ($r = $stmtJalur->fetch()) {
    $jalurCounts[$r['jalur']] = (int)$r['jumlah'];
}

// Siapkan data untuk Grafik Chart.js
$chartLabels = [];
$chartFullNames = [];
$chartCounts = [];
$chartKuotas = [];
$chartColors = [];

foreach ($DAFTAR_JURUSAN as $kode => $j) {
    $count = $jurusanCounts[$kode] ?? 0;
    $chartLabels[] = $kode;
    $chartFullNames[] = $j['nama'];
    $chartCounts[] = $count;
    $chartKuotas[] = (int)($j['kuota'] ?? 72);
    $chartColors[] = $j['badge'] ?? '#3B82F6';
}
?>

<!-- METRICS CARDS -->
<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color:var(--adm-navy-dark);"><?php echo $totalAll; ?></div>
            <div class="adm-stat-title">Total Pendaftar</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background:var(--adm-yellow);">
            <i class="ph-bold ph-users-four"></i>
        </div>
    </div>

    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color:#D97706;"><?php echo $totalPending; ?></div>
            <div class="adm-stat-title">Menunggu Verifikasi</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background:#FFF7B2;">
            <i class="ph-bold ph-hourglass-high" style="color:#B45309;"></i>
        </div>
    </div>

    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color:#16A34A;"><?php echo $totalAccepted; ?></div>
            <div class="adm-stat-title">Pendaftar Diterima</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background:var(--adm-green);">
            <i class="ph-bold ph-check-circle" style="color:#fff;"></i>
        </div>
    </div>

    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color:var(--adm-red);"><?php echo $totalRejected; ?></div>
            <div class="adm-stat-title">Ditolak</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background:#FFD2D2;">
            <i class="ph-bold ph-x-circle" style="color:var(--adm-red);"></i>
        </div>
    </div>
</div>

<!-- JALUR PENDAFTARAN SUMMARY -->
<div class="adm-card" style="margin-bottom:24px;">
    <div class="adm-card-header" style="padding:14px 20px;">
        <h3 class="adm-card-title" style="font-size:1rem;">
            <i class="ph-bold ph-path" style="color:var(--adm-navy);"></i> Pendaftar Berdasarkan Jalur Masuk
        </h3>
        <span style="font-size:0.82rem; color:#64748B; font-weight:600;">Klik kartu jalur untuk memfilter daftar</span>
    </div>
    <div class="adm-card-body" style="padding:16px 20px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
            <?php foreach ($DAFTAR_JALUR as $kJalur => $vJalur): 
                $countJalur = $jalurCounts[$kJalur] ?? 0;
            ?>
                <a href="pendaftar.php?jalur=<?php echo urlencode($kJalur); ?>" style="text-decoration:none; color:inherit; display:flex; align-items:center; justify-content:space-between; padding:14px 18px; background:#FAFAFA; border:var(--border-md); border-radius:12px; box-shadow:var(--shadow-sm); transition:all 0.15s ease;">
                    <div>
                        <div style="margin-bottom:4px;"><?php echo get_jalur_badge_html($kJalur); ?></div>
                        <div style="font-size:0.8rem; color:#555;"><?php echo $kJalur === 'Yatim/Piatu' ? 'Bantuan Khusus' : ($kJalur === 'Prestasi' ? 'Akademik & Non' : 'Seleksi Reguler'); ?></div>
                    </div>
                    <div style="font-family:var(--font-heading); font-size:1.4rem; font-weight:900;">
                        <?php echo $countJalur; ?> <span style="font-size:0.75rem; font-weight:600; color:#666;">siswa</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div style="margin-bottom:30px;">
    
    <!-- JURUSAN DISTRIBUTION (GRAFIK) -->
    <div class="adm-card" style="margin-bottom:0;">
        <div class="adm-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <h3 class="adm-card-title">
                <i class="ph-bold ph-chart-pie-slice"></i> Peminatan Jurusan
            </h3>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div class="adm-chart-tabs">
                    <button type="button" id="btnChartBar" class="adm-chart-tab active" onclick="setJurusanChartType('bar')">
                        <i class="ph-bold ph-chart-bar-horizontal"></i> Batang
                    </button>
                    <button type="button" id="btnChartDoughnut" class="adm-chart-tab" onclick="setJurusanChartType('doughnut')">
                        <i class="ph-bold ph-chart-pie-slice"></i> Donat
                    </button>
                </div>
                <a href="pendaftar.php" class="adm-btn adm-btn-sm adm-btn-secondary">
                    Kelola Semua Pendaftar <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="adm-card-body">
            <div class="adm-chart-inner-grid">
                <!-- Grafik Canvas -->
                <div style="position:relative; width:100%; height:310px;">
                    <canvas id="jurusanChart"></canvas>
                </div>

                <!-- Ringkasan Kuota per Jurusan -->
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; font-weight:800; color:#64748B; text-transform:uppercase; letter-spacing:0.04em; border-bottom:1px solid #E2E8F0; padding-bottom:8px;">
                        <span>Keterisian Kuota</span>
                        <span>Total: <?php echo $totalAll; ?> Pendaftar</span>
                    </div>
                    <div class="adm-jurusan-summary-grid">
                        <?php 
                        $pIdx = 0;
                        foreach ($DAFTAR_JURUSAN as $kode => $j): 
                            $cnt = $jurusanCounts[$kode] ?? 0;
                        ?>
                            <div class="adm-jurusan-pill" data-index="<?php echo $pIdx; ?>" data-kode="<?php echo $kode; ?>" title="<?php echo htmlspecialchars($j['nama']); ?>: <?php echo $cnt; ?> / <?php echo $j['kuota']; ?> siswa">
                                <span class="adm-jurusan-pill-code">
                                    <span class="adm-jurusan-pill-dot" style="background:<?php echo $j['badge']; ?>;"></span>
                                    <?php echo $kode; ?>
                                </span>
                                <span style="color:#0F172A; font-family:monospace;"><?php echo $cnt; ?>/<?php echo $j['kuota']; ?></span>
                            </div>
                        <?php 
                            $pIdx++;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rawLabels = <?php echo json_encode($chartLabels); ?>;
    const rawNames = <?php echo json_encode($chartFullNames); ?>;
    const rawCounts = <?php echo json_encode($chartCounts); ?>;
    const rawKuotas = <?php echo json_encode($chartKuotas); ?>;
    const rawColors = <?php echo json_encode($chartColors); ?>;

    const canvas = document.getElementById('jurusanChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let chartInstance = null;

    function renderChart(type) {
        if (chartInstance) {
            chartInstance.destroy();
        }

        if (type === 'doughnut') {
            chartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: rawLabels,
                    datasets: [{
                        data: rawCounts,
                        backgroundColor: rawColors,
                        borderColor: '#0F172A',
                        borderWidth: 1.5,
                        hoverOffset: 8,
                        hoverBorderWidth: 2.5,
                        hoverBorderColor: '#0F172A'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    onHover: (event, activeElements) => {
                        document.querySelectorAll('.adm-jurusan-pill').forEach(p => p.classList.remove('active-hover'));
                        if (activeElements && activeElements.length > 0) {
                            const idx = activeElements[0].index;
                            const target = document.querySelector(`.adm-jurusan-pill[data-index="${idx}"]`);
                            if (target) target.classList.add('active-hover');
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                borderRadius: 3,
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 11,
                                    weight: 'bold'
                                },
                                padding: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleFont: { family: "'Space Grotesk', sans-serif", size: 12, weight: 'bold' },
                            bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            borderColor: '#FACC15',
                            borderWidth: 1.5,
                            callbacks: {
                                title: function(items) {
                                    const idx = items[0].dataIndex;
                                    return rawLabels[idx] + ' (' + rawNames[idx] + ')';
                                },
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const val = context.raw;
                                    const total = rawCounts.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                    return ' ' + val + ' Siswa (' + pct + '% dari total)';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: rawLabels,
                    datasets: [{
                        label: 'Pendaftar',
                        data: rawCounts,
                        backgroundColor: rawColors,
                        borderColor: '#0F172A',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                        hoverBorderWidth: 2.5,
                        hoverBorderColor: '#0F172A'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    onHover: (event, activeElements) => {
                        document.querySelectorAll('.adm-jurusan-pill').forEach(p => p.classList.remove('active-hover'));
                        if (activeElements && activeElements.length > 0) {
                            const idx = activeElements[0].index;
                            const target = document.querySelector(`.adm-jurusan-pill[data-index="${idx}"]`);
                            if (target) target.classList.add('active-hover');
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleFont: { family: "'Space Grotesk', sans-serif", size: 12, weight: 'bold' },
                            bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            borderColor: '#FACC15',
                            borderWidth: 1.5,
                            callbacks: {
                                title: function(items) {
                                    const idx = items[0].dataIndex;
                                    return rawLabels[idx] + ' - ' + rawNames[idx];
                                },
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const val = context.raw;
                                    const kuota = rawKuotas[idx];
                                    const pct = Math.round((val / kuota) * 100);
                                    return ' ' + val + ' Siswa / Kuota ' + kuota + ' (' + pct + '%)';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    weight: 'bold',
                                    size: 11
                                }
                            },
                            grid: {
                                color: '#E2E8F0'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Space Grotesk', sans-serif",
                                    weight: 'bold',
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Interactive Hover between Pills and Chart
    function highlightInChart(idx) {
        if (!chartInstance) return;
        const meta = chartInstance.getDatasetMeta(0);
        if (meta && meta.data && meta.data[idx]) {
            const el = meta.data[idx];
            const pos = typeof el.tooltipPosition === 'function' ? el.tooltipPosition() : { x: el.x, y: el.y };
            chartInstance.setActiveElements([{ datasetIndex: 0, index: idx }]);
            chartInstance.tooltip.setActiveElements([{ datasetIndex: 0, index: idx }], pos);
            chartInstance.update();
        }
    }

    function clearChartHighlight() {
        if (!chartInstance) return;
        chartInstance.setActiveElements([]);
        chartInstance.tooltip.setActiveElements([], {});
        chartInstance.update();
    }

    const pills = document.querySelectorAll('.adm-jurusan-pill');
    pills.forEach(pill => {
        pill.addEventListener('mouseenter', function() {
            const idx = parseInt(this.getAttribute('data-index'), 10);
            if (!isNaN(idx)) {
                highlightInChart(idx);
            }
            pills.forEach(p => p.classList.remove('active-hover'));
            this.classList.add('active-hover');
        });

        pill.addEventListener('mouseleave', function() {
            clearChartHighlight();
            this.classList.remove('active-hover');
        });
    });

    canvas.addEventListener('mouseleave', function() {
        clearChartHighlight();
        pills.forEach(p => p.classList.remove('active-hover'));
    });

    window.setJurusanChartType = function(type) {
        localStorage.setItem('spmb_jurusan_chart_type', type);
        document.querySelectorAll('.adm-chart-tab').forEach(el => el.classList.remove('active'));
        if (type === 'doughnut') {
            document.getElementById('btnChartDoughnut').classList.add('active');
        } else {
            document.getElementById('btnChartBar').classList.add('active');
        }
        renderChart(type);
    };

    const initialType = localStorage.getItem('spmb_jurusan_chart_type') || 'bar';
    setJurusanChartType(initialType);
});
</script>

<!-- QUICK ACTION TOOLBAR -->
<!-- <div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-lightning"></i> Aksi Cepat Administrasi
        </h3>
    </div>
    <div class="adm-card-body">
        <div style="display:flex; gap:16px; flex-wrap:wrap;">
            <a href="tambah.php" class="adm-btn adm-btn-primary">
                <i class="ph-bold ph-user-plus"></i> Tambah Pendaftar Manual
            </a>
            <a href="export.php" class="adm-btn adm-btn-secondary">
                <i class="ph-bold ph-download-simple"></i> Unduh Data Pendaftar (CSV/Excel)
            </a>
            <a href="pengaturan.php" class="adm-btn adm-btn-secondary">
                <i class="ph-bold ph-sliders"></i> Ubah Status Gelombang &amp; Kuota
            </a>
            <a href="../daftar.php" target="_blank" class="adm-btn adm-btn-warning">
                <i class="ph-bold ph-arrow-square-out"></i> Buka Formulir Publik
            </a>
        </div>
    </div>
</div> -->

<?php require_once __DIR__ . '/footer.php'; ?>
