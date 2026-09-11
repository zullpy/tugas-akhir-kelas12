<?php
// spmb/admin/pengaturan.php
// Pengaturan Sistem SPMB SMKS SUKAPURA
$adminPageTitle = 'Pengaturan SPMB';
$adminPageHeading = 'Pengaturan Portal &amp; Akun Administrator';

require_once __DIR__ . '/header.php';

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

        $success = "Pengaturan portal SPMB berhasil disimpan!";
        $settings = get_all_settings(); // refresh
    } elseif ($action === 'kuota') {
        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        foreach ($DAFTAR_JURUSAN as $kode => $j) {
            $inputKey = 'kuota_' . $kode;
            if (isset($_POST[$inputKey])) {
                $val = max(1, (int)$_POST[$inputKey]);
                $ins->execute([$inputKey, (string)$val]);
            }
        }
        $success = "Kapasitas kuota siswa per jurusan berhasil diperbarui!";
        $settings = get_all_settings(); // refresh & sync
    } elseif ($action === 'fonnte') {
        $token = trim($_POST['fonnte_token'] ?? '');
        $status = isset($_POST['fonnte_status']) ? '1' : '0';
        $baseUrl = trim($_POST['base_url'] ?? '');

        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        $ins->execute(['fonnte_token', $token]);
        $ins->execute(['fonnte_status', $status]);
        $ins->execute(['base_url', $baseUrl]);

        $success = "Pengaturan integrasi WhatsApp Gateway (Fonnte) berhasil disimpan!";
        $settings = get_all_settings();
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
                <form action="pengaturan.php" method="POST">
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

                    <!-- <div class="spmb-form-group">
                        <label class="spmb-label">
                            <i class="ph-bold ph-money"></i> Biaya Pendaftaran (Static)
                        </label>
                        <div style="background:#F8FAFC; border:var(--border-thin); border-radius:8px; padding:10px 14px; font-size:0.85rem; font-weight:700; color:#334155; line-height:1.5;">
                            <div>&bull; Hanya 100.000 (Reguler)</div>
                            <div>&bull; 50.000 (Prestasi)</div>
                            <div>&bull; Gratis (Yatim/Piatu) + Mendapatkan Baju Putih Abu</div>
                        </div>
                        <input type="hidden" name="biaya_pendaftaran" value="• Hanya 100.000 (Reguler)&#10;• 50.000 (Prestasi)&#10;• Gratis (Yatim/Piatu) + Mendapatkan Baju Putih Abu">
                        <span style="font-size:0.75rem; color:#64748B; margin-top:4px; display:block;">
                            <i class="ph-bold ph-lock-key"></i> Biaya pendaftaran dikunci static 3 baris sesuai ketentuan sekolah.
                        </span>
                    </div> -->

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
                <form action="pengaturan.php" method="POST">
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
                                    <img src="../../assets/jurusan/<?php echo strtolower($kode); ?>.png" alt="<?php echo $kode; ?>" style="width:28px; height:28px; object-fit:contain;">
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

                <form action="pengaturan.php" method="POST" style="margin-bottom:18px;">
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
                    <form action="pengaturan.php" method="POST" style="display:flex; gap:8px;">
                        <input type="hidden" name="action" value="test_wa">
                        <input type="text" name="no_test" class="spmb-input" placeholder="08xxxxxxxxxx" required style="padding:8px 10px; font-size:0.85rem;" value="<?php echo htmlspecialchars($settings['hotline_wa'] ?? ''); ?>">
                        <button type="submit" class="adm-btn adm-btn-success" style="padding:8px 14px; font-size:0.85rem; white-space:nowrap;">
                            <i class="ph-bold ph-paper-plane-right"></i> Tes WA
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-lock-key"></i> Keamanan Akun Admin
                </h3>
            </div>
            <div class="adm-card-body">
                <form action="pengaturan.php" method="POST">
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
                        <td style="padding:4px 0; font-weight:700;">Tabel SPMB</td>
                        <td>: <code>spmb_pendaftar</code>, <code>spmb_admin</code>, <code>spmb_pengaturan</code></td>
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
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
