<?php
// spmb/daftar.php
// Formulir Pendaftaran Siswa Baru (SPMB) SMKS SUKAPURA
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'daftar';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

// Cek apakah pendaftaran sedang dibuka (otomatis sesuai tanggal atau manual)
$isBuka = is_spmb_open($settings);
$statusInfo = get_spmb_status_info($settings);

$error = '';
$success = '';

// Pre-fill pilihan jurusan jika ada query string
$selectedJurusan = sanitize_input($_GET['jurusan'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isBuka) {
        $error = "Mohon maaf, pendaftaran siswa baru saat ini sedang ditutup oleh panitia.";
    } else {
        // Ambil dan sanitasi data POST
        $nisn            = sanitize_input($_POST['nisn'] ?? '');
        $nik             = sanitize_input($_POST['nik'] ?? '');
        $nama_lengkap    = sanitize_input($_POST['nama_lengkap'] ?? '');
        $jenis_kelamin   = sanitize_input($_POST['jenis_kelamin'] ?? '');
        $tempat_lahir    = sanitize_input($_POST['tempat_lahir'] ?? '');
        $tanggal_lahir   = sanitize_input($_POST['tanggal_lahir'] ?? '');
        $agama           = null;
        $no_hp           = sanitize_input($_POST['no_hp'] ?? '');
        $alamat          = sanitize_input($_POST['alamat'] ?? '');
        $asal_sekolah    = sanitize_input($_POST['asal_sekolah'] ?? '');
        $jurusan_1       = sanitize_input($_POST['jurusan_1'] ?? '');
        $jurusan_2       = sanitize_input($_POST['jurusan_2'] ?? '');
        $jalur           = sanitize_input($_POST['jalur'] ?? 'Reguler');
        $nama_ayah       = sanitize_input($_POST['nama_ayah'] ?? '');
        $nama_ibu        = sanitize_input($_POST['nama_ibu'] ?? '');
        $pekerjaan_ortu  = sanitize_input($_POST['pekerjaan_ortu'] ?? '');
        $pekerjaan_lain  = sanitize_input($_POST['pekerjaan_ortu_lainnya'] ?? '');
        if ($pekerjaan_ortu === 'Lainnya' && !empty($pekerjaan_lain)) {
            $pekerjaan_ortu = $pekerjaan_lain;
        }
        $no_hp_ortu      = sanitize_input($_POST['no_hp_ortu'] ?? '');
        $penghasilan_ortu= sanitize_input($_POST['penghasilan_ortu'] ?? '');

        // Validasi wajib
        if (empty($nisn) || empty($nik) || empty($nama_lengkap) || empty($jenis_kelamin) || 
            empty($tempat_lahir) || empty($tanggal_lahir) || empty($no_hp) || empty($alamat) || 
            empty($asal_sekolah) || empty($jurusan_1) || empty($nama_ayah) || empty($no_hp_ortu)) {
            $error = "Harap lengkapi semua kolom yang bertanda bintang merah (*).";
        } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
            $error = "NISN harus berupa 10 digit angka valid.";
        } elseif (!preg_match('/^[0-9]{16}$/', $nik)) {
            $error = "NIK harus berupa 16 digit angka valid sesuai KTP/KK.";
        } elseif ($jurusan_1 === $jurusan_2) {
            $error = "Jurusan Pilihan 1 dan Pilihan 2 tidak boleh sama.";
        } else {
            // Cek apakah NISN sudah pernah mendaftar
            $checkNisn = $pdo->prepare("SELECT `no_pendaftaran` FROM `spmb_pendaftar` WHERE `nisn` = ?");
            $checkNisn->execute([$nisn]);
            $existing = $checkNisn->fetch();

            if ($existing) {
                $error = "NISN <strong>$nisn</strong> sudah pernah didaftarkan dengan Nomor: <strong>{$existing['no_pendaftaran']}</strong>. Silakan gunakan menu <a href='cek-status.php?nisn=$nisn' style='color:#0A4D68; text-decoration:underline;'>Cek Status Pendaftaran</a>.";
            } else {
                // Upload File Helper Function
                $uploadFile = function($fieldKey, $prefix, $isRequired, $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'], $maxBytes = 3145728) use (&$error) {
                    if (!isset($_FILES[$fieldKey]) || $_FILES[$fieldKey]['error'] === UPLOAD_ERR_NO_FILE) {
                        if ($isRequired) {
                            $docLabels = [
                                'berkas_kk' => 'Kartu Keluarga (KK)',
                                'berkas_akta' => 'Akta Kelahiran',
                                'berkas_ijazah' => 'Ijazah / Surat Keterangan Lulus (SKL)',
                                'berkas_ktp_ortu' => 'KTP Orang Tua / Wali',
                                'berkas_prestasi' => 'Sertifikat / Piagam Prestasi'
                            ];
                            $lbl = $docLabels[$fieldKey] ?? $fieldKey;
                            $error = "Wajib mengunggah berkas <strong>$lbl</strong>.";
                        }
                        return null;
                    }

                    if ($_FILES[$fieldKey]['error'] !== UPLOAD_ERR_OK) {
                        $error = "Terjadi masalah saat mengunggah berkas $fieldKey (Error code: {$_FILES[$fieldKey]['error']}).";
                        return null;
                    }

                    $fileTmp  = $_FILES[$fieldKey]['tmp_name'];
                    $fileName = $_FILES[$fieldKey]['name'];
                    $fileSize = $_FILES[$fieldKey]['size'];
                    $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (!in_array($ext, $allowed)) {
                        $error = "Format file $fieldKey tidak sesuai. Hanya diperbolehkan format " . strtoupper(implode(', ', $allowed)) . ".";
                        return null;
                    }

                    if ($fileSize > $maxBytes) {
                        $maxMB = round($maxBytes / (1024 * 1024));
                        $error = "Ukuran file $fieldKey melebihi batas maksimal ({$maxMB} MB).";
                        return null;
                    }

                    $newFileName = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $destination = __DIR__ . '/uploads/' . $newFileName;
                    if (move_uploaded_file($fileTmp, $destination)) {
                        return $newFileName;
                    } else {
                        $error = "Gagal menyimpan berkas $fieldKey ke server. Pastikan izin folder uploads aktif.";
                        return null;
                    }
                };

                // Upload Semua Berkas
                $foto_path           = $uploadFile('foto', 'foto', false, ['jpg', 'jpeg', 'png', 'webp'], 2097152);
                $berkas_kk_path       = empty($error) ? $uploadFile('berkas_kk', 'kk', true) : null;
                $berkas_akta_path     = empty($error) ? $uploadFile('berkas_akta', 'akta', true) : null;
                $berkas_ijazah_path   = empty($error) ? $uploadFile('berkas_ijazah', 'ijazah', true) : null;
                $berkas_ktp_ortu_path = empty($error) ? $uploadFile('berkas_ktp_ortu', 'ktp', true) : null;
                $berkas_kip_path      = empty($error) ? $uploadFile('berkas_kip', 'kip', false) : null;
                
                $berkas_prestasi_path = null;
                if (empty($error) && $jalur === 'Prestasi') {
                    $berkas_prestasi_path = $uploadFile('berkas_prestasi', 'prestasi', true);
                }

                if (empty($error)) {
                    // Generate No Registrasi
                    $no_pendaftaran = generate_no_pendaftaran($pdo);

                    $insertStmt = $pdo->prepare("INSERT INTO `spmb_pendaftar` (
                        `no_pendaftaran`, `nisn`, `nik`, `nama_lengkap`, `jenis_kelamin`, 
                        `tempat_lahir`, `tanggal_lahir`, `agama`, `no_hp`, `alamat`, 
                        `asal_sekolah`, `jurusan_1`, `jurusan_2`, `jalur`, `nama_ayah`, 
                        `nama_ibu`, `pekerjaan_ortu`, `no_hp_ortu`, `penghasilan_ortu`, 
                        `foto`, `berkas_kk`, `berkas_akta`, `berkas_ijazah`, `berkas_ktp_ortu`, `berkas_kip`, `berkas_prestasi`, 
                        `status`, `catatan_admin`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Verifikasi', 'Pendaftaran berhasil dikirim. Menunggu verifikasi berkas oleh panitia.')");

                    $insertStmt->execute([
                        $no_pendaftaran, $nisn, $nik, $nama_lengkap, $jenis_kelamin,
                        $tempat_lahir, $tanggal_lahir, $agama, $no_hp, $alamat,
                        $asal_sekolah, $jurusan_1, $jurusan_2 ?: null, $jalur, $nama_ayah,
                        $nama_ibu, $pekerjaan_ortu, $no_hp_ortu, $penghasilan_ortu,
                        $foto_path, $berkas_kk_path, $berkas_akta_path, $berkas_ijazah_path, $berkas_ktp_ortu_path, $berkas_kip_path, $berkas_prestasi_path
                    ]);

                    // Simpan ke session untuk akses langsung di sukses.php
                    $_SESSION['sukses_no_pendaftaran'] = $no_pendaftaran;
                    header("Location: sukses.php?no=" . urlencode($no_pendaftaran));
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran Siswa Baru | SPMB SMKS SUKAPURA</title>
    <meta name="description" content="Formulir Pendaftaran Peserta Didik Baru Online SMKS Sukapura TA <?php echo htmlspecialchars($settings['tahun_ajaran'] ?? '2026/2027'); ?>">
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

    <!-- SOFT MODERN SPMB HEADER -->
    <div style="background: linear-gradient(180deg, #FFFDF0 0%, #FFF9D2 100%); border-bottom: 2px solid #000; padding: 36px 0 32px; box-shadow: 0 3px 0px rgba(0,0,0,0.05);">
        <div class="spmb-container" style="text-align:center;">
            <div style="display:inline-flex; align-items:center; gap:8px; background:#FFE600; border:1.5px solid #000; box-shadow:2px 2px 0 #000; padding:4px 16px; border-radius:999px; font-weight:800; font-size:0.8rem; text-transform:uppercase; margin-bottom:12px; color:#0A4D68;">
                <i class="ph-bold ph-pencil-simple"></i> Formulir SPMB Online
            </div>
            <h1 style="font-family:var(--font-heading); font-size:clamp(1.7rem, 3.2vw, 2.3rem); font-weight:800; color:#053B50; margin-bottom:6px;">
                Registrasi Calon Siswa Baru
            </h1>
            <p style="font-size:0.95rem; color:#475569; margin:0 auto 18px; font-weight:600;">
                Tahun Ajaran <?php echo htmlspecialchars($settings['tahun_ajaran'] ?? '2026/2027'); ?> — <?php echo htmlspecialchars($settings['gelombang'] ?? 'Gelombang 1'); ?>
            </p>

            <!-- 4 STEPS PROGRESS HINT -->
            <div style="display:inline-flex; align-items:center; gap:8px; background:#FFFFFF; border:1.5px solid #000; padding:6px 16px; border-radius:10px; box-shadow:2.5px 2.5px 0 #000; flex-wrap:wrap; justify-content:center;">
                <span style="font-size:0.82rem; font-weight:800; color:#0A4D68;"><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Formulir Bertahap (Multi-Step)</span>
                <span style="color:#CBD5E1;">•</span>
                <span style="font-size:0.82rem; font-weight:700; color:#64748B;">4 Langkah Mudah &amp; Cepat</span>
            </div>
        </div>
    </div>

    <main class="spmb-container">
        <div class="spmb-form-container">
            <?php if (!$isBuka): ?>
                <div class="spmb-alert spmb-alert-danger">
                    <i class="ph-bold ph-lock-key" style="font-size:1.4rem;"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($statusInfo['label']); ?>!</strong><br>
                        <?php if (!empty($statusInfo['note'])): ?>
                            <?php echo htmlspecialchars($statusInfo['note']); ?>.
                        <?php else: ?>
                            Mohon maaf, pendaftaran saat ini sedang ditutup atau periode gelombang telah berakhir. Silakan pantau pengumuman terbaru atau hubungi panitia.
                        <?php endif; ?>
                        <div style="margin-top:10px;">
                            <a href="index.php" class="spmb-btn spmb-btn-sm spmb-btn-secondary"><i class="ph-bold ph-arrow-left"></i> Kembali ke Beranda SPMB</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>

                <?php if (!empty($error)): ?>
                    <div class="spmb-alert spmb-alert-danger" id="formAlertBox">
                        <i class="ph-bold ph-warning-circle" style="font-size:1.4rem;"></i>
                        <div><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>

                <!-- INTERACTIVE 4-STEPS WIZARD STEPPER -->
                <div class="spmb-stepper-container">
                    <div class="spmb-stepper">
                        <div class="spmb-stepper-item active" id="step-nav-1" onclick="jumpToStep(1)">
                            <div class="spmb-stepper-circle">
                                <span class="step-num">1</span>
                                <i class="ph-bold ph-check step-check"></i>
                            </div>
                            <div class="spmb-stepper-text">
                                <span class="spmb-stepper-label">1. Peminatan &amp; Jalur</span>
                                <span class="spmb-stepper-sub">Jurusan &amp; Jalur Seleksi</span>
                            </div>
                        </div>
                        <div class="spmb-stepper-item" id="step-nav-2" onclick="jumpToStep(2)">
                            <div class="spmb-stepper-circle">
                                <span class="step-num">2</span>
                                <i class="ph-bold ph-check step-check"></i>
                            </div>
                            <div class="spmb-stepper-text">
                                <span class="spmb-stepper-label">2. Data Siswa</span>
                                <span class="spmb-stepper-sub">Biodata Calon Siswa</span>
                            </div>
                        </div>
                        <div class="spmb-stepper-item" id="step-nav-3" onclick="jumpToStep(3)">
                            <div class="spmb-stepper-circle">
                                <span class="step-num">3</span>
                                <i class="ph-bold ph-check step-check"></i>
                            </div>
                            <div class="spmb-stepper-text">
                                <span class="spmb-stepper-label">3. Orang Tua</span>
                                <span class="spmb-stepper-sub">Data Orang Tua / Wali</span>
                            </div>
                        </div>
                        <div class="spmb-stepper-item" id="step-nav-4" onclick="jumpToStep(4)">
                            <div class="spmb-stepper-circle">
                                <span class="step-num">4</span>
                                <i class="ph-bold ph-check step-check"></i>
                            </div>
                            <div class="spmb-stepper-text">
                                <span class="spmb-stepper-label">4. Upload Berkas</span>
                                <span class="spmb-stepper-sub">Dokumen &amp; Pas Foto</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="daftar.php" method="POST" enctype="multipart/form-data" id="spmbForm">

                    <!-- ======================================================
                         LANGKAH 1: PILIHAN JURUSAN & JALUR
                         ====================================================== -->
                    <div class="spmb-step-pane active" id="step-pane-1">
                        <div class="spmb-form-step-title">
                            <span class="spmb-step-badge"><i class="ph-bold ph-graduation-cap"></i> Bagian 1 dari 4</span>
                            <span>Pilihan Peminatan &amp; Jalur Seleksi</span>
                        </div>

                        <div class="spmb-form-row">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="jurusan_1">
                                    Jurusan Pilihan 1 <span class="required">*</span>
                                </label>
                                <select name="jurusan_1" id="jurusan_1" class="spmb-select" required>
                                    <option value="">-- Pilih Jurusan Utama --</option>
                                    <?php foreach ($DAFTAR_JURUSAN as $kode => $j): ?>
                                        <option value="<?php echo $kode; ?>" <?php echo ($selectedJurusan === $kode || (isset($_POST['jurusan_1']) && $_POST['jurusan_1'] === $kode)) ? 'selected' : ''; ?>>
                                            <?php echo $kode; ?> - <?php echo $j['nama']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="spmb-input-helper">Jurusan prioritas yang paling kamu minati.</div>
                            </div>

                            <div class="spmb-form-group">
                                <label class="spmb-label" for="jurusan_2">
                                    Jurusan Pilihan 2 (Alternatif Cadangan)
                                </label>
                                <select name="jurusan_2" id="jurusan_2" class="spmb-select">
                                    <option value="">-- Pilih Jurusan Cadangan (Opsional) --</option>
                                    <?php foreach ($DAFTAR_JURUSAN as $kode => $j): ?>
                                        <option value="<?php echo $kode; ?>" <?php echo (isset($_POST['jurusan_2']) && $_POST['jurusan_2'] === $kode) ? 'selected' : ''; ?>>
                                            <?php echo $kode; ?> - <?php echo $j['nama']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="spmb-input-helper">Dipertimbangkan bila kuota pilihan 1 telah penuh.</div>
                            </div>
                        </div>

                        <div class="spmb-form-group">
                            <label class="spmb-label">Jalur Pendaftaran <span class="required">*</span></label>
                            <div class="spmb-radio-group">
                                <?php 
                                $currJalur = $_POST['jalur'] ?? 'Reguler';
                                $jalurIcons = [
                                    'Reguler' => 'ph-student',
                                    'Prestasi' => 'ph-trophy',
                                    'Yatim/Piatu' => 'ph-heart'
                                ];
                                $jalurColors = [
                                    'Reguler' => '#0284C7',
                                    'Prestasi' => '#D97706',
                                    'Yatim/Piatu' => '#16A34A'
                                ];
                                foreach ($DAFTAR_JALUR as $k => $v): 
                                ?>
                                    <label class="spmb-radio-label">
                                        <input type="radio" name="jalur" value="<?php echo $k; ?>" <?php echo ($currJalur === $k) ? 'checked' : ''; ?> onchange="toggleJalurPrestasi(this.value)" required>
                                        <i class="ph-bold <?php echo $jalurIcons[$k] ?? 'ph-check'; ?>" style="color:<?php echo $jalurColors[$k] ?? '#000'; ?>; font-size:1.15rem;"></i>
                                        <span><?php echo $k; ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div style="margin-top:10px; font-size:0.83rem; color:#1E293B; background:#F8FAFC; border:1.5px solid #CBD5E1; padding:10px 14px; border-radius:8px; display:flex; align-items:flex-start; gap:10px;">
                                <i class="ph-bold ph-info" style="color:var(--nb-navy); font-size:1.2rem; margin-top:2px; flex-shrink:0;"></i>
                                <div>
                                    <strong style="display:block; margin-bottom:4px;">Biaya Pendaftaran:</strong>
                                    <div style="display:flex; flex-direction:column; gap:2px; font-weight:700; color:#334155;">
                                        <div>• Hanya 100.000 (Reguler)</div>
                                        <div>• 50.000 (Prestasi)</div>
                                        <div>• Gratis (Yatim/Piatu) + Mendapatkan Baju Putih Abu</div>
                                    </div>
                                </div>
                            </div>

                            <!-- WRAPPER UPLOAD SERTIFIKAT PRESTASI -->
                            <div id="wrapper_berkas_prestasi" style="display:<?php echo ($currJalur === 'Prestasi') ? 'block' : 'none'; ?>; margin-top:14px; background:#FFFDF0; border:1.5px solid #F59E0B; border-left:5px solid #D97706; border-radius:10px; padding:16px 18px; box-shadow:2px 2px 0px rgba(217, 119, 6, 0.12);">
                                <label class="spmb-label" for="berkas_prestasi" style="color:#92400E; display:flex; align-items:center; gap:8px; margin-bottom:6px; font-weight:800;">
                                    <i class="ph-bold ph-trophy" style="font-size:1.25rem; color:#D97706;"></i>
                                    <span>Unggah Foto Sertifikat / Piagam Prestasi <span class="required">*</span></span>
                                </label>
                                <p style="font-size:0.85rem; color:#78350F; margin:0 0 12px 0; line-height:1.45;">
                                    Wajib melampirkan foto sertifikat atau piagam kejuaraan/penghargaan (Juara Lomba Akademik / Olahraga / Seni / Tahfidz, dll) minimal tingkat Kecamatan/Kabupaten.
                                </p>
                                <div class="spmb-file-box" onclick="document.getElementById('berkas_prestasi').click();" style="background:#FFFFFF; border:2px dashed #D97706; cursor:pointer; padding:20px 14px; text-align:center; border-radius:8px; transition:all 0.2s ease;">
                                    <i class="ph-bold ph-upload-simple" style="font-size:2rem; color:#D97706;"></i>
                                    <p style="font-weight:700; margin:8px 0 4px; color:#1E293B;">Klik untuk memilih file sertifikat / piagam</p>
                                    <span style="font-size:0.82rem; color:#64748B;">Format foto JPG, PNG, WEBP atau dokumen PDF (Maksimal 3 MB)</span>
                                    <input type="file" name="berkas_prestasi" id="berkas_prestasi" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none;" onchange="previewDoc(this, 'prestasiPreview', 'previewImgPrestasi', 'previewIconPrestasi', 'fileNameDisplayPrestasi')">
                                </div>
                                <div id="prestasiPreview" style="margin-top:12px; display:none; align-items:center; gap:12px; background:#FFFFFF; padding:10px 14px; border:1px solid #FCD34D; border-radius:8px;">
                                    <img id="previewImgPrestasi" src="" alt="Pratinjau Sertifikat" style="width:55px; height:55px; object-fit:cover; border:1.5px solid #D97706; border-radius:6px; display:none;">
                                    <div id="previewIconPrestasi" style="width:48px; height:48px; border-radius:6px; background:#FEF3C7; display:none; align-items:center; justify-content:center; border:1.5px solid #F59E0B;">
                                        <i class="ph-bold ph-file-pdf" style="font-size:1.8rem; color:#DC2626;"></i>
                                    </div>
                                    <div style="flex:1;">
                                        <div id="fileNameDisplayPrestasi" style="font-weight:700; font-size:0.88rem; color:#1E293B; word-break:break-all;"></div>
                                        <div style="font-size:0.8rem; color:#16A34A; font-weight:600; display:flex; align-items:center; gap:4px; margin-top:2px;">
                                            <i class="ph-bold ph-check-circle"></i> File siap diunggah
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- NAVIGATION BUTTONS LANGKAH 1 -->
                        <div class="spmb-nav-buttons">
                            <a href="index.php" class="spmb-btn spmb-btn-secondary">
                                <i class="ph-bold ph-arrow-left"></i> Batal &amp; Kembali ke Beranda
                            </a>
                            <button type="button" class="spmb-btn spmb-btn-primary" onclick="goToStep(2)" style="padding:14px 28px;">
                                <span>Lanjut: Data Diri Siswa</span>
                                <i class="ph-bold ph-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ======================================================
                         LANGKAH 2: DATA DIRI CALON SISWA
                         ====================================================== -->
                    <div class="spmb-step-pane" id="step-pane-2">
                        <div class="spmb-form-step-title">
                            <span class="spmb-step-badge"><i class="ph-bold ph-user"></i> Bagian 2 dari 4</span>
                            <span>Data Diri Calon Siswa</span>
                        </div>

                        <div class="spmb-form-row">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="nisn">
                                    NISN (Nomor Induk Siswa Nasional) <span class="required">*</span>
                                </label>
                                <input type="text" name="nisn" id="nisn" class="spmb-input" placeholder="Contoh: 0071234567" maxlength="10" value="<?php echo htmlspecialchars($_POST['nisn'] ?? ''); ?>" required>
                                <div class="spmb-input-helper">10 digit angka resmi dari SMP/MTs.</div>
                            </div>

                            <div class="spmb-form-group">
                                <label class="spmb-label" for="nik">
                                    NIK (Nomor Induk Kependudukan) <span class="required">*</span>
                                </label>
                                <input type="text" name="nik" id="nik" class="spmb-input" placeholder="16 digit sesuai KK / KTP" maxlength="16" value="<?php echo htmlspecialchars($_POST['nik'] ?? ''); ?>" required>
                                <div class="spmb-input-helper">16 digit angka yang tertera di Kartu Keluarga.</div>
                            </div>
                        </div>

                        <div class="spmb-form-group">
                            <label class="spmb-label" for="nama_lengkap">
                                Nama Lengkap Calon Siswa <span class="required">*</span>
                            </label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" class="spmb-input" placeholder="Sesuai dengan ijazah SMP/MTs atau Akta Kelahiran" value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>" required>
                        </div>

                        <div class="spmb-form-row">
                            <div class="spmb-form-group">
                                <label class="spmb-label">Jenis Kelamin <span class="required">*</span></label>
                                <div class="spmb-gender-group">
                                    <label class="spmb-gender-card">
                                        <input type="radio" name="jenis_kelamin" value="L" <?php echo (($_POST['jenis_kelamin'] ?? '') === 'L') ? 'checked' : ''; ?> required>
                                        <i class="ph-bold ph-gender-male" style="color:#0284C7; font-size:1.2rem;"></i>
                                        <span>Laki-laki</span>
                                    </label>
                                    <label class="spmb-gender-card">
                                        <input type="radio" name="jenis_kelamin" value="P" <?php echo (($_POST['jenis_kelamin'] ?? '') === 'P') ? 'checked' : ''; ?> required>
                                        <i class="ph-bold ph-gender-female" style="color:#E11D48; font-size:1.2rem;"></i>
                                        <span>Perempuan</span>
                                    </label>
                                </div>
                            </div>

                            <div class="spmb-form-group">
                                <label class="spmb-label" for="no_hp">
                                    No. WhatsApp Calon Siswa <span class="required">*</span>
                                </label>
                                <input type="tel" name="no_hp" id="no_hp" class="spmb-input" placeholder="Contoh: 081234567890" value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>" required>
                                <div class="spmb-input-helper">Untuk menerima notifikasi status dan info seleksi.</div>
                            </div>
                        </div>

                        <div class="spmb-form-row">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="tempat_lahir">
                                    Tempat Lahir <span class="required">*</span>
                                </label>
                                <input type="text" name="tempat_lahir" id="tempat_lahir" class="spmb-input" placeholder="Kota / Kabupaten Kelahiran" value="<?php echo htmlspecialchars($_POST['tempat_lahir'] ?? ''); ?>" required>
                            </div>

                            <div class="spmb-form-group">
                                <label class="spmb-label" for="tanggal_lahir">
                                    Tanggal Lahir <span class="required">*</span>
                                </label>
                                <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="spmb-input" value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="spmb-form-group">
                            <label class="spmb-label" for="asal_sekolah">
                                Asal Sekolah (SMP / MTs) <span class="required">*</span>
                            </label>
                            <input type="text" name="asal_sekolah" id="asal_sekolah" class="spmb-input" placeholder="Contoh: SMPN 1 Singaparna / MTs Sukapura" value="<?php echo htmlspecialchars($_POST['asal_sekolah'] ?? ''); ?>" required>
                        </div>

                        <div class="spmb-form-group">
                            <label class="spmb-label" for="alamat">
                                Alamat Tempat Tinggal Lengkap <span class="required">*</span>
                            </label>
                            <textarea name="alamat" id="alamat" class="spmb-textarea" placeholder="Tuliskan nama jalan, RT/RW, Dusun, Desa/Kelurahan, Kecamatan, dan Kabupaten/Kota..." required><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                        </div>

                        <!-- NAVIGATION BUTTONS LANGKAH 2 -->
                        <div class="spmb-nav-buttons">
                            <button type="button" class="spmb-btn spmb-btn-secondary" onclick="goToStep(1)">
                                <i class="ph-bold ph-arrow-left"></i> Kembali ke Pilihan Jurusan
                            </button>
                            <button type="button" class="spmb-btn spmb-btn-primary" onclick="goToStep(3)" style="padding:14px 28px;">
                                <span>Lanjut: Data Orang Tua</span>
                                <i class="ph-bold ph-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ======================================================
                         LANGKAH 3: DATA ORANG TUA / WALI
                         ====================================================== -->
                    <div class="spmb-step-pane" id="step-pane-3">
                        <div class="spmb-form-step-title">
                            <span class="spmb-step-badge"><i class="ph-bold ph-users"></i> Bagian 3 dari 4</span>
                            <span>Data Orang Tua / Wali</span>
                        </div>

                        <div class="spmb-form-row">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="nama_ayah">
                                    Nama Ayah Kandung / Wali <span class="required">*</span>
                                </label>
                                <input type="text" name="nama_ayah" id="nama_ayah" class="spmb-input" placeholder="Nama lengkap Ayah/Wali" value="<?php echo htmlspecialchars($_POST['nama_ayah'] ?? ''); ?>" required>
                            </div>

                            <div class="spmb-form-group">
                                <label class="spmb-label" for="nama_ibu">
                                    Nama Ibu Kandung <span class="required">*</span>
                                </label>
                                <input type="text" name="nama_ibu" id="nama_ibu" class="spmb-input" placeholder="Nama lengkap Ibu" value="<?php echo htmlspecialchars($_POST['nama_ibu'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="spmb-form-row-3">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="pekerjaan_ortu">Pekerjaan Orang Tua</label>
                                <?php
                                $jobs = ['PNS / TNI / POLRI', 'Karyawan Swasta', 'Wiraswasta / Pedagang', 'Petani / Peternak', 'Buruh Harian Lepas', 'Guru / Dosen', 'Ibu Rumah Tangga', 'Lainnya'];
                                $selJob = $_POST['pekerjaan_ortu'] ?? '';
                                $lainnyaVal = $_POST['pekerjaan_ortu_lainnya'] ?? '';
                                $isLainnya = ($selJob === 'Lainnya') || (!empty($selJob) && !in_array($selJob, $jobs)) || !empty($lainnyaVal);
                                if ($isLainnya && empty($lainnyaVal) && !in_array($selJob, $jobs)) {
                                    $lainnyaVal = $selJob;
                                    $selJob = 'Lainnya';
                                }
                                ?>
                                <select name="pekerjaan_ortu" id="pekerjaan_ortu" class="spmb-select" onchange="togglePekerjaanLainnya(this.value)">
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    <?php foreach ($jobs as $j): ?>
                                        <option value="<?php echo $j; ?>" <?php echo (($selJob === $j) || ($j === 'Lainnya' && $isLainnya)) ? 'selected' : ''; ?>><?php echo $j; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="wrapper_pekerjaan_lainnya" style="margin-top:8px; display:<?php echo $isLainnya ? 'block' : 'none'; ?>;">
                                    <input type="text" name="pekerjaan_ortu_lainnya" id="pekerjaan_ortu_lainnya" class="spmb-input" placeholder="Ketik jenis pekerjaan..." value="<?php echo htmlspecialchars($lainnyaVal); ?>" style="font-size:0.88rem; padding:9px 12px;">
                                    <div class="spmb-input-helper">Tuliskan profesi secara spesifik.</div>
                                </div>
                            </div>

                            <div class="spmb-form-group">
                                <label class="spmb-label" for="no_hp_ortu">
                                    No. WhatsApp Orang Tua <span class="required">*</span>
                                </label>
                                <input type="tel" name="no_hp_ortu" id="no_hp_ortu" class="spmb-input" placeholder="08xxxxxxxxxx" value="<?php echo htmlspecialchars($_POST['no_hp_ortu'] ?? ''); ?>" required>
                            </div>

                            <div class="spmb-form-group">
                                <label class="spmb-label" for="penghasilan_ortu">Rata-rata Penghasilan</label>
                                <select name="penghasilan_ortu" id="penghasilan_ortu" class="spmb-select">
                                    <option value="">-- Pilih Range --</option>
                                    <option value="< Rp 1.000.000">&lt; Rp 1.000.000</option>
                                    <option value="Rp 1.000.000 - Rp 2.500.000">Rp 1.000.000 - Rp 2.500.000</option>
                                    <option value="Rp 2.500.000 - Rp 5.000.000">Rp 2.500.000 - Rp 5.000.000</option>
                                    <option value="> Rp 5.000.000">&gt; Rp 5.000.000</option>
                                </select>
                            </div>
                        </div>

                        <!-- NAVIGATION BUTTONS LANGKAH 3 -->
                        <div class="spmb-nav-buttons">
                            <button type="button" class="spmb-btn spmb-btn-secondary" onclick="goToStep(2)">
                                <i class="ph-bold ph-arrow-left"></i> Kembali ke Data Siswa
                            </button>
                            <button type="button" class="spmb-btn spmb-btn-primary" onclick="goToStep(4)" style="padding:14px 28px;">
                                <span>Lanjut: Upload Berkas</span>
                                <i class="ph-bold ph-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ======================================================
                         LANGKAH 4: UPLOAD BERKAS & PAS FOTO
                         ====================================================== -->
                    <div class="spmb-step-pane" id="step-pane-4">
                        <div class="spmb-form-step-title">
                            <span class="spmb-step-badge"><i class="ph-bold ph-files"></i> Bagian 4 dari 4</span>
                            <span>Upload Dokumen Persyaratan &amp; Pas Foto</span>
                        </div>

                        <div style="background:#FFFDF0; border:1.5px solid #CBD5E1; border-left:4px solid #0A4D68; border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:0.88rem; color:#334155; line-height:1.5;">
                            <strong style="color:#0A4D68;"><i class="ph-bold ph-info"></i> Petunjuk Unggah Berkas:</strong> Unggah foto/scan dokumen yang jelas dan dapat dibaca. Format berkas yang didukung adalah <strong>JPG, PNG, WEBP, atau PDF</strong> dengan ukuran maksimal <strong>3 MB</strong> per dokumen (Pas foto maks 2 MB). Berkas fisik persyaratan tetap dibawa saat proses <strong>Daftar Ulang</strong>.
                        </div>

                        <div class="spmb-upload-grid">
                            
                            <!-- 1. PAS FOTO CALON SISWA -->
                            <div class="spmb-upload-card">
                                <div>
                                    <div class="spmb-upload-card-header">
                                        <div class="spmb-upload-card-title">
                                            <i class="ph-bold ph-camera" style="color:var(--nb-navy); font-size:1.25rem;"></i>
                                            <span>Pas Foto Siswa</span>
                                        </div>
                                        <span class="spmb-upload-badge-opt">Dianjurkan</span>
                                    </div>
                                    <p class="spmb-upload-card-desc">Pas foto 3x4 berwarna resmi latar merah atau biru (JPG/PNG/WEBP, Maks 2MB).</p>
                                </div>
                                <div>
                                    <div class="spmb-upload-dropzone" onclick="document.getElementById('foto').click();">
                                        <i class="ph-bold ph-upload-simple" style="font-size:1.6rem; color:var(--nb-navy);"></i>
                                        <div style="font-weight:700; font-size:0.85rem; margin-top:4px;">Klik untuk memilih foto</div>
                                        <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewDoc(this, 'previewBoxFoto', 'previewImgFoto', null, 'fileNameFoto')">
                                    </div>
                                    <div id="previewBoxFoto" class="spmb-upload-preview">
                                        <img id="previewImgFoto" src="" alt="Foto Siswa">
                                        <div style="flex:1; min-width:0;">
                                            <div id="fileNameFoto" style="font-weight:700; font-size:0.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                            <div style="font-size:0.75rem; color:#16A34A; font-weight:700;"><i class="ph-bold ph-check"></i> Siap diunggah</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. KARTU KELUARGA (KK) -->
                            <div class="spmb-upload-card">
                                <div>
                                    <div class="spmb-upload-card-header">
                                        <div class="spmb-upload-card-title">
                                            <i class="ph-bold ph-identification-card" style="color:#0284C7; font-size:1.25rem;"></i>
                                            <span>Kartu Keluarga (KK)</span>
                                        </div>
                                        <span class="spmb-upload-badge-req">Wajib</span>
                                    </div>
                                    <p class="spmb-upload-card-desc">Scan / foto Kartu Keluarga terbaru yang memuat nama calon siswa (JPG/PNG/PDF, Maks 3MB).</p>
                                </div>
                                <div>
                                    <div class="spmb-upload-dropzone" onclick="document.getElementById('berkas_kk').click();">
                                        <i class="ph-bold ph-upload-simple" style="font-size:1.6rem; color:#0284C7;"></i>
                                        <div style="font-weight:700; font-size:0.85rem; margin-top:4px;">Pilih Berkas KK</div>
                                        <input type="file" name="berkas_kk" id="berkas_kk" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none;" required onchange="previewDoc(this, 'previewBoxKK', 'previewImgKK', 'previewIconKK', 'fileNameKK')">
                                    </div>
                                    <div id="previewBoxKK" class="spmb-upload-preview">
                                        <img id="previewImgKK" src="" alt="KK" style="display:none;">
                                        <div id="previewIconKK" class="pdf-icon" style="display:none;"><i class="ph-bold ph-file-pdf"></i></div>
                                        <div style="flex:1; min-width:0;">
                                            <div id="fileNameKK" style="font-weight:700; font-size:0.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                            <div style="font-size:0.75rem; color:#16A34A; font-weight:700;"><i class="ph-bold ph-check"></i> Berkas KK terpilih</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. AKTA KELAHIRAN -->
                            <div class="spmb-upload-card">
                                <div>
                                    <div class="spmb-upload-card-header">
                                        <div class="spmb-upload-card-title">
                                            <i class="ph-bold ph-scroll" style="color:#059669; font-size:1.25rem;"></i>
                                            <span>Akta Kelahiran</span>
                                        </div>
                                        <span class="spmb-upload-badge-req">Wajib</span>
                                    </div>
                                    <p class="spmb-upload-card-desc">Scan / foto Akta Kelahiran resmi dari Disdukcapil (JPG/PNG/PDF, Maks 3MB).</p>
                                </div>
                                <div>
                                    <div class="spmb-upload-dropzone" onclick="document.getElementById('berkas_akta').click();">
                                        <i class="ph-bold ph-upload-simple" style="font-size:1.6rem; color:#059669;"></i>
                                        <div style="font-weight:700; font-size:0.85rem; margin-top:4px;">Pilih Berkas Akta</div>
                                        <input type="file" name="berkas_akta" id="berkas_akta" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none;" required onchange="previewDoc(this, 'previewBoxAkta', 'previewImgAkta', 'previewIconAkta', 'fileNameAkta')">
                                    </div>
                                    <div id="previewBoxAkta" class="spmb-upload-preview">
                                        <img id="previewImgAkta" src="" alt="Akta" style="display:none;">
                                        <div id="previewIconAkta" class="pdf-icon" style="display:none;"><i class="ph-bold ph-file-pdf"></i></div>
                                        <div style="flex:1; min-width:0;">
                                            <div id="fileNameAkta" style="font-weight:700; font-size:0.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                            <div style="font-size:0.75rem; color:#16A34A; font-weight:700;"><i class="ph-bold ph-check"></i> Berkas Akta terpilih</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. IJAZAH / SURAT KETERANGAN LULUS -->
                            <div class="spmb-upload-card">
                                <div>
                                    <div class="spmb-upload-card-header">
                                        <div class="spmb-upload-card-title">
                                            <i class="ph-bold ph-certificate" style="color:#D97706; font-size:1.25rem;"></i>
                                            <span>Ijazah / SKL SMP</span>
                                        </div>
                                        <span class="spmb-upload-badge-req">Wajib</span>
                                    </div>
                                    <p class="spmb-upload-card-desc">Scan / foto Ijazah atau Surat Keterangan Lulus dari SMP/MTs (JPG/PNG/PDF, Maks 3MB).</p>
                                </div>
                                <div>
                                    <div class="spmb-upload-dropzone" onclick="document.getElementById('berkas_ijazah').click();">
                                        <i class="ph-bold ph-upload-simple" style="font-size:1.6rem; color:#D97706;"></i>
                                        <div style="font-weight:700; font-size:0.85rem; margin-top:4px;">Pilih Ijazah / SKL</div>
                                        <input type="file" name="berkas_ijazah" id="berkas_ijazah" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none;" required onchange="previewDoc(this, 'previewBoxIjazah', 'previewImgIjazah', 'previewIconIjazah', 'fileNameIjazah')">
                                    </div>
                                    <div id="previewBoxIjazah" class="spmb-upload-preview">
                                        <img id="previewImgIjazah" src="" alt="Ijazah/SKL" style="display:none;">
                                        <div id="previewIconIjazah" class="pdf-icon" style="display:none;"><i class="ph-bold ph-file-pdf"></i></div>
                                        <div style="flex:1; min-width:0;">
                                            <div id="fileNameIjazah" style="font-weight:700; font-size:0.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                            <div style="font-size:0.75rem; color:#16A34A; font-weight:700;"><i class="ph-bold ph-check"></i> Berkas Ijazah terpilih</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. KTP ORANG TUA / WALI -->
                            <div class="spmb-upload-card">
                                <div>
                                    <div class="spmb-upload-card-header">
                                        <div class="spmb-upload-card-title">
                                            <i class="ph-bold ph-users-three" style="color:#7C3AED; font-size:1.25rem;"></i>
                                            <span>KTP Orang Tua / Wali</span>
                                        </div>
                                        <span class="spmb-upload-badge-req">Wajib</span>
                                    </div>
                                    <p class="spmb-upload-card-desc">Scan / foto KTP Ayah, Ibu, atau Wali yang masih berlaku (JPG/PNG/PDF, Maks 3MB).</p>
                                </div>
                                <div>
                                    <div class="spmb-upload-dropzone" onclick="document.getElementById('berkas_ktp_ortu').click();">
                                        <i class="ph-bold ph-upload-simple" style="font-size:1.6rem; color:#7C3AED;"></i>
                                        <div style="font-weight:700; font-size:0.85rem; margin-top:4px;">Pilih Berkas KTP</div>
                                        <input type="file" name="berkas_ktp_ortu" id="berkas_ktp_ortu" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none;" required onchange="previewDoc(this, 'previewBoxKtp', 'previewImgKtp', 'previewIconKtp', 'fileNameKtp')">
                                    </div>
                                    <div id="previewBoxKtp" class="spmb-upload-preview">
                                        <img id="previewImgKtp" src="" alt="KTP" style="display:none;">
                                        <div id="previewIconKtp" class="pdf-icon" style="display:none;"><i class="ph-bold ph-file-pdf"></i></div>
                                        <div style="flex:1; min-width:0;">
                                            <div id="fileNameKtp" style="font-weight:700; font-size:0.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                            <div style="font-size:0.75rem; color:#16A34A; font-weight:700;"><i class="ph-bold ph-check"></i> Berkas KTP terpilih</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. KARTU INDONESIA PINTAR (KIP / PIP) -->
                            <div class="spmb-upload-card">
                                <div>
                                    <div class="spmb-upload-card-header">
                                        <div class="spmb-upload-card-title">
                                            <i class="ph-bold ph-credit-card" style="color:#EA580C; font-size:1.25rem;"></i>
                                            <span>Kartu Indonesia Pintar (KIP)</span>
                                        </div>
                                        <span class="spmb-upload-badge-opt">Opsional</span>
                                    </div>
                                    <p class="spmb-upload-card-desc">Bagi pemegang kartu KIP, PIP, PKH, atau KKS bantuan pemerintah (JPG/PNG/PDF, Maks 3MB).</p>
                                </div>
                                <div>
                                    <div class="spmb-upload-dropzone" onclick="document.getElementById('berkas_kip').click();">
                                        <i class="ph-bold ph-upload-simple" style="font-size:1.6rem; color:#EA580C;"></i>
                                        <div style="font-weight:700; font-size:0.85rem; margin-top:4px;">Pilih Kartu KIP (Jika Ada)</div>
                                        <input type="file" name="berkas_kip" id="berkas_kip" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none;" onchange="previewDoc(this, 'previewBoxKip', 'previewImgKip', 'previewIconKip', 'fileNameKip')">
                                    </div>
                                    <div id="previewBoxKip" class="spmb-upload-preview">
                                        <img id="previewImgKip" src="" alt="KIP" style="display:none;">
                                        <div id="previewIconKip" class="pdf-icon" style="display:none;"><i class="ph-bold ph-file-pdf"></i></div>
                                        <div style="flex:1; min-width:0;">
                                            <div id="fileNameKip" style="font-weight:700; font-size:0.82rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                            <div style="font-size:0.75rem; color:#16A34A; font-weight:700;"><i class="ph-bold ph-check"></i> Berkas KIP terpilih</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- PERNYATAAN KEBENARAN DATA -->
                        <div style="background:#FFFDF0; border:1.5px solid #CBD5E1; border-left:4px solid #0A4D68; border-radius:10px; padding:18px 20px; margin:28px 0; box-shadow:2px 2px 0px rgba(0,0,0,0.05);">
                            <label style="display:flex; align-items:flex-start; gap:12px; cursor:pointer; font-weight:600; font-size:0.92rem; color:#1E293B;">
                                <input type="checkbox" required style="width:19px; height:19px; margin-top:2px; accent-color:var(--nb-navy); cursor:pointer;">
                                <span>Saya menyatakan dengan sesungguhnya bahwa seluruh data dan dokumen yang saya isikan serta lampirkan dalam formulir ini adalah benar, sah, dan dapat dipertanggungjawabkan secara hukum.</span>
                            </label>
                        </div>

                        <!-- NAVIGATION BUTTONS LANGKAH 4 (SUBMIT) -->
                        <div class="spmb-nav-buttons">
                            <button type="button" class="spmb-btn spmb-btn-secondary" onclick="goToStep(3)">
                                <i class="ph-bold ph-arrow-left"></i> Kembali ke Data Orang Tua
                            </button>
                            <button type="submit" class="spmb-btn spmb-btn-primary" style="padding:16px 36px; font-size:1.05rem;">
                                <i class="ph-bold ph-paper-plane-tilt"></i> Kirim Pendaftaran Sekarang
                            </button>
                        </div>
                    </div>

                </form>
            <?php endif; ?>
        </div>
    </main>

    <script>
    let currentStep = 1;
    const totalSteps = 4;

    function updateStepperUI() {
        for (let i = 1; i <= totalSteps; i++) {
            const navItem = document.getElementById('step-nav-' + i);
            if (!navItem) continue;
            navItem.classList.remove('active', 'completed');
            if (i < currentStep) {
                navItem.classList.add('completed');
            } else if (i === currentStep) {
                navItem.classList.add('active');
            }
        }
    }

    function validateCurrentStep() {
        const pane = document.getElementById('step-pane-' + currentStep);
        if (!pane) return true;
        const requiredInputs = pane.querySelectorAll('input[required], select[required], textarea[required]');
        for (let input of requiredInputs) {
            // Abaikan input yang tersembunyi
            if (input.offsetParent === null) continue;
            if (!input.checkValidity()) {
                input.reportValidity();
                input.focus();
                return false;
            }
        }
        return true;
    }

    function goToStep(step) {
        if (step > currentStep) {
            if (!validateCurrentStep()) {
                return;
            }
        }
        if (step < 1 || step > totalSteps) return;

        document.querySelectorAll('.spmb-step-pane').forEach(el => el.classList.remove('active'));
        const targetPane = document.getElementById('step-pane-' + step);
        if (targetPane) {
            targetPane.classList.add('active');
        }
        currentStep = step;
        updateStepperUI();

        // Scroll halus ke kepala form
        const formBox = document.querySelector('.spmb-form-container');
        if (formBox) {
            formBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function jumpToStep(step) {
        if (step < currentStep) {
            goToStep(step);
        } else if (step === currentStep + 1) {
            goToStep(step);
        }
    }

    function previewDoc(input, previewBoxId, previewImgId, previewIconId, previewNameId) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const previewBox = document.getElementById(previewBoxId);
            const imgEl = previewImgId ? document.getElementById(previewImgId) : null;
            const iconEl = previewIconId ? document.getElementById(previewIconId) : null;
            const nameEl = document.getElementById(previewNameId);

            if (nameEl) {
                nameEl.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
            }
            if (previewBox) {
                previewBox.style.display = 'flex';
            }

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imgEl) {
                        imgEl.src = e.target.result;
                        imgEl.style.display = 'block';
                    }
                    if (iconEl) {
                        iconEl.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                if (imgEl) {
                    imgEl.style.display = 'none';
                }
                if (iconEl) {
                    iconEl.style.display = 'flex';
                }
            }
        }
    }

    function toggleJalurPrestasi(val) {
        const wrapper = document.getElementById('wrapper_berkas_prestasi');
        const input = document.getElementById('berkas_prestasi');
        if (!wrapper || !input) return;
        if (val === 'Prestasi') {
            wrapper.style.display = 'block';
            input.setAttribute('required', 'required');
        } else {
            wrapper.style.display = 'none';
            input.removeAttribute('required');
        }
    }

    function togglePekerjaanLainnya(val) {
        const wrapper = document.getElementById('wrapper_pekerjaan_lainnya');
        const input = document.getElementById('pekerjaan_ortu_lainnya');
        if (val === 'Lainnya') {
            wrapper.style.display = 'block';
            setTimeout(function() {
                input.focus();
            }, 100);
            input.setAttribute('required', 'required');
        } else {
            wrapper.style.display = 'none';
            input.removeAttribute('required');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkedJalur = document.querySelector('input[name="jalur"]:checked');
        if (checkedJalur) {
            toggleJalurPrestasi(checkedJalur.value);
        }

        <?php if (!empty($error)): ?>
        // Jika ada error backend (misal salah upload berkas), arahkan ke langkah 4
        goToStep(4);
        <?php endif; ?>
    });
    </script>

    <?php include '../components/footer.php'; ?>
</body>
</html>
