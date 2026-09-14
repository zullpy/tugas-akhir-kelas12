<?php
// setting/index.php
// Pusat Pengaturan Sistem & Konfigurasi Aplikasi SMKS SUKAPURA

// Load config & session DULU sebelum output apapun
require_once __DIR__ . '/../spmb/config.php';
check_admin_login(true, '../spmb/admin/login.php');

$pdo      = get_db_connection();
$settings = get_all_settings();

$success = '';
$error = '';

// Update Pengaturan Sistem
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
    } elseif ($action === 'fonnte') {
        $token = trim($_POST['fonnte_token'] ?? '');
        $status = isset($_POST['fonnte_status']) ? '1' : '0';
        $baseUrl = trim($_POST['base_url'] ?? '');

        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        $ins->execute(['fonnte_token', $token]);
        $ins->execute(['fonnte_status', $status]);
        $ins->execute(['base_url', $baseUrl]);

        $settings = get_all_settings();
        $success = "Pengaturan integrasi WhatsApp Gateway (Fonnte) berhasil disimpan!";

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success]);
            exit;
        }
    } elseif ($action === 'cloudinary') {
        $cName = trim($_POST['cloudinary_cloud_name'] ?? '');
        $cKey  = trim($_POST['cloudinary_api_key'] ?? '');
        $cSec  = trim($_POST['cloudinary_api_secret'] ?? '');

        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        $ins->execute(['cloudinary_cloud_name', $cName]);
        $ins->execute(['cloudinary_api_key', $cKey]);
        $ins->execute(['cloudinary_api_secret', $cSec]);

        $settings = get_all_settings();
        $success = "Pengaturan Cloudinary CDN berhasil disimpan!";

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success]);
            exit;
        }
    } elseif ($action === 'test_cloudinary') {
        $cName = trim($_POST['cloudinary_cloud_name'] ?? '');
        $cKey  = trim($_POST['cloudinary_api_key'] ?? '');
        $cSec  = trim($_POST['cloudinary_api_secret'] ?? '');

        $res = test_cloudinary_connection($cName, $cKey, $cSec);

        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    } elseif ($action === 'test_wa') {
        $noTes = trim($_POST['no_test'] ?? '');
        if (empty($noTes)) {
            $error = "Nomor WhatsApp untuk uji coba wajib diisi.";
        } else {
            $pesanTes = "🔔 *UJI COBA WHATSAPP GATEWAY (FONNTE)*\n\n"
                      . "Halo Administrator SMKS Sukapura,\n"
                      . "Ini adalah pesan verifikasi koneksi API Fonnte dari Portal SPMB SMKS Sukapura.\n\n"
                      . "✅ *Status: KONEKSI BERHASIL!*\n"
                      . "Waktu Kirim: " . date('d/m/Y H:i:s') . " WIB\n\n"
                      . "Sistem notifikasi otomatis SPMB siap digunakan.";
            
            $res = kirim_wa_fonnte($noTes, $pesanTes);
            if ($res['success']) {
                $success = "Uji coba kirim pesan WhatsApp ke nomor <strong>" . htmlspecialchars($noTes) . "</strong> BERHASIL!";
            } else {
                $error = "Uji coba WhatsApp GAGAL: " . htmlspecialchars($res['error'] ?? 'Terjadi kesalahan saat menghubungi API Fonnte');
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => !empty($success),
                    'message' => !empty($success) ? $success : $error
                ]);
                exit;
            }
        }
    } elseif ($action === 'password') {
        $lama = $_POST['pass_lama'] ?? '';
        $baru = $_POST['pass_baru'] ?? '';
        $konf = $_POST['pass_konfirm'] ?? '';

        if (empty($lama) || empty($baru) || empty($konf)) {
            $error = "Semua kolom password wajib diisi.";
        } elseif ($baru !== $konf) {
            $error = "Konfirmasi password baru tidak cocok.";
        } elseif (strlen($baru) < 6) {
            $error = "Password baru minimal 6 karakter.";
        } else {
            $adminId = $_SESSION['spmb_admin_id'];
            $chk = $pdo->prepare("SELECT `password` FROM `spmb_admin` WHERE `id` = ?");
            $chk->execute([$adminId]);
            $hash = $chk->fetchColumn();

            if (password_verify($lama, $hash)) {
                $newHash = password_hash($baru, PASSWORD_BCRYPT);
                $updPass = $pdo->prepare("UPDATE `spmb_admin` SET `password` = ? WHERE `id` = ?");
                $updPass->execute([$newHash, $adminId]);
                $success = "Password akun admin berhasil diubah!";
            } else {
                $error = "Password lama yang Anda masukkan salah.";
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => !empty($success),
                    'message' => !empty($success) ? $success : $error
                ]);
                exit;
            }
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

// Tampilkan HTML (setelah semua AJAX sudah exit)
$adminPageTitle   = 'Pengaturan Sistem';
$adminPageHeading = 'Pengaturan Sistem &amp; Akun Administrator';
require_once __DIR__ . '/../spmb/admin/header.php';
?>

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

<div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px;">

    <!-- PENGATURAN UMUM SPMB -->
    <div>
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
                        <label class="spmb-label">Metode &amp; Status Pendaftaran</label>
                        <select name="status_pendaftaran" id="status_pendaftaran" class="spmb-select" style="font-weight:800;">
                            <option value="otomatis" <?php echo (($settings['status_pendaftaran'] ?? '') === 'otomatis') ? 'selected' : ''; ?>>
                                OTOMATIS - Buka &amp; Tutup Otomatis Sesuai Tanggal Jadwal (Sangat Direkomendasikan)
                            </option>
                            <option value="buka" <?php echo (($settings['status_pendaftaran'] ?? '') === 'buka') ? 'selected' : ''; ?>>
                                BUKA MANUAL - Paksa Buka Sekarang (Mengabaikan Batas Tanggal)
                            </option>
                            <option value="tutup" <?php echo (($settings['status_pendaftaran'] ?? '') === 'tutup') ? 'selected' : ''; ?>>
                                TUTUP MANUAL - Paksa Tutup Sekarang (Mengabaikan Batas Tanggal)
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
                        <input type="text" name="pengumuman_header" class="spmb-input" value="<?php echo htmlspecialchars($settings['pengumuman_header'] ?? ''); ?>" required>
                    </div>

                    <button type="submit" class="adm-btn adm-btn-primary" style="padding:14px 28px;">
                        <i class="ph-bold ph-floppy-disk"></i> Simpan Konfigurasi Portal
                    </button>
                </form>
            </div>
        </div>

        <!-- DAYA TAMPUNG & KUOTA PER JURUSAN -->
        <div class="adm-card" style="margin-top:24px;">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-chart-donut"></i> Daya Tampung &amp; Kuota per Jurusan
                </h3>
                <span class="spmb-badge" style="background:var(--adm-yellow-light); border:var(--border-thin); font-size:0.8rem; font-weight:800;">
                    Total Pagu: <?php echo array_sum(array_column($DAFTAR_JURUSAN, 'kuota')); ?> Siswa
                </span>
            </div>
            <div class="adm-card-body">
                <form id="form-kuota" action="index.php" method="POST">
                    <input type="hidden" name="action" value="kuota">
                    <p style="font-size:0.85rem; color:#64748B; margin-bottom:16px;">
                        Tentukan batas maksimal calon siswa yang dapat diterima untuk setiap kompetensi keahlian. Angka ini akan otomatis mengubah target persentase pada dashboard dan kuota di web SPMB.
                    </p>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px; margin-bottom:20px;">
                        <?php foreach ($DAFTAR_JURUSAN as $kode => $j): 
                            $pendaftarCount = $currentApplicantCounts[$kode] ?? 0;
                            $currentKuota = $j['kuota'];
                        ?>
                            <div style="background:#FAFAFA; border:var(--border-md); border-radius:10px; padding:14px; display:flex; flex-direction:column; justify-content:space-between; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="../assets/jurusan/<?php echo strtolower($kode); ?>.png" alt="<?php echo $kode; ?>" style="width:28px; height:28px; object-fit:contain;">
                                    <div style="min-width:0;">
                                        <div style="font-weight:800; font-size:0.95rem; line-height:1.2;"><?php echo $kode; ?></div>
                                        <div style="font-size:0.75rem; color:#64748B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($j['nama']); ?>"><?php echo htmlspecialchars($j['nama']); ?></div>
                                    </div>
                                </div>

                                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding-top:8px; border-top:1px dashed #E2E8F0;">
                                    <span style="font-size:0.78rem; font-weight:700; color:#475569;">
                                        Pendaftar: <strong><?php echo $pendaftarCount; ?></strong>
                                    </span>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <input type="number" name="kuota_<?php echo $kode; ?>" class="spmb-input" min="1" max="1000" value="<?php echo htmlspecialchars($currentKuota); ?>" required style="width:75px; text-align:center; padding:6px 8px; font-weight:800; font-size:0.95rem;">
                                        <span style="font-size:0.8rem; font-weight:700; color:#64748B;">siswa</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="adm-btn adm-btn-primary" style="padding:12px 24px;">
                        <i class="ph-bold ph-floppy-disk"></i> Simpan Kuota Jurusan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: INTEGRASI WHATSAPP & KEAMANAN -->
    <div>
        <!-- INTEGRASI WHATSAPP GATEWAY (FONNTE) -->
        <div class="adm-card" style="margin-bottom:24px;">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-whatsapp-logo" style="color:#22C55E;"></i> Integrasi WhatsApp Gateway (Fonnte)
                </h3>
                <?php 
                $isFonnteAktif = (!empty($settings['fonnte_token']) && ($settings['fonnte_status'] ?? '1') === '1');
                ?>
                <span class="spmb-badge" style="background:<?php echo $isFonnteAktif ? '#DCFCE7' : '#FEE2E2'; ?>; color:<?php echo $isFonnteAktif ? '#166534' : '#991B1B'; ?>; font-weight:800; font-size:0.75rem; border:1px solid currentColor;">
                    <i class="ph-bold <?php echo $isFonnteAktif ? 'ph-check-circle' : 'ph-x-circle'; ?>"></i>
                    <?php echo $isFonnteAktif ? 'TERHUBUNG' : 'BELUM AKTIF'; ?>
                </span>
            </div>
            <div class="adm-card-body">
                <p style="font-size:0.83rem; color:#64748B; margin-bottom:14px; line-height:1.5;">
                    Notifikasi otomatis akan dikirimkan langsung ke nomor WhatsApp calon siswa saat status pendaftaran diperbarui (Diterima + PDF Kartu, Cadangan, Perlu Perbaikan + Link Edit, &amp; Ditolak + Pesan Motivasi).
                </p>

                <form id="form-fonnte" action="index.php" method="POST" style="margin-bottom:18px;">
                    <input type="hidden" name="action" value="fonnte">

                    <div class="spmb-form-group">
                        <label class="spmb-label">
                            Token API Fonnte <span class="required">*</span>
                        </label>
                        <input type="text" name="fonnte_token" class="spmb-input" placeholder="Masukkan token API akun Fonnte Anda" value="<?php echo htmlspecialchars($settings['fonnte_token'] ?? ''); ?>" required style="font-family:monospace; font-size:0.9rem;">
                        <span style="font-size:0.75rem; color:#64748B; margin-top:4px; display:block;">
                            Dapatkan token API pada dashboard <a href="https://fonnte.com" target="_blank" style="color:#2563EB; font-weight:700; text-decoration:underline;">Fonnte.com</a>.
                        </span>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Base URL Web SPMB</label>
                        <?php 
                        $defaultBase = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');
                        ?>
                        <input type="url" name="base_url" class="spmb-input" placeholder="<?php echo $defaultBase; ?>" value="<?php echo htmlspecialchars($settings['base_url'] ?? $defaultBase); ?>" required>
                        <span style="font-size:0.75rem; color:#64748B; margin-top:4px; display:block;">
                            Digunakan untuk menyusun tautan unduh Kartu Peserta dan link Perbaikan Formulir pada pesan WhatsApp.
                        </span>
                    </div>

                    <div class="spmb-form-group">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.88rem; font-weight:700; color:#1E293B;">
                            <input type="checkbox" name="fonnte_status" value="1" <?php echo (($settings['fonnte_status'] ?? '1') === '1') ? 'checked' : ''; ?> style="width:18px; height:18px; accent-color:#16A34A;">
                            <span>Aktifkan Pengiriman WhatsApp Otomatis</span>
                        </label>
                    </div>

                    <button type="submit" class="adm-btn adm-btn-primary" style="width:100%; padding:10px;">
                        <i class="ph-bold ph-floppy-disk"></i> Simpan Pengaturan Fonnte
                    </button>
                </form>

                <!-- FORM UJI COBA PESAN -->
                <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:8px; padding:12px 14px;">
                    <div style="font-weight:800; font-size:0.85rem; color:#1E293B; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <i class="ph-bold ph-paper-plane-tilt" style="color:#0284C7;"></i> Uji Coba Pengiriman Pesan WA
                    </div>
                    <form action="index.php" method="POST" style="display:flex; gap:8px;">
                        <input type="hidden" name="action" value="test_wa">
                        <input type="text" name="no_test" class="spmb-input" placeholder="08xxxxxxxxxx" required style="padding:8px 10px; font-size:0.85rem;" value="<?php echo htmlspecialchars($settings['hotline_wa'] ?? ''); ?>">
                        <button type="submit" class="adm-btn adm-btn-success" style="padding:8px 14px; font-size:0.85rem; white-space:nowrap;">
                            <i class="ph-bold ph-paper-plane-right"></i> Tes WA
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- INTEGRASI CLOUDINARY CDN -->
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-cloud" style="color:#0284C7;"></i> Penyimpanan Foto Cloud (Cloudinary CDN)
                </h3>
                <?php 
                $cCfg = get_cloudinary_config();
                ?>
                <span class="spmb-badge" style="background:<?php echo $cCfg['configured'] ? '#DCFCE7' : '#FEF3C7'; ?>; color:<?php echo $cCfg['configured'] ? '#166534' : '#92400E'; ?>; font-weight:800; font-size:0.75rem; border:1px solid currentColor;">
                    <i class="ph-bold <?php echo $cCfg['configured'] ? 'ph-check-circle' : 'ph-info'; ?>"></i>
                    <?php echo $cCfg['configured'] ? 'TERHUBUNG (' . htmlspecialchars($cCfg['cloud_name']) . ')' : 'SERVER LOKAL (DEFAULT)'; ?>
                </span>
            </div>
            <div class="adm-card-body">
                <p style="font-size:0.88rem; color:#64748B; margin-bottom:16px; line-height:1.5;">
                    Simpan seluruh foto galeri dokumentasi sekolah di Cloudinary. Akses gambar akan jauh lebih cepat melalui CDN global dan tidak membebani kapasitas hosting server Anda.
                </p>

                <form id="form-cloudinary" action="index.php" method="POST">
                    <input type="hidden" name="action" value="cloudinary">

                    <div class="adm-form-group">
                        <label class="adm-form-label">
                            <span class="label-title"><i class="ph-bold ph-cloud"></i> Cloud Name</span>
                            <span class="label-hint">Nama akun cloud Anda</span>
                        </label>
                        <div class="adm-input-icon-group">
                            <i class="ph-bold ph-globe input-icon-left"></i>
                            <input type="text" name="cloudinary_cloud_name" class="adm-input" placeholder="Contoh: smksukapura" value="<?php echo htmlspecialchars($settings['cloudinary_cloud_name'] ?? env('CLOUDINARY_CLOUD_NAME', '')); ?>" style="font-family:monospace; font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-form-label">
                            <span class="label-title"><i class="ph-bold ph-key"></i> API Key</span>
                            <span class="label-hint">Kunci API publik</span>
                        </label>
                        <div class="adm-input-icon-group">
                            <i class="ph-bold ph-fingerprint input-icon-left"></i>
                            <input type="text" name="cloudinary_api_key" class="adm-input" placeholder="Contoh: 123456789012345" value="<?php echo htmlspecialchars($settings['cloudinary_api_key'] ?? env('CLOUDINARY_API_KEY', '')); ?>" style="font-family:monospace; font-size:0.9rem;">
                        </div>
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-form-label">
                            <span class="label-title"><i class="ph-bold ph-lock-key"></i> API Secret</span>
                            <span class="label-hint">Kunci rahasia API</span>
                        </label>
                        <div class="adm-input-icon-group">
                            <i class="ph-bold ph-shield-check input-icon-left"></i>
                            <input type="password" name="cloudinary_api_secret" id="settings_cloud_secret" class="adm-input" placeholder="Contoh: abcdEFGHIJKLMnop12345" value="<?php echo htmlspecialchars($settings['cloudinary_api_secret'] ?? env('CLOUDINARY_API_SECRET', '')); ?>" style="font-family:monospace; font-size:0.9rem; padding-right:44px;">
                            <button type="button" onclick="const f = document.getElementById('settings_cloud_secret'); const ic = this.querySelector('i'); if (f.type==='password') { f.type='text'; ic.className='ph-bold ph-eye-slash'; } else { f.type='password'; ic.className='ph-bold ph-eye'; }" style="position:absolute; right:14px; background:none; border:none; cursor:pointer; color:#64748B; font-size:1.15rem; display:flex; align-items:center;">
                                <i class="ph-bold ph-eye"></i>
                            </button>
                        </div>
                        <span class="spmb-help" style="margin-top:6px; display:block;">
                            Dapatkan kredensial gratis di <a href="https://cloudinary.com" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">Cloudinary.com</a>. Jika dikosongkan, file disimpan di server lokal.
                        </span>
                    </div>

                    <div style="margin-top:24px; display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="submit" class="adm-btn adm-btn-navy" style="font-weight:800; padding:10px 20px;">
                            <i class="ph-bold ph-floppy-disk"></i> Simpan Pengaturan Cloudinary
                        </button>
                        <button type="button" onclick="testCloudinarySetting()" class="adm-btn adm-btn-warning" style="font-weight:800; padding:10px 18px;">
                            <i class="ph-bold ph-plugs-connected"></i> Uji Koneksi
                        </button>
                        <a href="../spmb/admin/galeri.php" class="adm-btn adm-btn-yellow" style="font-weight:800; padding:10px 20px;">
                            <i class="ph-bold ph-images"></i> Buka Galeri
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-lock-key"></i> Keamanan Akun Admin
                </h3>
            </div>
            <div class="adm-card-body">
                <form id="form-password" action="index.php" method="POST">
                    <input type="hidden" name="action" value="password">

                    <div class="spmb-form-group">
                        <label class="spmb-label">Username Saat Ini</label>
                        <input type="text" class="spmb-input" value="<?php echo htmlspecialchars($_SESSION['spmb_admin_user'] ?? 'admin'); ?>" disabled style="background:#f0f0f0;">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Password Lama <span class="required">*</span></label>
                        <input type="password" name="pass_lama" class="spmb-input" placeholder="Password lama" required>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Password Baru <span class="required">*</span></label>
                        <input type="password" name="pass_baru" class="spmb-input" placeholder="Minimal 6 karakter" required minlength="6">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Konfirmasi Password Baru <span class="required">*</span></label>
                        <input type="password" name="pass_konfirm" class="spmb-input" placeholder="Ketik ulang password baru" required minlength="6">
                    </div>

                    <button type="submit" class="adm-btn adm-btn-secondary" style="width:100%; padding:12px;">
                        <i class="ph-bold ph-key"></i> Perbarui Password
                    </button>
                </form>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-database"></i> Status Database MySQL
                </h3>
            </div>
            <div class="adm-card-body" style="font-size:0.88rem;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding:4px 0; font-weight:700;">Host</td>
                        <td>: <?php echo DB_HOST; ?></td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0; font-weight:700;">Database</td>
                        <td>: <code><?php echo DB_NAME; ?></code></td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0; font-weight:700;">Tabel Sistem</td>
                        <td>: <code>spmb_pendaftar</code>, <code>spmb_admin</code>, <code>spmb_pengaturan</code>, <code>galeri_foto</code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function syncPeriodeTeks() {
    const tgl1 = document.getElementById('tanggal_mulai').value;
    const tgl2 = document.getElementById('tanggal_selesai').value;
    if (tgl1 && tgl2) {
        const d1 = new Date(tgl1 + 'T00:00:00');
        const d2 = new Date(tgl2 + 'T00:00:00');
        const bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const formatTgl = (d) => `${d.getDate()} ${bulanIndo[d.getMonth()]} ${d.getFullYear()}`;
        const inputPeriode = document.getElementById('periode_gelombang');
        if (inputPeriode) {
            inputPeriode.value = `${formatTgl(d1)} - ${formatTgl(d2)}`;
        }
    }
}

// ─── AJAX Helper ───
function showAdmToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 3000, timerProgressBar: true
    });
    Toast.fire({ icon, title });
}

function attachAjaxForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner" style="animation:spin 1s linear infinite"></i> Menyimpan...'; }

        fetch('index.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            showAdmToast(data.success ? 'success' : 'error', data.message || (data.success ? 'Berhasil disimpan!' : 'Terjadi kesalahan.'));
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            showAdmToast('error', 'Koneksi gagal, coba lagi.');
        });
    });
}

function testCloudinarySetting() {
    const form = document.getElementById('form-cloudinary');
    const cloudName = form.querySelector('input[name="cloudinary_cloud_name"]').value.trim();
    const apiKey    = form.querySelector('input[name="cloudinary_api_key"]').value.trim();
    const apiSecret = form.querySelector('input[name="cloudinary_api_secret"]').value.trim();

    if (!cloudName || !apiKey || !apiSecret) {
        Swal.fire({
            icon: 'warning',
            title: 'Kredensial Belum Lengkap',
            text: 'Harap isi Cloud Name, API Key, dan API Secret sebelum menguji koneksi.'
        });
        return;
    }

    Swal.fire({
        title: 'Menguji Koneksi Cloudinary...',
        html: '<div style="display:flex; justify-content:center; padding:15px;"><i class="ph-bold ph-spinner" style="font-size:2rem; animation:spin 1s linear infinite;"></i></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    const fd = new FormData();
    fd.append('action', 'test_cloudinary');
    fd.append('cloudinary_cloud_name', cloudName);
    fd.append('cloudinary_api_key', apiKey);
    fd.append('cloudinary_api_secret', apiSecret);

    fetch('index.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Koneksi Berhasil!' : 'Koneksi Gagal',
            text: data.message
        });
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menghubungi Server',
            text: 'Terjadi kesalahan saat memproses pengujian koneksi.'
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    ['form-settings', 'form-kuota', 'form-fonnte', 'form-cloudinary', 'form-password'].forEach(attachAjaxForm);
});
</script>

<?php require_once __DIR__ . '/../spmb/admin/footer.php'; ?>
