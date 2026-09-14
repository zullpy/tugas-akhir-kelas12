<?php
// spmb/sukses.php
// Halaman Konfirmasi Sukses Pendaftaran SPMB SMKS SUKAPURA
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'sukses';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

$no_pendaftaran = sanitize_input($_GET['no'] ?? ($_SESSION['sukses_no_pendaftaran'] ?? ''));

if (empty($no_pendaftaran)) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `no_pendaftaran` = ?");
$stmt->execute([$no_pendaftaran]);
$pendaftar = $stmt->fetch();

if (!$pendaftar) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil | SPMB SMKS SUKAPURA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="spmb.css?v=<?php echo @filemtime(__DIR__ . '/spmb.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <main class="spmb-container" style="padding: 60px 15px;">
        <div style="max-width:700px; margin:0 auto; background:var(--nb-white); border:var(--border-thick); border-radius:var(--radius-card); box-shadow:var(--shadow-xl); padding:45px 35px; text-align:center;">
            
            <div style="width:80px; height:80px; background:var(--nb-green); border:var(--border-thick); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; box-shadow:var(--shadow-sm);">
                <i class="ph-bold ph-check" style="font-size:2.8rem; color:#000;"></i>
            </div>

            <div class="spmb-badge-pill" style="background:var(--nb-green-light); color:#0A4D68; margin-bottom:12px;">
                <i class="ph-bold ph-seal-check"></i> Registrasi Diterima Sistem
            </div>

            <h1 style="font-family:var(--font-heading); font-size:clamp(1.8rem, 3.2vw, 2.4rem); font-weight:800; margin-bottom:10px;">
                Selamat! Pendaftaran Berhasil
            </h1>
            <p style="font-size:1rem; color:#444; margin-bottom:28px;">
                Data calon siswa atas nama <strong><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></strong> telah berhasil tersimpan di sistem SPMB SMKS Sukapura.
            </p>

            <!-- Box Nomor Registrasi -->
            <div style="background:var(--nb-yellow); border:var(--border-thick); border-radius:var(--radius-btn); box-shadow:var(--shadow-md); padding:20px; margin-bottom:30px;">
                <div style="font-size:0.9rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--nb-navy-dark); margin-bottom:6px;">
                    Nomor Pendaftaran Resmi Anda
                </div>
                <div style="font-family:var(--font-heading); font-size:clamp(2rem, 4vw, 2.8rem); font-weight:800; letter-spacing:1px; color:#000;">
                    <?php echo htmlspecialchars($pendaftar['no_pendaftaran']); ?>
                </div>
                <div style="font-size:0.85rem; font-weight:600; color:#333; margin-top:4px;">
                    Simpan atau catat nomor registrasi ini untuk mengecek status seleksi.
                </div>
            </div>

            <!-- Detail Singkat -->
            <div style="text-align:left; background:var(--nb-gray-bg); border:var(--border-md); border-radius:var(--radius-btn); padding:20px; margin-bottom:30px;">
                <table style="width:100%; border-collapse:collapse; font-size:0.95rem;">
                    <tr>
                        <td style="padding:6px 0; font-weight:700; width:140px;">NISN</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($pendaftar['nisn']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Asal Sekolah</td>
                        <td style="padding:6px 0;">: <?php echo htmlspecialchars($pendaftar['asal_sekolah']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Jurusan Pilihan 1</td>
                        <td style="padding:6px 0;">: <strong><?php echo htmlspecialchars($pendaftar['jurusan_1']); ?></strong> (<?php echo htmlspecialchars($DAFTAR_JURUSAN[$pendaftar['jurusan_1']]['nama'] ?? ''); ?>)</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Jalur</td>
                        <td style="padding:6px 0;">: <?php echo get_jalur_badge_html($pendaftar['jalur']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; font-weight:700;">Status Awal</td>
                        <td style="padding:6px 0;">: <?php echo get_status_badge_html($pendaftar['status']); ?></td>
                    </tr>
                </table>
            </div>

            <div style="margin:20px 0; padding:12px 14px; background:#FFFDF0; border:1.5px solid #000; border-left:4px solid #0A4D68; border-radius:8px; font-size:0.85rem; text-align:left; color:#1E293B; line-height:1.4;">
                <i class="ph-bold ph-info" style="color:#0A4D68; margin-right:4px;"></i>
                <strong>Informasi Penting:</strong> Berkas fisik dokumen persyaratan (ijazah/SKL, KK, akta kelahiran, pas foto) <u>dibawa dan diserahkan saat proses Daftar Ulang</u> di sekretariat panitia sekolah.
            </div>

            <!-- Action Buttons -->
            <div style="display:flex; flex-direction:column; gap:14px;">
                <a href="cetak.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&from=sukses" class="spmb-btn spmb-btn-primary" style="font-size:1.05rem; padding:16px;">
                    <i class="ph-bold ph-printer"></i> Cetak Kartu Bukti Pendaftaran
                </a>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <a href="cek-status.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>" class="spmb-btn spmb-btn-secondary">
                        <i class="ph-bold ph-magnifying-glass"></i> Cek Status
                    </a>
                    <a href="index.php" class="spmb-btn spmb-btn-secondary">
                        <i class="ph-bold ph-house"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>

        </div>
    </main>

    <?php include '../components/footer.php'; ?>
</body>
</html>
