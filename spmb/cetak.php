<?php
// spmb/cetak.php
// Cetak Kartu Peserta / Bukti Pendaftaran SPMB SMKS SUKAPURA
require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

$no = sanitize_input($_GET['no'] ?? '');

if (empty($no)) {
    die("Nomor pendaftaran tidak valid.");
}

$stmt = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `no_pendaftaran` = ?");
$stmt->execute([$no]);
$p = $stmt->fetch();

if (!$p) {
    die("Data pendaftar tidak ditemukan.");
}

// Tentukan tujuan tombol Kembali secara dinamis
$from = sanitize_input($_GET['from'] ?? '');
$referer = $_SERVER['HTTP_REFERER'] ?? '';

if ($from === 'admin' || $from === 'pendaftar') {
    $backUrl = 'admin/pendaftar.php';
    $backLabel = 'Kembali ke Panel Admin';
} elseif ($from === 'detail') {
    $backUrl = 'admin/detail.php?id=' . (int)($p['id'] ?? 0);
    $backLabel = 'Kembali ke Detail Siswa';
} elseif ($from === 'sukses') {
    $backUrl = 'sukses.php?no=' . urlencode($p['no_pendaftaran']);
    $backLabel = 'Kembali';
} else {
    // Default untuk calon siswa / link WhatsApp / publik
    $backUrl = 'cek-status.php?no=' . urlencode($p['no_pendaftaran']);
    $backLabel = 'Kembali ke Cek Status';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta SPMB - <?php echo htmlspecialchars($p['no_pendaftaran']); ?> - <?php echo htmlspecialchars($p['nama_lengkap']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" href="spmb.css?v=<?php echo @filemtime(__DIR__ . '/spmb.css') ?: time(); ?>">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
</head>
<body style="background:#f4f4f4; padding:20px 0;">

    <div class="no-print" style="max-width:800px; margin:0 auto 20px; display:flex; justify-content:space-between; align-items:center; background:#fff; padding:15px 25px; border:var(--border-md); border-radius:10px; box-shadow:var(--shadow-md);">
        <a href="<?php echo htmlspecialchars($backUrl); ?>" class="spmb-btn spmb-btn-sm spmb-btn-secondary">
            <i class="ph-bold ph-arrow-left"></i> <?php echo $backLabel; ?>
        </a>
        <div style="font-weight:700;">
            <i class="ph-bold ph-printer"></i> Kartu Bukti Pendaftaran SPMB
        </div>
        <button onclick="window.print();" class="spmb-btn spmb-btn-sm spmb-btn-primary">
            <i class="ph-bold ph-printer"></i> Cetak Dokumen
        </button>
    </div>

    <!-- PRINT CARD CONTAINER -->
    <div class="spmb-card-print">
        
        <!-- HEADER KOP SURAT -->
        <div class="spmb-print-header">
            <img src="../assets/favicon.ico" alt="Logo SMKS Sukapura" class="spmb-print-logo" onerror="this.src='https://via.placeholder.com/75?text=SMK';">
            <div class="spmb-print-title-box">
                <h3 style="margin:0; font-size:1rem; font-weight:700; letter-spacing:1px;">PANITIA PENERIMAAN PESERTA DIDIK BARU (SPMB)</h3>
                <h2 style="margin:2px 0 4px; font-size:1.45rem; font-weight:800; color:#0A4D68;">SMK SUKAPURA KABUPATEN TASIKMALAYA</h2>
                <p style="margin:0; font-size:0.8rem; line-height:1.3;">
                    Jl. Sukapura, Singaparna, Kec. Singaparna, Kabupaten Tasikmalaya, Jawa Barat<br>
                    Website: smksukapura.sch.id | Email: <?php echo htmlspecialchars($settings['email_spmb'] ?? 'spmb@smksukapura.sch.id'); ?> | Hotline: <?php echo htmlspecialchars($settings['hotline_wa'] ?? '0812-3456-7890'); ?>
                </p>
            </div>
        </div>

        <div style="text-align:center; margin-bottom:20px;">
            <div style="display:inline-block; border:2px solid #000; padding:6px 24px; font-family:var(--font-heading); font-weight:800; font-size:1.15rem; background:#FFE600; text-transform:uppercase;">
                KARTU TANDA BUKTI PENDAFTARAN SPMB TA <?php echo htmlspecialchars($settings['tahun_ajaran'] ?? '2026/2027'); ?>
            </div>
        </div>

        <!-- BODY DATA -->
        <div class="spmb-print-body">
            <div>
                <table class="spmb-print-table">
                    <tr>
                        <td>Nomor Pendaftaran</td>
                        <td>: <strong style="font-size:1.15rem; letter-spacing:1px; background:#f0f0f0; padding:2px 8px; border:1px solid #000;"><?php echo htmlspecialchars($p['no_pendaftaran']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>NISN / NIK</td>
                        <td>: <?php echo htmlspecialchars($p['nisn']); ?> / <?php echo htmlspecialchars($p['nik']); ?></td>
                    </tr>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>: <strong style="font-size:1.05rem;"><?php echo strtoupper(htmlspecialchars($p['nama_lengkap'])); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>: <?php echo $p['jenis_kelamin'] === 'L' ? 'Laki-laki (L)' : 'Perempuan (P)'; ?></td>
                    </tr>
                    <tr>
                        <td>Tempat, Tgl Lahir</td>
                        <td>: <?php echo htmlspecialchars($p['tempat_lahir']) . ', ' . tgl_indo($p['tanggal_lahir']); ?></td>
                    </tr>
                    <tr>
                        <td>Asal Sekolah (SMP)</td>
                        <td>: <?php echo htmlspecialchars($p['asal_sekolah']); ?></td>
                    </tr>
                    <tr>
                        <td>Alamat Calon Siswa</td>
                        <td>: <?php echo htmlspecialchars($p['alamat']); ?></td>
                    </tr>
                    <tr>
                        <td>No. HP / WhatsApp</td>
                        <td>: <?php echo htmlspecialchars($p['no_hp']); ?></td>
                    </tr>
                    <tr>
                        <td>Jurusan Pilihan 1</td>
                        <td>: <strong style="color:#0A4D68;"><?php echo htmlspecialchars($p['jurusan_1']); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$p['jurusan_1']]['nama'] ?? ''); ?></strong></td>
                    </tr>
                    <?php if (!empty($p['jurusan_2'])): ?>
                    <tr>
                        <td>Jurusan Pilihan 2</td>
                        <td>: <?php echo htmlspecialchars($p['jurusan_2']); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$p['jurusan_2']]['nama'] ?? ''); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($p['status'] === 'Diterima'): 
                        $jDiterimaCetak = !empty($p['jurusan_diterima']) ? $p['jurusan_diterima'] : $p['jurusan_1'];
                    ?>
                    <tr style="background:#DCFCE7;">
                        <td><strong>JURUSAN DITERIMA</strong></td>
                        <td>: <strong style="color:#15803D; font-size:1.05rem;"><?php echo htmlspecialchars($jDiterimaCetak); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$jDiterimaCetak]['nama'] ?? ''); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Jalur Pendaftaran</td>
                        <td>: <?php echo htmlspecialchars($p['jalur']); ?></td>
                    </tr>
                    <tr>
                        <td>Nama Orang Tua/Wali</td>
                        <td>: <?php echo htmlspecialchars($p['nama_ayah']); ?> / <?php echo htmlspecialchars($p['nama_ibu']); ?></td>
                    </tr>
                    <tr>
                        <td>Status Pendaftaran</td>
                        <td>: <strong style="text-transform:uppercase; color:<?php echo ($p['status'] === 'Diterima') ? '#15803D' : '#000'; ?>;"><?php echo htmlspecialchars($p['status']); ?></strong></td>
                    </tr>
                </table>
            </div>

            <!-- FOTO & QR SIMULASI -->
            <div style="display:flex; flex-direction:column; align-items:center; gap:16px;">
                <div class="spmb-print-photo-box">
                    <?php if (!empty($p['foto']) && file_exists(__DIR__ . '/uploads/' . $p['foto'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($p['foto']); ?>" alt="Foto">
                    <?php else: ?>
                        <div style="padding:10px;">
                            <i class="ph-bold ph-user" style="font-size:2.5rem;"></i><br>
                            PAS FOTO<br>3 x 4
                        </div>
                    <?php endif; ?>
                </div>

                <div style="border:1.5px solid #000; padding:8px 12px; background:#fff; text-align:center; width:150px;">
                    <div style="font-family:monospace; font-size:0.75rem; letter-spacing:2px; font-weight:800;">
                        ||| | |||| | ||| ||<br>
                        ||| || ||| || ||||
                    </div>
                    <span style="font-size:0.7rem; font-weight:700;"><?php echo htmlspecialchars($p['no_pendaftaran']); ?></span>
                </div>
            </div>
        </div>

        <!-- JADWAL & RUANG TES -->
        <div style="margin-top:20px; border:2px solid #000; padding:12px 16px; background:#FFFDF0; font-size:0.9rem;">
            <div style="font-weight:800; text-transform:uppercase; margin-bottom:4px; font-size:0.95rem;">
                <i class="ph-bold ph-info"></i> Petunjuk &amp; Jadwal Ujian:
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:6px;">
                <div>Jadwal Pelaksanaan: <strong><?php echo htmlspecialchars($p['jadwal_tes'] ?: 'Menunggu Jadwal Panitia'); ?></strong></div>
                <div>Ruang Tes/Wawancara: <strong><?php echo htmlspecialchars($p['ruang_tes'] ?: 'Menunggu Informasi Panitia'); ?></strong></div>
            </div>
            <div style="font-size:0.8rem; color:#444; margin-top:6px;">
                * Bawalah cetakan kartu tanda bukti ini beserta seluruh kelengkapan berkas fisik persyaratan saat proses <strong>Daftar Ulang</strong> di sekretariat SPMB SMKS Sukapura.
            </div>
        </div>

        <!-- TANDA TANGAN -->
        <div class="spmb-print-footer">
            <div style="text-align:center;">
                <p style="margin:0; font-size:0.85rem;">Calon Peserta Didik,</p>
                <div style="height:60px;"></div>
                <p style="margin:0; font-weight:700; text-decoration:underline; font-size:0.95rem;">
                    <?php echo strtoupper(htmlspecialchars($p['nama_lengkap'])); ?>
                </p>
                <span style="font-size:0.8rem;">NISN: <?php echo htmlspecialchars($p['nisn']); ?></span>
            </div>

            <div style="text-align:center;">
                <p style="margin:0; font-size:0.85rem;">Tasikmalaya, <?php echo tgl_indo($p['tanggal_daftar']); ?><br>Panitia SPMB SMKS Sukapura,</p>
                <div style="height:60px; display:flex; align-items:center; justify-content:center;">
                    <div style="border:1.5px dashed #0A4D68; color:#0A4D68; padding:3px 12px; font-size:0.75rem; font-weight:800; transform:rotate(-5deg); border-radius:4px;">
                        TERVERIFIKASI SISTEM
                    </div>
                </div>
                <p style="margin:0; font-weight:700; text-decoration:underline; font-size:0.95rem;">
                    PANITIA SPMB
                </p>
                <span style="font-size:0.8rem;">NIP/NUPTK Panitia</span>
            </div>
        </div>

    </div>

</body>
</html>
