<?php
// spmb/admin/detail.php
// Detail & Verifikasi Pendaftar SPMB
$adminPageTitle = 'Detail Pendaftar';
$adminPageHeading = 'Detail &amp; Verifikasi Berkas Pendaftar';

require_once __DIR__ . '/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: pendaftar.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `id` = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    header("Location: pendaftar.php");
    exit;
}

$error = '';
$success = '';
$infoAlert = '';

// Ambil info kuota pilihan jurusan
$infoP1 = get_jurusan_info_kuota($pdo, $p['jurusan_1'], $p['id']);
$infoP2 = !empty($p['jurusan_2']) ? get_jurusan_info_kuota($pdo, $p['jurusan_2'], $p['id']) : null;

// Update Verifikasi Status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statusBaru       = sanitize_input($_POST['status'] ?? $p['status']);
    $catatanAdmin     = sanitize_input($_POST['catatan_admin'] ?? '');
    $jadwalTes        = sanitize_input($_POST['jadwal_tes'] ?? '');
    $ruangTes         = sanitize_input($_POST['ruang_tes'] ?? '');
    $jurusanDiterima  = sanitize_input($_POST['jurusan_diterima'] ?? ($p['jurusan_diterima'] ?? ''));

    // Validasi status Perlu Perbaikan wajib ada catatan
    if ($statusBaru === 'Perlu Perbaikan' && empty(trim($catatanAdmin))) {
        $error = "Untuk status <strong>Perlu Perbaikan</strong>, Anda wajib menuliskan Catatan Panitia agar calon siswa mengetahui data atau berkas mana yang harus diperbaiki.";
    } else {
        // Logika Otomatisasi Kuota saat memilih Diterima
        if ($statusBaru === 'Diterima') {
            // Karena input catatan tidak ditampilkan saat Diterima, kosongkan catatan revisi
            $catatanAdmin = '';

            // Jika admin tidak memilih jurusan spesifik secara eksplisit atau memilih auto
            if (empty($jurusanDiterima) || $jurusanDiterima === 'AUTO') {
                $eval = evaluasi_kuota_pendaftar($pdo, $p['id'], $p['jurusan_1'], $p['jurusan_2'] ?? null);
                if ($eval['status'] === 'Diterima') {
                    $jurusanDiterima = $eval['jurusan_diterima'];
                    if (!empty($eval['dialihkan'])) {
                        // Dialihkan ke Pilihan 2
                        $catatanAdmin = "[Sistem: Kuota Pilihan 1 (" . $p['jurusan_1'] . ") penuh. Otomatis dialihkan & diterima di Pilihan 2 (" . $jurusanDiterima . ")]";
                        $infoAlert = "<strong>Perhatian Alokasi Kuota:</strong> Kuota Pilihan 1 (" . $p['jurusan_1'] . ") sudah penuh. Calon siswa otomatis dialihkan dan <strong>DITERIMA di Pilihan 2 (" . $jurusanDiterima . ")</strong>.";
                    }
                } else {
                    // Pilihan 1 & 2 sama-sama penuh -> Auto ditolak
                    $statusBaru = 'Ditolak';
                    $jurusanDiterima = null;
                    $catatanAdmin = "[Sistem: Otomatis Ditolak karena kuota Pilihan 1 (" . $p['jurusan_1'] . ")" . (!empty($p['jurusan_2']) ? " dan Pilihan 2 (" . $p['jurusan_2'] . ")" : "") . " telah terpenuhi seluruhnya]";
                    $infoAlert = "<strong>Peringatan Kuota Penuh:</strong> Kuota penerimaan untuk Pilihan 1 (" . $p['jurusan_1'] . ")" . (!empty($p['jurusan_2']) ? " dan Pilihan 2 (" . $p['jurusan_2'] . ")" : "") . " sudah PENUH. Status calon siswa otomatis ditetapkan menjadi <strong>Ditolak</strong>.";
                }
            }
        } else {
            // Jika bukan Diterima, jurusan_diterima diset null
            $jurusanDiterima = null;
        }

        $upd = $pdo->prepare("UPDATE `spmb_pendaftar` SET 
            `status` = ?, 
            `jurusan_diterima` = ?,
            `catatan_admin` = ?, 
            `jadwal_tes` = ?, 
            `ruang_tes` = ? 
            WHERE `id` = ?");
        $upd->execute([$statusBaru, $jurusanDiterima, $catatanAdmin, $jadwalTes, $ruangTes, $id]);

        $success = "Status verifikasi dan data pendaftar berhasil diperbarui!";
        
        // Refresh data pendaftar & kuota
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        $infoP1 = get_jurusan_info_kuota($pdo, $p['jurusan_1'], $p['id']);
        $infoP2 = !empty($p['jurusan_2']) ? get_jurusan_info_kuota($pdo, $p['jurusan_2'], $p['id']) : null;
    }
}
?>

<?php if (!empty($error)): ?>
    <div class="spmb-alert spmb-alert-danger" style="margin-bottom:20px;">
        <i class="ph-bold ph-warning-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $error; ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($infoAlert)): ?>
    <div class="spmb-alert spmb-alert-info" style="margin-bottom:20px; background:#FFFBEB; border-color:#F59E0B; color:#92400E;">
        <i class="ph-bold ph-bell-ringing" style="font-size:1.5rem; color:#B45309;"></i>
        <div><?php echo $infoAlert; ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="spmb-alert spmb-alert-success" style="margin-bottom:20px;">
        <i class="ph-bold ph-check-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $success; ?></div>
    </div>
<?php endif; ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <a href="pendaftar.php" class="adm-btn adm-btn-secondary">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Daftar
    </a>
    <div style="display:flex; gap:10px;">
        <a href="../cetak.php?no=<?php echo urlencode($p['no_pendaftaran']); ?>&from=detail" class="adm-btn adm-btn-primary">
            <i class="ph-bold ph-printer"></i> Cetak Kartu Peserta
        </a>
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $p['no_hp']); ?>" target="_blank" class="adm-btn adm-btn-success">
            <i class="ph-bold ph-whatsapp-logo"></i> Hubungi Calon Siswa
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px;">

    <!-- KOLOM KIRI: BIODATA LENGKAP -->
    <div>
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-user-circle"></i> Biodata Calon Peserta Didik
                </h3>
                <div>
                    <?php echo get_status_badge_html($p['status']); ?>
                </div>
            </div>
            <div class="adm-card-body">
                
                <div style="display:flex; gap:20px; margin-bottom:24px; align-items:center;">
                    <div style="width:90px; height:120px; border:var(--border-md); border-radius:8px; overflow:hidden; background:#f0f0f0; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <?php if (!empty($p['foto']) && file_exists(__DIR__ . '/../uploads/' . $p['foto'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($p['foto']); ?>" alt="Foto" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <i class="ph-bold ph-user" style="font-size:2.5rem; color:#888;"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span style="font-size:0.8rem; font-weight:700; color:#666; text-transform:uppercase;">No. Registrasi</span>
                        <h2 style="font-family:var(--font-heading); font-size:1.6rem; font-weight:800; color:var(--adm-navy-dark);">
                            <?php echo htmlspecialchars($p['no_pendaftaran']); ?>
                        </h2>
                        <h3 style="font-size:1.15rem; font-weight:700; margin-top:2px;">
                            <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                        </h3>
                        <span style="font-size:0.85rem; color:#555;">Didaftarkan pada: <?php echo date('d M Y, H:i', strtotime($p['tanggal_daftar'])); ?> WIB</span>
                    </div>
                </div>

                <h4 style="font-family:var(--font-heading); font-size:1.05rem; font-weight:800; border-bottom:var(--border-thin); padding-bottom:6px; margin:20px 0 12px;">
                    Data Pribadi
                </h4>
                <table style="width:100%; border-collapse:collapse; font-size:0.92rem;">
                    <tr>
                        <td style="padding:6px 0; font-weight:700; width:170px;">NISN</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($p['nisn']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">NIK</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($p['nik']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Jenis Kelamin</td>
                        <td style="padding:6px 0;">: <?php echo $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Tempat, Tgl Lahir</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($p['tempat_lahir']) . ', ' . tgl_indo($p['tanggal_lahir']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">No. WhatsApp Siswa</td>
                        <td style="padding:6px 0;">: <a href="tel:<?php echo htmlspecialchars($p['no_hp']); ?>" style="color:var(--adm-navy); font-weight:700;"><?php echo htmlspecialchars($p['no_hp']); ?></a></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Asal Sekolah (SMP)</td>
                        <td style="padding:6px 0;">: <strong><?php echo htmlspecialchars($p['asal_sekolah']); ?></strong></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700; vertical-align:top;">Alamat Lengkap</td>
                        <td style="padding:6px 0;">: <?php echo nl2br(htmlspecialchars($p['alamat'])); ?></td>
                    </tr>
                </table>

                <h4 style="font-family:var(--font-heading); font-size:1.05rem; font-weight:800; border-bottom:var(--border-thin); padding-bottom:6px; margin:24px 0 12px;">
                    Pilihan Jurusan &amp; Jalur
                </h4>
                <table style="width:100%; border-collapse:collapse; font-size:0.92rem;">
                    <tr>
                        <td style="padding:6px 0; font-weight:700; width:155px; vertical-align:middle;">Pilihan Utama (1)</td>
                        <td style="padding:6px 0;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; background:#F8FAFC; border:1px solid #E2E8F0; padding:8px 12px; border-radius:8px;">
                                <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                                    <img src="../../assets/jurusan/<?php echo strtolower($p['jurusan_1']); ?>.png" alt="<?php echo $p['jurusan_1']; ?>" style="width:26px; height:26px; object-fit:contain; flex-shrink:0;">
                                    <div style="min-width:0; line-height:1.35;">
                                        <strong style="color:var(--adm-navy); font-size:0.95rem;"><?php echo htmlspecialchars($p['jurusan_1']); ?></strong>
                                        <span style="color:#475569; font-size:0.85rem;"> — <?php echo htmlspecialchars($DAFTAR_JURUSAN[$p['jurusan_1']]['nama'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <span class="spmb-badge" style="flex-shrink:0; white-space:nowrap; <?php echo $infoP1['penuh'] ? 'background:#FEE2E2; color:#DC2626; border:1px solid #FCA5A5;' : 'background:#DCFCE7; color:#15803D; border:1px solid #86EFAC;'; ?> font-size:0.75rem;">
                                    <i class="ph-bold <?php echo $infoP1['penuh'] ? 'ph-x-circle' : 'ph-check-circle'; ?>"></i>
                                    Kuota: <?php echo $infoP1['terisi']; ?>/<?php echo $infoP1['total']; ?> (<?php echo $infoP1['penuh'] ? 'PENUH' : 'Sisa ' . $infoP1['sisa']; ?>)
                                </span>
                            </div>
                        </td>
                    </tr>
                    <?php if (!empty($p['jurusan_2'])): ?>
                    <tr>
                        <td style="padding:6px 0; font-weight:700; vertical-align:middle;">Pilihan Cadangan (2)</td>
                        <td style="padding:6px 0;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; background:#F8FAFC; border:1px solid #E2E8F0; padding:8px 12px; border-radius:8px;">
                                <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                                    <img src="../../assets/jurusan/<?php echo strtolower($p['jurusan_2']); ?>.png" alt="<?php echo $p['jurusan_2']; ?>" style="width:26px; height:26px; object-fit:contain; flex-shrink:0;">
                                    <div style="min-width:0; line-height:1.35;">
                                        <strong style="color:#0F172A; font-size:0.95rem;"><?php echo htmlspecialchars($p['jurusan_2']); ?></strong>
                                        <span style="color:#475569; font-size:0.85rem;"> — <?php echo htmlspecialchars($DAFTAR_JURUSAN[$p['jurusan_2']]['nama'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <?php if ($infoP2): ?>
                                    <span class="spmb-badge" style="flex-shrink:0; white-space:nowrap; <?php echo $infoP2['penuh'] ? 'background:#FEE2E2; color:#DC2626; border:1px solid #FCA5A5;' : 'background:#DCFCE7; color:#15803D; border:1px solid #86EFAC;'; ?> font-size:0.75rem;">
                                        <i class="ph-bold <?php echo $infoP2['penuh'] ? 'ph-x-circle' : 'ph-check-circle'; ?>"></i>
                                        Kuota: <?php echo $infoP2['terisi']; ?>/<?php echo $infoP2['total']; ?> (<?php echo $infoP2['penuh'] ? 'PENUH' : 'Sisa ' . $infoP2['sisa']; ?>)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($p['status'] === 'Diterima' && !empty($p['jurusan_diterima'])): ?>
                    <tr>
                        <td style="padding:6px 0; font-weight:800; color:#166534; vertical-align:middle;">Diterima di Jurusan</td>
                        <td style="padding:6px 0;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; background:#F0FDF4; border:1.5px solid #86EFAC; padding:8px 12px; border-radius:8px;">
                                <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                                    <img src="../../assets/jurusan/<?php echo strtolower($p['jurusan_diterima']); ?>.png" alt="<?php echo $p['jurusan_diterima']; ?>" style="width:28px; height:28px; object-fit:contain; flex-shrink:0;">
                                    <div style="min-width:0; line-height:1.35;">
                                        <strong style="color:#166534; font-size:1rem;"><?php echo htmlspecialchars($p['jurusan_diterima']); ?></strong>
                                        <span style="color:#15803D; font-size:0.88rem;"> — <?php echo htmlspecialchars($DAFTAR_JURUSAN[$p['jurusan_diterima']]['nama'] ?? ''); ?></span>
                                        <?php if (!empty($p['jurusan_2']) && $p['jurusan_diterima'] === $p['jurusan_2'] && $p['jurusan_1'] !== $p['jurusan_2']): ?>
                                            <span class="spmb-badge" style="background:#FEF3C7; color:#B45309; border:1px solid #F59E0B; font-size:0.72rem; margin-left:6px; vertical-align:middle;">Dialihkan dari P1</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="spmb-badge spmb-badge-success" style="flex-shrink:0; white-space:nowrap; font-size:0.75rem;">
                                    <i class="ph-bold ph-check-circle"></i> Lulus Seleksi
                                </span>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Jalur Pendaftaran</td>
                        <td style="padding:6px 0;">: <?php echo get_jalur_badge_html($p['jalur']); ?></td>
                    </tr>
                    <?php if ($p['jalur'] === 'Prestasi' || !empty($p['berkas_prestasi'])): ?>
                    <tr>
                        <td style="padding:10px 0; font-weight:700; vertical-align:top;">Sertifikat Prestasi</td>
                        <td style="padding:10px 0;">
                            <?php if (!empty($p['berkas_prestasi'])): 
                                $extPrestasi = strtolower(pathinfo($p['berkas_prestasi'], PATHINFO_EXTENSION));
                                $isImg = in_array($extPrestasi, ['jpg', 'jpeg', 'png', 'webp']);
                            ?>
                                <div style="display:inline-flex; align-items:center; gap:12px; background:#FFFBEB; border:1.5px solid #F59E0B; border-radius:8px; padding:10px 14px;">
                                    <?php if ($isImg): ?>
                                        <a href="../uploads/<?php echo htmlspecialchars($p['berkas_prestasi']); ?>" target="_blank" title="Klik untuk melihat ukuran penuh">
                                            <img src="../uploads/<?php echo htmlspecialchars($p['berkas_prestasi']); ?>" alt="Sertifikat Prestasi" style="width:65px; height:65px; object-fit:cover; border-radius:6px; border:1px solid #D97706; box-shadow:1px 1px 0px rgba(0,0,0,0.1);">
                                        </a>
                                    <?php else: ?>
                                        <div style="width:52px; height:52px; background:#FEE2E2; border-radius:6px; display:flex; align-items:center; justify-content:center; border:1px solid #EF4444;">
                                            <i class="ph-bold ph-file-pdf" style="font-size:2rem; color:#DC2626;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight:700; font-size:0.92rem; color:#92400E;">Sertifikat / Piagam Kejuaraan</div>
                                        <a href="../uploads/<?php echo htmlspecialchars($p['berkas_prestasi']); ?>" target="_blank" style="display:inline-flex; align-items:center; gap:5px; font-weight:700; color:#0A4D68; font-size:0.85rem; text-decoration:underline; margin-top:3px;">
                                            <i class="ph-bold ph-arrow-square-out"></i> Buka / Unduh Dokumen
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span style="color:#DC2626; font-style:italic; font-size:0.9rem;">
                                    <i class="ph-bold ph-warning"></i> Belum ada file sertifikat yang diunggah
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>

                <h4 style="font-family:var(--font-heading); font-size:1.05rem; font-weight:800; border-bottom:var(--border-thin); padding-bottom:6px; margin:24px 0 12px;">
                    Data Orang Tua / Wali
                </h4>
                <table style="width:100%; border-collapse:collapse; font-size:0.92rem;">
                    <tr>
                        <td style="padding:6px 0; font-weight:700; width:170px;">Nama Ayah</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($p['nama_ayah']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Nama Ibu</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($p['nama_ibu']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Pekerjaan Orang Tua</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($p['pekerjaan_ortu'] ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">No. WhatsApp Orang Tua</td>
                        <td style="padding:6px 0;">: <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $p['no_hp_ortu']); ?>" target="_blank" style="color:var(--adm-navy); font-weight:700;"><?php echo htmlspecialchars($p['no_hp_ortu']); ?></a></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Rata-rata Penghasilan</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($p['penghasilan_ortu'] ?: '-'); ?></td>
                    </tr>
                </table>

                <h4 style="font-family:var(--font-heading); font-size:1.05rem; font-weight:800; border-bottom:var(--border-thin); padding-bottom:6px; margin:28px 0 14px; display:flex; align-items:center; gap:8px;">
                    <i class="ph-bold ph-files" style="color:var(--adm-navy);"></i>
                    <span>Berkas &amp; Dokumen Persyaratan Siswa</span>
                </h4>

                <?php
                $renderDocItem = function($label, $filename, $icon, $isRequired = true) {
                    echo '<div style="background:#FFFFFF; border:1.5px solid #CBD5E1; border-radius:10px; padding:12px 14px; display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px; box-shadow:1.5px 1.5px 0 rgba(0,0,0,0.03);">';
                    echo '<div style="display:flex; align-items:center; gap:12px; min-width:0;">';
                    
                    if (!empty($filename)) {
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                        if ($isImg) {
                            echo '<a href="../uploads/' . htmlspecialchars($filename) . '" target="_blank" title="Klik untuk memperbesar">';
                            echo '<img src="../uploads/' . htmlspecialchars($filename) . '" alt="' . htmlspecialchars($label) . '" style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1.5px solid #0A4D68;">';
                            echo '</a>';
                        } else {
                            echo '<div style="width:48px; height:48px; background:#FEE2E2; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#DC2626; font-size:1.6rem; border:1px solid #FCA5A5;">';
                            echo '<i class="ph-bold ph-file-pdf"></i>';
                            echo '</div>';
                        }
                        echo '<div style="min-width:0;">';
                        echo '<div style="font-weight:800; font-size:0.92rem; color:#0F172A;">' . htmlspecialchars($label) . '</div>';
                        echo '<div style="font-size:0.75rem; color:#64748B; word-break:break-all;">' . htmlspecialchars($filename) . '</div>';
                        echo '</div>';
                        echo '</div>'; // end left flex
                        
                        echo '<a href="../uploads/' . htmlspecialchars($filename) . '" target="_blank" class="adm-btn adm-btn-sm adm-btn-secondary" style="white-space:nowrap; flex-shrink:0;">';
                        echo '<i class="ph-bold ph-arrow-square-out"></i> Buka File';
                        echo '</a>';
                    } else {
                        echo '<div style="width:48px; height:48px; background:#F1F5F9; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#94A3B8; font-size:1.6rem; border:1px solid #CBD5E1;">';
                        echo '<i class="ph-bold ' . $icon . '"></i>';
                        echo '</div>';
                        echo '<div style="min-width:0;">';
                        echo '<div style="font-weight:800; font-size:0.92rem; color:#64748B;">' . htmlspecialchars($label) . '</div>';
                        echo '<div style="font-size:0.78rem; color:' . ($isRequired ? '#DC2626' : '#94A3B8') . '; font-style:italic;">' . ($isRequired ? 'Belum diunggah' : 'Tidak dilampirkan (Opsional)') . '</div>';
                        echo '</div>';
                        echo '</div>'; // end left flex
                        echo '<span class="spmb-badge" style="background:#F1F5F9; color:#64748B; border:1px solid #CBD5E1; font-size:0.75rem;">' . ($isRequired ? 'Kosong' : 'Opsional') . '</span>';
                    }
                    
                    echo '</div>';
                };

                $renderDocItem('Kartu Keluarga (KK)', $p['berkas_kk'] ?? '', 'ph-identification-card', true);
                $renderDocItem('Akta Kelahiran', $p['berkas_akta'] ?? '', 'ph-scroll', true);
                $renderDocItem('Ijazah / SKL SMP', $p['berkas_ijazah'] ?? '', 'ph-certificate', true);
                $renderDocItem('KTP Orang Tua / Wali', $p['berkas_ktp_ortu'] ?? '', 'ph-users-three', true);
                $renderDocItem('Kartu Indonesia Pintar (KIP / PIP)', $p['berkas_kip'] ?? '', 'ph-credit-card', false);
                if ($p['jalur'] === 'Prestasi' || !empty($p['berkas_prestasi'])) {
                    $renderDocItem('Sertifikat Prestasi', $p['berkas_prestasi'] ?? '', 'ph-trophy', true);
                }
                ?>

            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: FORM VERIFIKASI -->
    <div>
        <div class="adm-card">
            <div class="adm-card-header" style="background:var(--adm-yellow);">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-shield-check"></i> Form Verifikasi Panitia
                </h3>
            </div>
            <div class="adm-card-body">
                <!-- Ringkasan Live Kuota Siswa -->
                <div style="background:#F8FAFC; border:1.5px solid #CBD5E1; border-radius:10px; padding:12px 14px; margin-bottom:18px;">
                    <div style="font-weight:800; font-size:0.85rem; color:#475569; text-transform:uppercase; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                        <i class="ph-bold ph-chart-pie-slice"></i> Status Kuota Pilihan Calon Siswa
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px; font-size:0.88rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span><strong>P1:</strong> <?php echo htmlspecialchars($p['jurusan_1']); ?></span>
                            <span class="spmb-badge" style="<?php echo $infoP1['penuh'] ? 'background:#FEE2E2; color:#DC2626; border:1px solid #FCA5A5;' : 'background:#DCFCE7; color:#15803D; border:1px solid #86EFAC;'; ?> font-size:0.75rem;">
                                <?php echo $infoP1['terisi']; ?>/<?php echo $infoP1['total']; ?> (<?php echo $infoP1['penuh'] ? 'PENUH' : 'Sisa ' . $infoP1['sisa']; ?>)
                            </span>
                        </div>
                        <?php if ($infoP2): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span><strong>P2:</strong> <?php echo htmlspecialchars($p['jurusan_2']); ?></span>
                            <span class="spmb-badge" style="<?php echo $infoP2['penuh'] ? 'background:#FEE2E2; color:#DC2626; border:1px solid #FCA5A5;' : 'background:#DCFCE7; color:#15803D; border:1px solid #86EFAC;'; ?> font-size:0.75rem;">
                                <?php echo $infoP2['terisi']; ?>/<?php echo $infoP2['total']; ?> (<?php echo $infoP2['penuh'] ? 'PENUH' : 'Sisa ' . $infoP2['sisa']; ?>)
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:0.75rem; color:#64748B; margin-top:8px; line-height:1.4;">
                        <i class="ph-bold ph-info"></i> Bila diset <strong>Diterima</strong>, sistem otomatis mengalihkan ke P2 jika P1 penuh, dan otomatis <strong>Ditolak</strong> jika keduanya penuh.
                    </div>
                </div>

                <form action="detail.php?id=<?php echo $p['id']; ?>" method="POST">
                    
                    <div class="spmb-form-group">
                        <label class="spmb-label" for="status">Keputusan Status Seleksi <span class="required">*</span></label>
                        <select name="status" id="status" class="spmb-select" required style="font-weight:800; font-size:1rem;" onchange="toggleFormFields()">
                            <option value="Menunggu Verifikasi" <?php echo ($p['status'] === 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                            <option value="Diterima" <?php echo ($p['status'] === 'Diterima') ? 'selected' : ''; ?>>Diterima (Lulus Seleksi)</option>
                            <option value="Cadangan" <?php echo ($p['status'] === 'Cadangan') ? 'selected' : ''; ?>>Cadangan (Daftar Tunggu)</option>
                            <option value="Perlu Perbaikan" <?php echo ($p['status'] === 'Perlu Perbaikan') ? 'selected' : ''; ?>>Perlu Perbaikan (Data/Dokumen Kurang)</option>
                            <option value="Ditolak" <?php echo ($p['status'] === 'Ditolak') ? 'selected' : ''; ?>>Ditolak / Tidak Memenuhi Syarat</option>
                        </select>
                    </div>

                    <!-- Dropdown Alokasi Jurusan Diterima -->
                    <div class="spmb-form-group" id="group_jurusan_diterima" style="<?php echo ($p['status'] === 'Diterima') ? '' : 'display:none;'; ?>">
                        <label class="spmb-label" for="jurusan_diterima">Alokasi Jurusan Diterima</label>
                        <select name="jurusan_diterima" id="jurusan_diterima" class="spmb-select" style="font-weight:700;">
                            <option value="AUTO">-- Otomatis Sesuai Kuota (P1 ➔ P2) --</option>
                            <option value="<?php echo htmlspecialchars($p['jurusan_1']); ?>" <?php echo ($p['jurusan_diterima'] === $p['jurusan_1']) ? 'selected' : ''; ?>>
                                Pilihan 1: <?php echo htmlspecialchars($p['jurusan_1']); ?> (Sisa: <?php echo $infoP1['sisa']; ?>)
                            </option>
                            <?php if (!empty($p['jurusan_2']) && $infoP2): ?>
                                <option value="<?php echo htmlspecialchars($p['jurusan_2']); ?>" <?php echo ($p['jurusan_diterima'] === $p['jurusan_2']) ? 'selected' : ''; ?>>
                                    Pilihan 2: <?php echo htmlspecialchars($p['jurusan_2']); ?> (Sisa: <?php echo $infoP2['sisa']; ?>)
                                </option>
                            <?php endif; ?>
                        </select>
                        <div class="spmb-input-helper">Pilih 'Otomatis' agar sistem memprioritaskan Pilihan 1 jika masih ada slot.</div>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="jadwal_tes">Jadwal Ujian / Tes Minat Bakat</label>
                        <input type="text" name="jadwal_tes" id="jadwal_tes" class="spmb-input" placeholder="Contoh: 15 Mei 2026, 08.00 WIB" value="<?php echo htmlspecialchars($p['jadwal_tes'] ?? ''); ?>">
                        <div class="spmb-input-helper">Akan tampil di kartu peserta dan halaman tracking siswa.</div>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="ruang_tes">Ruangan Ujian / Wawancara</label>
                        <input type="text" name="ruang_tes" id="ruang_tes" class="spmb-input" placeholder="Contoh: Lab Komputer 1 / Ruang 102" value="<?php echo htmlspecialchars($p['ruang_tes'] ?? ''); ?>">
                    </div>

                    <!-- Catatan Panitia (Disembunyikan jika Diterima) -->
                    <div class="spmb-form-group" id="group_catatan_admin" style="<?php echo ($p['status'] === 'Diterima') ? 'display:none;' : ''; ?>">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label class="spmb-label" for="catatan_admin" style="margin-bottom:0;">Catatan Panitia untuk Siswa</label>
                            <span style="font-size:0.75rem; color:#666;">Bisa dibaca siswa di cek-status</span>
                        </div>

                        <!-- Tombol Template Cepat Catatan Revisi (Hanya tampil saat Perlu Perbaikan) -->
                        <div id="template_catatan_box" style="margin-bottom:8px; display:<?php echo ($p['status'] === 'Perlu Perbaikan') ? 'flex' : 'none'; ?>; flex-wrap:wrap; gap:6px;">
                            <span style="font-size:0.75rem; font-weight:700; color:#555; align-self:center; margin-right:2px;">Template Catatan:</span>
                            <button type="button" class="adm-badge-btn" onclick="addCatatan('NIK pada formulir berbeda dengan foto Kartu Keluarga (KK). Mohon dicek dan diperbaiki.')">+ NIK beda dgn KK</button>
                            <button type="button" class="adm-badge-btn" onclick="addCatatan('Nomor NISN belum valid atau tidak sesuai dengan data Dapodik. Mohon perbaiki 10 digit NISN Anda.')">+ NISN tidak sesuai</button>
                            <button type="button" class="adm-badge-btn" onclick="addCatatan('Foto/scan dokumen Kartu Keluarga buram atau tidak terbaca. Harap unggah ulang foto yang lebih jelas dan terang.')">+ KK buram</button>
                            <button type="button" class="adm-badge-btn" onclick="addCatatan('Dokumen Ijazah / Surat Keterangan Lulus (SKL) terpotong atau belum lengkap.')">+ Ijazah/SKL terpotong</button>
                            <button type="button" class="adm-badge-btn" onclick="addCatatan('Foto Akta Kelahiran tidak jelas atau nama tidak terbaca.')">+ Akta buram</button>
                            <button type="button" class="adm-badge-btn" onclick="addCatatan('Pas foto belum formal. Harap gunakan foto berpakaian rapi / seragam sekolah dengan latar belakang polos.')">+ Foto belum resmi</button>
                            <button type="button" class="adm-badge-btn" onclick="addCatatan('Sertifikat kejuaraan/prestasi tidak dapat divalidasi. Harap unggah piagam yang sah.')">+ Sertifikat tdk sah</button>
                        </div>

                        <textarea name="catatan_admin" id="catatan_admin" class="spmb-textarea" rows="4" placeholder="Tuliskan catatan verifikasi atau poin perbaikan data..."><?php echo htmlspecialchars($p['catatan_admin'] ?? ''); ?></textarea>
                        <div class="spmb-input-helper" id="catatan_helper">Jika status diset 'Perlu Perbaikan', jelaskan bagian data/berkas mana yang harus direvisi calon siswa.</div>
                    </div>

                    <button type="submit" class="adm-btn adm-btn-primary" style="width:100%; padding:14px; font-size:1rem;">
                        <i class="ph-bold ph-floppy-disk"></i> Simpan Hasil Verifikasi
                    </button>
                </form>

                <script>
                function toggleFormFields() {
                    const statusVal = document.getElementById('status').value;
                    const groupJurusan = document.getElementById('group_jurusan_diterima');
                    const groupCatatan = document.getElementById('group_catatan_admin');
                    const templateBox  = document.getElementById('template_catatan_box');

                    // Alokasi Jurusan hanya tampil jika Diterima
                    if (groupJurusan) {
                        groupJurusan.style.display = (statusVal === 'Diterima') ? 'block' : 'none';
                    }

                    // Sesuai permintaan: Jika status Diterima, input catatan tidak ada (disembunyikan)
                    if (groupCatatan) {
                        groupCatatan.style.display = (statusVal === 'Diterima') ? 'none' : 'block';
                    }

                    // Template revisi cepat hanya relevan dan tampil saat status Perlu Perbaikan
                    if (templateBox) {
                        templateBox.style.display = (statusVal === 'Perlu Perbaikan') ? 'flex' : 'none';
                    }

                    const helper = document.getElementById('catatan_helper');
                    if (helper) {
                        if (statusVal === 'Perlu Perbaikan') {
                            helper.innerHTML = '<strong style="color:#B45309;">Wajib diisi:</strong> Sebutkan kesalahan data/berkas agar calon siswa bisa memperbaikinya di formulir edit.';
                        } else {
                            helper.innerHTML = 'Catatan ini bisa dibaca oleh calon siswa saat cek status.';
                        }
                    }
                }

                // Inisialisasi saat halaman dimuat
                document.addEventListener('DOMContentLoaded', toggleFormFields);

                function addCatatan(text) {
                    const txt = document.getElementById('catatan_admin');
                    const current = txt.value.trim();
                    if (current === '') {
                        txt.value = text;
                    } else if (current.indexOf(text) === -1) {
                        txt.value = current + "\n• " + text;
                    }
                    txt.focus();
                }
                </script>
            </div>
        </div>

        <!-- QUICK INFO CARD -->
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-info"></i> Tindakan Lainnya
                </h3>
            </div>
            <div class="adm-card-body" style="display:flex; flex-direction:column; gap:10px;">
                <a href="../cetak.php?no=<?php echo urlencode($p['no_pendaftaran']); ?>&from=detail" class="adm-btn adm-btn-secondary" style="width:100%; justify-content:center;">
                    <i class="ph-bold ph-printer"></i> Cetak Kartu Bukti Pendaftaran
                </a>
                <a href="hapus.php?id=<?php echo $p['id']; ?>" class="adm-btn adm-btn-danger" style="width:100%; justify-content:center;" onclick="return confirm('Peringatan: Menghapus data pendaftar ini bersifat permanen!');">
                    <i class="ph-bold ph-trash"></i> Hapus Calon Siswa Ini
                </a>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
