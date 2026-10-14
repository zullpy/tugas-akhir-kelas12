<?php
// setting/index.php
// Pusat Pengaturan Konfigurasi Portal SPMB & Kuota Jurusan SMKS SUKAPURA

require_once __DIR__ . '/../spmb/config.php';
check_admin_login(true, '../spmb/admin/login.php');

$pdo      = get_db_connection();
$settings = get_all_settings();

$success = '';
$error   = '';

// Update Pengaturan Sistem SPMB
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'settings') {
        $keys = [
            'status_pendaftaran',
            'tanggal_mulai',
            'tanggal_selesai',
            'tahun_ajaran',
            'gelombang',
            'periode_gelombang',
            'usia_min',
            'usia_max',
            'hotline_wa',
            'email_spmb',
            'biaya_pendaftaran',
            'pengumuman_header'
        ];

        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $val = sanitize_input($_POST[$k]);
                $ins->execute([$k, $val]);
            }
        }

        $settings = get_all_settings(); // refresh
        $success = "Pengaturan portal SPMB berhasil disimpan!";

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success]);
            exit;
        }
    } elseif ($action === 'kuota') {
        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        foreach ($DAFTAR_JURUSAN as $kode => $j) {
            $inputKey = 'kuota_' . $kode;
            if (isset($_POST[$inputKey])) {
                $val = max(1, (int)$_POST[$inputKey]);
                $ins->execute([$inputKey, (string)$val]);
            }
        }
        $settings = get_all_settings(); // refresh & sync
        $success = "Kapasitas kuota siswa per jurusan berhasil diperbarui!";

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success]);
            exit;
        }
    }
}

// Ambil jumlah pendaftar terkini per jurusan untuk informasi kuota
$stmtCounts = $pdo->query("SELECT `jurusan_1`, COUNT(*) as jml FROM `spmb_pendaftar` GROUP BY `jurusan_1`");
$currentApplicantCounts = [];
while ($row = $stmtCounts->fetch()) {
    $currentApplicantCounts[$row['jurusan_1']] = (int)$row['jml'];
}

$statusInfo = get_spmb_status_info($settings);

$adminPageTitle   = 'Pengaturan Portal SPMB';
$adminPageHeading = 'Pengaturan Portal SPMB &amp; Kuota Jurusan';
require_once __DIR__ . '/../spmb/admin/header.php';
?>

<?php $activeTab = 'index.php'; require_once __DIR__ . '/tabs.php'; ?>

<?php if (!empty($success)): ?>
    <div class="spmb-alert spmb-alert-success">
        <i class="ph-bold ph-check-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $success; ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="spmb-alert spmb-alert-danger">
        <i class="ph-bold ph-warning-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $error; ?></div>
    </div>
<?php endif; ?>

<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- CARD 1: KONFIGURASI PORTAL SPMB -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title">
                <i class="ph-bold ph-sliders-horizontal"></i> Konfigurasi Portal SPMB
            </h3>
        </div>
        <div class="adm-card-body">
            <form id="form-settings" action="index.php" method="POST">
                <input type="hidden" name="action" value="settings">

                <div class="spmb-form-group">
                    <label class="spmb-label">Status &amp; Jadwal Pendaftaran</label>
                    <select name="status_pendaftaran" id="status_pendaftaran" class="spmb-select" style="font-weight:800;">
                        <option value="otomatis" <?php echo (($settings['status_pendaftaran'] ?? '') === 'otomatis') ? 'selected' : ''; ?>>
                            Sesuai Jadwal Tanggal (Buka &amp; Tutup Otomatis)
                        </option>
                        <option value="buka" <?php echo (($settings['status_pendaftaran'] ?? '') === 'buka') ? 'selected' : ''; ?>>
                            Buka Sekarang (Pendaftaran Aktif)
                        </option>
                        <option value="tutup" <?php echo (($settings['status_pendaftaran'] ?? '') === 'tutup') ? 'selected' : ''; ?>>
                            Tutup Sekarang (Pendaftaran Ditutup)
                        </option>
                    </select>
                </div>

                <!-- LIVE STATUS INDICATOR -->
                <div style="background:#F8FAFC; border:var(--border-thin); border-radius:8px; padding:10px 14px; margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; font-weight:700;">
                        <span style="color:#64748B;">Status Sistem Hari Ini (<?php echo tgl_indo(date('Y-m-d')); ?>):</span>
                        <span style="display:inline-flex; align-items:center; gap:5px; background:<?php echo $statusInfo['badgeColor']; ?>; color:#FFF; padding:2px 10px; border-radius:999px; font-size:0.75rem; border:1px solid #000; font-weight:800;">
                            <i class="ph-bold <?php echo $statusInfo['isOpen'] ? 'ph-check-circle' : 'ph-lock-key'; ?>"></i> <?php echo $statusInfo['label']; ?>
                        </span>
                    </div>
                    <?php if (!empty($statusInfo['note'])): ?>
                        <span style="font-size:0.8rem; font-weight:700; color:#475569;">
                            <i class="ph-bold ph-calendar"></i> <?php echo htmlspecialchars($statusInfo['note']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">
                            <i class="ph-bold ph-calendar-plus" style="color:#16A34A;"></i> Tanggal Mulai Dibuka
                        </label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="spmb-input" value="<?php echo htmlspecialchars($settings['tanggal_mulai'] ?? '2027-02-15'); ?>" onchange="syncPeriodeTeks()">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">
                            <i class="ph-bold ph-calendar-x" style="color:#DC2626;"></i> Tanggal Selesai / Ditutup
                        </label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="spmb-input" value="<?php echo htmlspecialchars($settings['tanggal_selesai'] ?? '2027-05-10'); ?>" onchange="syncPeriodeTeks()">
                    </div>
                </div>

                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="spmb-input" value="<?php echo htmlspecialchars($settings['tahun_ajaran'] ?? '2027-2028'); ?>" required>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Nama Gelombang</label>
                        <input type="text" name="gelombang" class="spmb-input" value="<?php echo htmlspecialchars($settings['gelombang'] ?? 'Gelombang 1'); ?>" required>
                    </div>
                </div>

                <div class="spmb-form-group">
                    <label class="spmb-label">Teks Periode Waktu Gelombang (Publik)</label>
                    <input type="text" name="periode_gelombang" id="periode_gelombang" class="spmb-input" value="<?php echo htmlspecialchars($settings['periode_gelombang'] ?? '15 Februari - 10 Mei 2027'); ?>" required>
                    <span style="font-size:0.75rem; color:#64748B; font-weight:600;">Otomatis tersinkronisasi saat tanggal mulai/selesai diubah di atas</span>
                </div>

                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">Hotline WhatsApp Panitia</label>
                        <input type="text" name="hotline_wa" class="spmb-input" value="<?php echo htmlspecialchars($settings['hotline_wa'] ?? '081234567890'); ?>" required>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Email Resmi SPMB</label>
                        <input type="email" name="email_spmb" class="spmb-input" value="<?php echo htmlspecialchars($settings['email_spmb'] ?? 'spmb@smksukapura.sch.id'); ?>" required>
                    </div>
                </div>

                <div class="spmb-form-group">
                    <label class="spmb-label">Header Pengumuman Beranda SPMB</label>
                    <input type="text" name="pengumuman_header" class="spmb-input" value="<?php echo htmlspecialchars($settings['pengumuman_header'] ?? ''); ?>" placeholder="Tuliskan banner pengumuman jika ada...">
                </div>

                <button type="submit" class="adm-btn adm-btn-primary" style="padding:14px 28px; font-size:0.95rem;">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan Konfigurasi Portal
                </button>
            </form>
        </div>
    </div>

    <!-- CARD 2: DAYA TAMPUNG & KUOTA PER JURUSAN -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title">
                <i class="ph-bold ph-chart-donut" style="color:#0A4D68;"></i> Daya Tampung &amp; Kuota Jurusan
            </h3>
            <span class="spmb-badge" style="background:var(--adm-yellow-light); border:var(--border-thin); font-size:0.8rem; font-weight:800;">
                Total Pagu: <?php echo array_sum(array_column($DAFTAR_JURUSAN, 'kuota')); ?> Siswa
            </span>
        </div>
        <div class="adm-card-body">
            <form id="form-kuota" action="index.php" method="POST">
                <input type="hidden" name="action" value="kuota">
                <p style="font-size:0.85rem; color:#64748B; margin-bottom:16px; line-height:1.5;">
                    Tentukan batas maksimal calon siswa yang dapat diterima untuk setiap kompetensi keahlian. Angka ini secara otomatis menentukan kuota riil dan alokasi jurusan saat verifikasi.
                </p>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px; margin-bottom:20px;">
                    <?php foreach ($DAFTAR_JURUSAN as $kode => $j): 
                        $pendaftarCount = $currentApplicantCounts[$kode] ?? 0;
                        $currentKuota = $j['kuota'];
                    ?>
                        <div style="background:#FAFAFA; border:var(--border-md); border-radius:10px; padding:14px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                            <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                                <img src="../assets/jurusan/<?php echo strtolower($kode); ?>.png" alt="<?php echo $kode; ?>" style="width:36px; height:36px; object-fit:contain; flex-shrink:0;">
                                <div style="min-width:0;">
                                    <div style="font-weight:800; font-size:1rem; line-height:1.2;"><?php echo $kode; ?></div>
                                    <div style="font-size:0.75rem; color:#64748B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($j['nama']); ?>"><?php echo htmlspecialchars($j['nama']); ?></div>
                                    <div style="font-size:0.75rem; color:#0A4D68; font-weight:700; margin-top:2px;">
                                        Pendaftar: <strong><?php echo $pendaftarCount; ?></strong> siswa
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                                <input type="number" name="kuota_<?php echo $kode; ?>" class="spmb-input" min="1" max="1000" value="<?php echo htmlspecialchars($currentKuota); ?>" required style="width:75px; text-align:center; padding:8px 6px; font-weight:800; font-size:1rem;">
                                <span style="font-size:0.78rem; font-weight:700; color:#64748B;">kuota</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="adm-btn adm-btn-primary" style="padding:12px 28px; font-size:0.95rem;">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan Kuota Jurusan
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function syncPeriodeTeks() {
    const tglMulai = document.getElementById('tanggal_mulai').value;
    const tglSelesai = document.getElementById('tanggal_selesai').value;
    if (!tglMulai || !tglSelesai) return;

    const bulanIndo = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];

    const d1 = new Date(tglMulai);
    const d2 = new Date(tglSelesai);

    if (isNaN(d1) || isNaN(d2)) return;

    const t1 = d1.getDate();
    const b1 = bulanIndo[d1.getMonth()];
    const y1 = d1.getFullYear();

    const t2 = d2.getDate();
    const b2 = bulanIndo[d2.getMonth()];
    const y2 = d2.getFullYear();

    let teks = "";
    if (y1 === y2) {
        if (b1 === b2) {
            teks = `${t1} - ${t2} ${b1} ${y1}`;
        } else {
            teks = `${t1} ${b1} - ${t2} ${b2} ${y1}`;
        }
    } else {
        teks = `${t1} ${b1} ${y1} - ${t2} ${b2} ${y2}`;
    }

    document.getElementById('periode_gelombang').value = teks;
}

// AJAX form submit handlers
['form-settings', 'form-kuota'].forEach(id => {
    const f = document.getElementById(id);
    if (f) {
        f.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = f.querySelector('button[type="submit"]');
            const origHtml = btn ? btn.innerHTML : '';
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner" style="animation:spin 1s linear infinite"></i> Menyimpan...'; }

            fetch('index.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(f)
            })
            .then(r => r.json())
            .then(data => {
                if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(() => {
                if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
                f.submit();
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../spmb/admin/footer.php'; ?>
