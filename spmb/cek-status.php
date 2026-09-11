<?php
// spmb/cek-status.php
// Cek Status Pendaftaran Calon Siswa Baru SPMB SMKS SUKAPURA
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'cek-status';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

$keyword = sanitize_input($_GET['no'] ?? ($_GET['nisn'] ?? ($_POST['keyword'] ?? '')));
$pendaftar = null;
$searched = false;
$notFound = false;

if (!empty($keyword)) {
    $searched = true;
    $stmt = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `no_pendaftaran` = ? OR `nisn` = ? OR `nik` = ? LIMIT 1");
    $stmt->execute([$keyword, $keyword, $keyword]);
    $pendaftar = $stmt->fetch();
    if (!$pendaftar) {
        $notFound = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran | SPMB SMKS SUKAPURA</title>
    <meta name="description" content="Lacak status verifikasi berkas dan hasil seleksi pendaftaran SPMB SMKS Sukapura secara online.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="spmb.css">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <div class="spmb-hero" style="padding: 45px 0;">
        <div class="spmb-container" style="text-align:center;">
            <div class="spmb-badge-pill"><i class="ph-bold ph-magnifying-glass"></i> Tracking SPMB</div>
            <h1 class="spmb-hero-title" style="font-size:clamp(1.8rem, 3.5vw, 2.5rem); margin-bottom:10px;">
                Cek Status Pendaftaran
            </h1>
            <p class="spmb-hero-subtitle" style="margin:0 auto;">
                Masukkan Nomor Registrasi (contoh: <code>REG-2026-0001</code>) atau 10 digit NISN calon siswa.
            </p>
        </div>
    </div>

    <main class="spmb-container" style="padding: 40px 15px 70px;">
        <div style="max-width: 800px; margin: 0 auto;">

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'edited'): ?>
                <div class="spmb-alert spmb-alert-success" style="padding:18px 24px; margin-bottom:25px; border:2.5px solid var(--nb-black); box-shadow:var(--shadow-md);">
                    <i class="ph-bold ph-check-circle" style="font-size:1.8rem; color:#15803D;"></i>
                    <div>
                        <strong style="font-size:1.08rem; color:#166534;">Perbaikan Berhasil Disimpan!</strong><br>
                        Data dan berkas pendaftaran Anda telah berhasil diperbarui dan diajukan ulang. Status saat ini kembali menjadi <strong>Menunggu Verifikasi</strong> untuk ditinjau oleh panitia.
                    </div>
                </div>
            <?php endif; ?>

            <!-- SEARCH BOX -->
            <div style="background:var(--nb-white); border:var(--border-thick); border-radius:var(--radius-card); box-shadow:var(--shadow-lg); padding:30px; margin-bottom:35px;">
                <form action="cek-status.php" method="GET" style="display:flex; gap:12px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:240px;">
                        <input type="text" name="no" class="spmb-input" placeholder="Masukkan Nomor Registrasi / NISN..." value="<?php echo htmlspecialchars($keyword); ?>" required style="font-size:1.05rem; padding:14px 18px;">
                    </div>
                    <button type="submit" class="spmb-btn spmb-btn-primary" style="padding:14px 26px;">
                        <i class="ph-bold ph-magnifying-glass"></i> Cari Data
                    </button>
                </form>
            </div>

            <!-- RESULT: NOT FOUND -->
            <?php if ($notFound): ?>
                <div class="spmb-alert spmb-alert-danger" style="padding:24px;">
                    <i class="ph-bold ph-warning-circle" style="font-size:1.8rem;"></i>
                    <div>
                        <strong style="font-size:1.1rem;">Data Tidak Ditemukan!</strong><br>
                        Tidak ada pendaftar dengan kata kunci <strong>"<?php echo htmlspecialchars($keyword); ?>"</strong>. Pastikan Anda memasukkan Nomor Pendaftaran (misal <code>REG-2026-0001</code>) atau 10 digit NISN yang terdaftar saat mengisi formulir.
                        <div style="margin-top:12px;">
                            <a href="daftar.php" class="spmb-btn spmb-btn-sm spmb-btn-primary">
                                <i class="ph-bold ph-pencil-simple"></i> Buat Pendaftaran Baru
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- RESULT: FOUND -->
            <?php if ($pendaftar): ?>
                <div style="background:var(--nb-white); border:var(--border-thick); border-radius:var(--radius-card); box-shadow:var(--shadow-xl); overflow:hidden;">
                    
                    <!-- Card Top Header with Status -->
                    <div style="padding:25px 30px; background:var(--nb-gray-bg); border-bottom:var(--border-md); display:flex; justify-content:space-between; align-items:center; flex-wrap:gap; gap:16px;">
                        <div>
                            <span style="font-size:0.85rem; font-weight:700; color:#666; text-transform:uppercase;">Nomor Registrasi</span>
                            <h2 style="font-family:var(--font-heading); font-size:1.5rem; font-weight:800; color:var(--nb-navy-dark); margin-top:2px;">
                                <?php echo htmlspecialchars($pendaftar['no_pendaftaran']); ?>
                            </h2>
                        </div>
                        <div>
                            <?php echo get_status_badge_html($pendaftar['status']); ?>
                        </div>
                    </div>

                    <div style="padding:30px;">
                        
                        <!-- Catatan Status Alert -->
                        <div style="margin-bottom:28px;">
                            <?php if ($pendaftar['status'] === 'Diterima'): 
                                $jDiterima = !empty($pendaftar['jurusan_diterima']) ? $pendaftar['jurusan_diterima'] : $pendaftar['jurusan_1'];
                                $isDialihkan = (!empty($pendaftar['jurusan_2']) && $jDiterima === $pendaftar['jurusan_2'] && $pendaftar['jurusan_1'] !== $pendaftar['jurusan_2']);
                            ?>
                                <div class="spmb-alert spmb-alert-success" style="background:#DCFCE7; border:2px solid #15803D;">
                                    <i class="ph-bold ph-check-circle" style="font-size:2rem; color:#15803D; flex-shrink:0;"></i>
                                    <div>
                                        <strong style="font-size:1.15rem; color:#166534;">Selamat! Anda Diterima di SMKS Sukapura</strong>
                                        <div style="margin-top:6px; font-size:0.95rem; color:#14532D;">
                                            Dinyatakan Lulus pada Kompetensi Keahlian: <strong><?php echo htmlspecialchars($jDiterima); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$jDiterima]['nama'] ?? ''); ?></strong>
                                            <?php if ($isDialihkan): ?>
                                                <div style="margin-top:4px; font-size:0.85rem; color:#B45309; background:#FEF3C7; padding:4px 8px; border-radius:6px; display:inline-block; font-weight:700;">
                                                    <i class="ph-bold ph-arrow-bend-right-down"></i> Dialihkan ke Pilihan 2 karena kuota Pilihan 1 telah terpenuhi.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="margin-top:8px; font-size:0.9rem; color:#333; border-top:1px dashed #86EFAC; padding-top:6px;">
                                            <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'] ?? 'Silakan segera cetak kartu bukti pendaftaran dan ikuti petunjuk daftar ulang.')); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($pendaftar['status'] === 'Perlu Perbaikan'): ?>
                                <div class="spmb-alert" style="background:#FEF3C7; border:2px solid #F59E0B; color:#92400E; padding:20px; display:flex; gap:16px; align-items:flex-start;">
                                    <i class="ph-bold ph-warning" style="font-size:2.2rem; color:#D97706; flex-shrink:0;"></i>
                                    <div style="flex:1;">
                                        <strong style="font-size:1.15rem; color:#78350F; display:block; margin-bottom:4px;">
                                            Pemberitahuan: Data / Berkas Pendaftaran Perlu Diperbaiki
                                        </strong>
                                        <div style="font-size:0.92rem; line-height:1.5; color:#92400E; margin-bottom:12px;">
                                            Panitia telah memeriksa data Anda dan menemukan beberapa hal yang perlu direvisi:
                                            <div style="background:#FFFFFF; border:1.5px solid #F59E0B; border-radius:8px; padding:10px 14px; margin-top:8px; font-weight:600; color:#B45309; white-space:pre-line;">
                                                <?php echo htmlspecialchars($pendaftar['catatan_admin'] ?: 'Mohon periksa kembali kesesuaian data identitas atau kejelasan foto berkas yang diunggah.'); ?>
                                            </div>
                                        </div>
                                        <a href="edit.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&nisn=<?php echo urlencode($pendaftar['nisn']); ?>" class="spmb-btn" style="background:#F59E0B; color:#0F172A; font-weight:800; border:2px solid #000; box-shadow:2px 2px 0 #000; padding:10px 20px; display:inline-flex; align-items:center; gap:8px;">
                                            <i class="ph-bold ph-pencil-simple-line" style="font-size:1.2rem;"></i> Perbaiki Data &amp; Unggah Ulang Berkas Sekarang
                                        </a>
                                    </div>
                                </div>
                            <?php elseif ($pendaftar['status'] === 'Cadangan'): ?>
                                <div class="spmb-alert spmb-alert-info" style="background-color:var(--nb-yellow-light);">
                                    <i class="ph-bold ph-clock" style="font-size:1.6rem;"></i>
                                    <div>
                                        <strong style="font-size:1.05rem;">Status: Daftar Tunggu (Cadangan)</strong><br>
                                        <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'] ?? 'Pilihan jurusan Anda saat ini penuh, berkas masuk dalam antrean cadangan tahap 2.')); ?>
                                    </div>
                                </div>
                            <?php elseif ($pendaftar['status'] === 'Ditolak'): ?>
                                <div class="spmb-alert spmb-alert-danger">
                                    <i class="ph-bold ph-x-circle" style="font-size:1.6rem;"></i>
                                    <div>
                                        <strong style="font-size:1.05rem;">Mohon Maaf, Berkas Belum Memenuhi Syarat</strong><br>
                                        <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'] ?? 'Berkas pendaftaran tidak memenuhi kualifikasi seleksi atau kuota jurusan pilihan telah terpenuhi seluruhnya.')); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="spmb-alert spmb-alert-info">
                                    <i class="ph-bold ph-hourglass-high" style="font-size:1.6rem;"></i>
                                    <div>
                                        <strong style="font-size:1.05rem;">Menunggu Verifikasi Berkas Administrasi</strong><br>
                                        <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'] ?? 'Data pendaftaran Anda telah tercatat dan sedang dalam antrean verifikasi tim panitia SPMB. Harap cek kembali secara berkala.')); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Jadwal Ujian / Tes jika ada -->
                        <?php if (!empty($pendaftar['jadwal_tes']) || !empty($pendaftar['ruang_tes'])): ?>
                            <div style="background:var(--nb-yellow-light); border:var(--border-md); border-radius:var(--radius-btn); padding:18px; margin-bottom:28px;">
                                <h4 style="font-family:var(--font-heading); font-weight:800; font-size:1.1rem; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                                    <i class="ph-bold ph-calendar"></i> Informasi Tes Minat &amp; Bakat
                                </h4>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                                    <div>
                                        <span style="font-size:0.85rem; font-weight:700; color:#555;">Jadwal / Waktu:</span><br>
                                        <strong><?php echo htmlspecialchars($pendaftar['jadwal_tes'] ?: 'Akan diumumkan'); ?></strong>
                                    </div>
                                    <div>
                                        <span style="font-size:0.85rem; font-weight:700; color:#555;">Lokasi / Ruang:</span><br>
                                        <strong><?php echo htmlspecialchars($pendaftar['ruang_tes'] ?: 'Akan diumumkan'); ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Table Biodata Siswa -->
                        <h3 style="font-family:var(--font-heading); font-size:1.2rem; font-weight:800; margin-bottom:14px; padding-bottom:8px; border-bottom:var(--border-thin);">
                            Rincian Biodata Calon Siswa
                        </h3>

                        <div style="display:grid; grid-template-columns:1fr 140px; gap:20px; align-items:start; margin-bottom:30px;">
                            <table style="width:100%; border-collapse:collapse; font-size:0.95rem;">
                                <tr>
                                    <td style="padding:7px 0; font-weight:700; width:170px;">Nama Lengkap</td>
                                    <td style="padding:7px 0;">: <strong><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0; font-weight:700;">NISN</td>
                                    <td style="padding:7px 0;">: <?php echo htmlspecialchars($pendaftar['nisn']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0; font-weight:700;">Jenis Kelamin</td>
                                    <td style="padding:7px 0;">: <?php echo $pendaftar['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0; font-weight:700;">Tempat, Tgl Lahir</td>
                                    <td style="padding:7px 0;">: <?php echo htmlspecialchars($pendaftar['tempat_lahir']) . ', ' . tgl_indo($pendaftar['tanggal_lahir']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0; font-weight:700;">Asal Sekolah</td>
                                    <td style="padding:7px 0;">: <?php echo htmlspecialchars($pendaftar['asal_sekolah']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0; font-weight:700;">Pilihan Jurusan 1</td>
                                    <td style="padding:7px 0;">: <strong><?php echo htmlspecialchars($pendaftar['jurusan_1']); ?></strong> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$pendaftar['jurusan_1']]['nama'] ?? ''); ?></td>
                                </tr>
                                <?php if (!empty($pendaftar['jurusan_2'])): ?>
                                <tr>
                                    <td style="padding:7px 0; font-weight:700;">Pilihan Jurusan 2</td>
                                    <td style="padding:7px 0;">: <?php echo htmlspecialchars($pendaftar['jurusan_2']); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$pendaftar['jurusan_2']]['nama'] ?? ''); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($pendaftar['status'] === 'Diterima'): 
                                    $jDiterimaRow = !empty($pendaftar['jurusan_diterima']) ? $pendaftar['jurusan_diterima'] : $pendaftar['jurusan_1'];
                                ?>
                                <tr style="background:#F0FDF4;">
                                    <td style="padding:8px 4px; font-weight:800; color:#15803D;">Jurusan Diterima</td>
                                    <td style="padding:8px 4px;">
                                        <div style="display:inline-flex; align-items:center; gap:8px;">
                                            <img src="../assets/jurusan/<?php echo strtolower($jDiterimaRow); ?>.png" alt="<?php echo $jDiterimaRow; ?>" style="width:22px; height:22px; object-fit:contain;">
                                            <strong style="color:#15803D; font-size:1.02rem;">: <?php echo htmlspecialchars($jDiterimaRow); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$jDiterimaRow]['nama'] ?? ''); ?></strong>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td style="padding:7px 0; font-weight:700;">Jalur Pendaftaran</td>
                                    <td style="padding:7px 0;">: <?php echo get_jalur_badge_html($pendaftar['jalur']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0; font-weight:700;">Tanggal Mendaftar</td>
                                    <td style="padding:7px 0;">: <?php echo tgl_indo($pendaftar['tanggal_daftar']); ?></td>
                                </tr>
                            </table>

                            <div style="text-align:center;">
                                <div style="width:120px; height:150px; border:var(--border-md); border-radius:8px; overflow:hidden; background:#f0f0f0; margin:0 auto; display:flex; align-items:center; justify-content:center; box-shadow:var(--shadow-sm);">
                                    <?php if (!empty($pendaftar['foto']) && file_exists(__DIR__ . '/uploads/' . $pendaftar['foto'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($pendaftar['foto']); ?>" alt="Foto Calon Siswa" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="color:#777; font-size:0.85rem; font-weight:700;">
                                            <i class="ph-bold ph-user" style="font-size:2rem; display:block; margin-bottom:4px;"></i>
                                            Pas Foto
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
                            <?php if ($pendaftar['status'] === 'Perlu Perbaikan'): ?>
                                <a href="edit.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&nisn=<?php echo urlencode($pendaftar['nisn']); ?>" class="spmb-btn" style="background:#F59E0B; color:#0F172A; font-weight:800; border:2px solid #000; box-shadow:2px 2px 0 #000;">
                                    <i class="ph-bold ph-pencil-simple-line"></i> Perbaiki Formulir &amp; Berkas
                                </a>
                            <?php elseif ($pendaftar['status'] === 'Menunggu Verifikasi'): ?>
                                <a href="edit.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&nisn=<?php echo urlencode($pendaftar['nisn']); ?>" class="spmb-btn spmb-btn-secondary">
                                    <i class="ph-bold ph-pencil-simple"></i> Edit Data Pendaftaran
                                </a>
                            <?php endif; ?>

                            <a href="cetak.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&from=cek-status" class="spmb-btn spmb-btn-primary">
                                <i class="ph-bold ph-printer"></i> Cetak Kartu Peserta
                            </a>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $settings['hotline_wa'] ?? '6281234567890'); ?>" target="_blank" class="spmb-btn spmb-btn-secondary">
                                <i class="ph-bold ph-question"></i> Bantuan Panitia
                            </a>
                        </div>

                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include '../components/footer.php'; ?>
</body>
</html>
