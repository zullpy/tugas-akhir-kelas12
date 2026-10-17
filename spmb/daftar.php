<?php
// spmb/daftar.php
// Formulir Pendaftaran Siswa Baru (SPMB) SMKS SUKAPURA
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'daftar';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fonnte.php';

// Calon siswa wajib login akun SPMB terlebih dahulu sebelum mengisi formulir
check_spmb_login(true);
$currentUser = get_spmb_user();

$pdo = get_db_connection();
$settings = get_all_settings();

// Cek apakah pendaftaran sedang dibuka (otomatis sesuai tanggal atau manual)
$isBuka = is_spmb_open($settings);
$statusInfo = get_spmb_status_info($settings);

// Hitung batas usia PPDB (Maks 21 tahun per 1 Juli tahun berjalan, Min 13 tahun)
$refYear = (int)date('Y'); // Tahun berjalan (2026 -> rentang kelahiran 2005 s.d. 2013)
$minBirthDate = ($refYear - 21) . '-07-01'; // Maksimal 21 tahun per 1 Juli (2005-07-01)
$maxBirthDate = ($refYear - 13) . '-12-31'; // Minimal 13 tahun (2013-12-31)

$error = '';
$success = '';

// Data default otomatis dari akun calon siswa yang sedang login (tetap bisa diedit oleh pengguna)
$valNamaLengkap  = $_POST['nama_lengkap'] ?? ($currentUser['nama_lengkap'] ?? '');
$valNoHp         = $_POST['no_hp'] ?? ($currentUser['no_hp'] ?? '');
$valTanggalLahir = $_POST['tanggal_lahir'] ?? ($currentUser['tanggal_lahir'] ?? '');

// Pre-fill pilihan jurusan jika ada query string
$selectedJurusan = sanitize_input($_GET['jurusan'] ?? '');

// Cek apakah akun ini sudah pernah memiliki pendaftaran
$existingForm = null;
if (!empty($currentUser['id'])) {
    $stmtExist = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `id_akun` = ? ORDER BY `id` DESC LIMIT 1");
    $stmtExist->execute([$currentUser['id']]);
    $existingForm = $stmtExist->fetch();
}

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
        } elseif (!is_valid_indonesian_phone($no_hp)) {
            $error = "Nomor WhatsApp Calon Siswa tidak valid. Harap gunakan nomor seluler Indonesia yang aktif (contoh: 081234567890, 10-14 digit).";
        } elseif (!is_valid_indonesian_phone($no_hp_ortu)) {
            $error = "Nomor WhatsApp Orang Tua tidak valid. Harap gunakan nomor seluler Indonesia yang aktif (contoh: 081234567890, 10-14 digit).";
        } elseif ($jurusan_1 === $jurusan_2) {
            $error = "Jurusan Pilihan 1 dan Pilihan 2 tidak boleh sama.";
        } elseif (!empty($tanggal_lahir) && ($tanggal_lahir < $minBirthDate || $tanggal_lahir > $maxBirthDate)) {
            if ($tanggal_lahir < $minBirthDate) {
                $error = "Usia calon siswa melebihi 21 tahun. Berdasarkan ketentuan Permendikbud PPDB SMK, batas usia maksimal adalah 21 tahun per 1 Juli $refYear.";
            } else {
                $error = "Usia calon siswa kurang dari 13 tahun. Berdasarkan ketentuan Permendikbud PPDB SMK, batas usia minimal adalah 13 tahun.";
            }
        } else {
            // Cek apakah NISN sudah pernah mendaftar
            $checkNisn = $pdo->prepare("SELECT `no_pendaftaran`, `nama_lengkap` FROM `spmb_pendaftar` WHERE `nisn` = ?");
            $checkNisn->execute([$nisn]);
            $existing = $checkNisn->fetch();

            if ($existing) {
                $error = "NISN <strong>$nisn</strong> sudah pernah didaftarkan atas nama <strong>{$existing['nama_lengkap']}</strong> dengan Nomor: <strong>{$existing['no_pendaftaran']}</strong>. Silakan gunakan menu <a href='cek-status.php?nisn=$nisn' style='color:#0A4D68; text-decoration:underline;'>Cek Status Pendaftaran</a>.";
            } else {
                // Normalisasi no_hp & no_hp_ortu sebelum disimpan
                $no_hp = normalize_phone_number($no_hp);
                $no_hp_ortu = normalize_phone_number($no_hp_ortu);

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
                // Upload Berkas KTP Orang Tua / Wali (Mendukung hingga 2 foto)
                $uploadedKtp = [];
                // 1. Cek jika dikirim via array multiple
                if (isset($_FILES['berkas_ktp_ortu']['name']) && is_array($_FILES['berkas_ktp_ortu']['name'])) {
                    $cnt = count($_FILES['berkas_ktp_ortu']['name']);
                    for ($i = 0; $i < min($cnt, 2); $i++) {
                        if ($_FILES['berkas_ktp_ortu']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                        if ($_FILES['berkas_ktp_ortu']['error'][$i] !== UPLOAD_ERR_OK) {
                            $error = "Terjadi masalah saat mengunggah salah satu berkas KTP.";
                            break;
                        }
                        $fileTmp  = $_FILES['berkas_ktp_ortu']['tmp_name'][$i];
                        $fileName = $_FILES['berkas_ktp_ortu']['name'][$i];
                        $fileSize = $_FILES['berkas_ktp_ortu']['size'][$i];
                        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                            $error = "Format berkas KTP harus JPG, PNG, WEBP, atau PDF.";
                            break;
                        }
                        if ($fileSize > 3145728) {
                            $error = "Ukuran file KTP melebihi batas maksimal 3 MB.";
                            break;
                        }
                        $newFileName = 'ktp_' . ($i + 1) . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                        if (move_uploaded_file($fileTmp, __DIR__ . '/uploads/' . $newFileName)) {
                            $uploadedKtp[] = $newFileName;
                        }
                    }
                } else {
                    $ktp1 = empty($error) ? $uploadFile('berkas_ktp_ortu', 'ktp1', false) : null;
                    if ($ktp1) $uploadedKtp[] = $ktp1;
                }

                // 2. Cek juga slot kedua berkas_ktp_ortu_2 jika diisi
                if (empty($error) && count($uploadedKtp) < 2) {
                    $ktp2 = $uploadFile('berkas_ktp_ortu_2', 'ktp2', false);
                    if ($ktp2) $uploadedKtp[] = $ktp2;
                }

                if (empty($error) && empty($uploadedKtp)) {
                    $error = "Wajib mengunggah minimal 1 berkas <strong>KTP Orang Tua / Wali</strong>.";
                }
                $berkas_ktp_ortu_path = !empty($uploadedKtp) ? implode(',', $uploadedKtp) : null;
                $berkas_kip_path      = empty($error) ? $uploadFile('berkas_kip', 'kip', false) : null;
                
                $berkas_prestasi_path = null;
                if (empty($error) && $jalur === 'Prestasi') {
                    $berkas_prestasi_path = $uploadFile('berkas_prestasi', 'prestasi', true);
                }

                if (empty($error)) {
                    // Generate No Registrasi
                    $no_pendaftaran = generate_no_pendaftaran($pdo);
                    $id_akun = $currentUser['id'] ?? null;

                    $insertStmt = $pdo->prepare("INSERT INTO `spmb_pendaftar` (
                        `id_akun`, `no_pendaftaran`, `nisn`, `nik`, `nama_lengkap`, `jenis_kelamin`,
                        `tempat_lahir`, `tanggal_lahir`, `agama`, `no_hp`, `alamat`,
                        `asal_sekolah`, `jurusan_1`, `jurusan_2`, `jalur`, `nama_ayah`,
                        `nama_ibu`, `pekerjaan_ortu`, `no_hp_ortu`, `penghasilan_ortu`,
                        `foto`, `berkas_kk`, `berkas_akta`, `berkas_ijazah`, `berkas_ktp_ortu`, `berkas_kip`, `berkas_prestasi`,
                        `status`, `catatan_admin`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Verifikasi', 'Pendaftaran berhasil dikirim. Menunggu verifikasi berkas oleh panitia.')");

                    $insertStmt->execute([
                        $id_akun, $no_pendaftaran, $nisn, $nik, $nama_lengkap, $jenis_kelamin,
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
    <link rel="stylesheet" href="spmb.css?v=<?php echo @filemtime(__DIR__ . '/spmb.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include __DIR__ . '/header_minimal.php'; ?>

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

                <?php if ($existingForm): ?>
                    <div class="spmb-alert spmb-alert-info" style="margin-bottom: 24px; border: 2.5px solid var(--nb-black); box-shadow: var(--shadow-sm); background: #EFF6FF; padding: 18px 20px;">
                        <i class="ph-bold ph-info" style="font-size: 1.8rem; color: #0A4D68; flex-shrink: 0;"></i>
                        <div style="flex: 1;">
                            <strong style="font-size: 1.05rem; color: #053B50;">Akun Anda Sudah Terdaftar!</strong><br>
                            <span style="font-size: 0.92rem; color: #334155; line-height: 1.5;">
                                Anda telah memiliki formulir pendaftaran dengan Nomor Registrasi <strong><?php echo htmlspecialchars($existingForm['no_pendaftaran']); ?></strong> (Status: <strong><?php echo htmlspecialchars($existingForm['status']); ?></strong>).
                            </span>
                            <div style="margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap;">
                                <a href="cek-status.php" class="spmb-btn spmb-btn-sm spmb-btn-primary" style="padding: 8px 16px;">
                                    <i class="ph-bold ph-magnifying-glass"></i> Cek Status Pendaftaran Anda
                                </a>
                                <a href="edit.php?no=<?php echo urlencode($existingForm['no_pendaftaran']); ?>&nisn=<?php echo urlencode($existingForm['nisn']); ?>" class="spmb-btn spmb-btn-sm spmb-btn-secondary" style="padding: 8px 16px;">
                                    <i class="ph-bold ph-pencil-simple"></i> Perbaiki Formulir / Berkas
                                </a>
                            </div>
                        </div>
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
                                    <button type="button" class="spmb-cancel-upload-btn" onclick="cancelDoc('berkas_prestasi', 'prestasiPreview', 'previewImgPrestasi', 'previewIconPrestasi', 'fileNameDisplayPrestasi')" title="Batal pilihan"><i class="ph-bold ph-x"></i></button>
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
                                <div style="position:relative;">
                                    <input type="text" name="nisn" id="nisn" class="spmb-input" placeholder="Contoh: 0071234567" maxlength="10" value="<?php echo htmlspecialchars($_POST['nisn'] ?? ''); ?>" required autocomplete="off">
                                    <span id="nisn_spinner" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); display:none; color:#0A4D68;">
                                        <i class="ph-bold ph-spinner" style="animation: spin 1s linear infinite; font-size:1.15rem; display:inline-block;"></i>
                                    </span>
                                </div>
                                <div id="nisn_feedback" class="spmb-input-feedback"></div>
                                <div class="spmb-input-helper" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">
                                    <span>10 digit angka resmi dari SMP/MTs.</span>
                                    <a href="https://nisn.data.kemendikdasmen.go.id/" target="_blank" rel="noopener noreferrer" style="color:#0A4D68; font-weight:700; text-decoration:underline; font-size:0.75rem;">
                                        <i class="ph-bold ph-arrow-square-out"></i> Cek di Portal Kemendikbud
                                    </a>
                                </div>
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
                            <input type="text" name="nama_lengkap" id="nama_lengkap" class="spmb-input" placeholder="Sesuai dengan ijazah SMP/MTs atau Akta Kelahiran" value="<?php echo htmlspecialchars($valNamaLengkap); ?>" required>
                            <div class="spmb-input-helper"><i class="ph-bold ph-info"></i> Otomatis terisi dari data akun Anda, masih dapat diedit jika diperlukan.</div>
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
                                <div style="position:relative;">
                                    <input type="tel" name="no_hp" id="no_hp" class="spmb-input" placeholder="Contoh: 081234567890" value="<?php echo htmlspecialchars($valNoHp); ?>" required autocomplete="tel">
                                    <span id="no_hp_spinner" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); display:none; color:#0A4D68;">
                                        <i class="ph-bold ph-spinner" style="animation: spin 1s linear infinite; font-size:1.15rem; display:inline-block;"></i>
                                    </span>
                                </div>
                                <div id="no_hp_feedback" class="spmb-input-feedback"></div>
                                <div class="spmb-input-helper">Untuk menerima notifikasi status kelulusan (otomatis terisi dari akun, masih dapat diedit).</div>
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
                                <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="spmb-input" value="<?php echo htmlspecialchars($valTanggalLahir); ?>" min="<?php echo $minBirthDate; ?>" max="<?php echo $maxBirthDate; ?>" required>
                                <div id="tgl_lahir_feedback" class="spmb-input-feedback" style="margin-top: 6px;"></div>
                                <div class="spmb-input-helper">Usia wajib 13 s.d. 21 tahun per 1 Juli <?php echo $refYear; ?> (sesuai ketentuan PPDB).</div>
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
                                        <button type="button" class="spmb-cancel-upload-btn" onclick="cancelDoc('foto', 'previewBoxFoto', 'previewImgFoto', null, 'fileNameFoto')" title="Hapus foto"><i class="ph-bold ph-x"></i></button>
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
                                        <button type="button" class="spmb-cancel-upload-btn" onclick="cancelDoc('berkas_kk', 'previewBoxKK', 'previewImgKK', 'previewIconKK', 'fileNameKK')" title="Batal pilihan"><i class="ph-bold ph-x"></i></button>
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
                                        <button type="button" class="spmb-cancel-upload-btn" onclick="cancelDoc('berkas_akta', 'previewBoxAkta', 'previewImgAkta', 'previewIconAkta', 'fileNameAkta')" title="Batal pilihan"><i class="ph-bold ph-x"></i></button>
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
                                        <button type="button" class="spmb-cancel-upload-btn" onclick="cancelDoc('berkas_ijazah', 'previewBoxIjazah', 'previewImgIjazah', 'previewIconIjazah', 'fileNameIjazah')" title="Batal pilihan"><i class="ph-bold ph-x"></i></button>
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
                                    <p class="spmb-upload-card-desc">Scan / foto KTP Ayah, Ibu, atau Wali (JPG/PNG/PDF, Maks 3MB). Bisa upload hingga 2 foto (KTP Ayah &amp; Ibu).</p>
                                </div>
                                <div>
                                    <div id="ktpDropzone" class="spmb-upload-dropzone" onclick="document.getElementById('berkas_ktp_ortu').click();">
                                        <i class="ph-bold ph-upload-simple" style="font-size:1.6rem; color:#7C3AED;"></i>
                                        <div style="font-weight:700; font-size:0.85rem; margin-top:4px;">Pilih Berkas KTP</div>
                                        <span style="font-size:0.75rem; color:#64748B;">Bisa pilih 1 atau 2 foto sekaligus</span>
                                    </div>
                                    <input type="file" name="berkas_ktp_ortu[]" id="berkas_ktp_ortu" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none;" multiple required onchange="handleKtpChange(this)">
                                    <input type="file" id="berkas_ktp_extra" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none;" onchange="handleKtpExtra(this)">
                                    <div id="previewBoxKtpContainer" style="display:none;"></div>
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
                                        <button type="button" class="spmb-cancel-upload-btn" onclick="cancelDoc('berkas_kip', 'previewBoxKip', 'previewImgKip', 'previewIconKip', 'fileNameKip')" title="Batal pilihan"><i class="ph-bold ph-x"></i></button>
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

    let isPhoneRegistered = false;
    let isNisnRegistered = false;

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

        // Validasi khusus Langkah 2 (Data Diri Calon Siswa)
        if (currentStep === 2) {
            const nisnInput = document.getElementById('nisn');
            if (nisnInput) {
                const nisnVal = nisnInput.value.trim();
                if (!/^[0-9]{10}$/.test(nisnVal)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'NISN Tidak Valid',
                        text: 'NISN harus berupa 10 digit angka valid.',
                        confirmButtonColor: '#0A4D68'
                    });
                    nisnInput.focus();
                    return false;
                }
                if (isNisnRegistered) {
                    Swal.fire({
                        icon: 'error',
                        title: 'NISN Sudah Terdaftar',
                        text: 'NISN ini sudah pernah didaftarkan. Harap gunakan menu Cek Status Pendaftaran.',
                        confirmButtonColor: '#0A4D68'
                    });
                    nisnInput.focus();
                    return false;
                }
            }

            const hpInput = document.getElementById('no_hp');
            if (hpInput) {
                const hpVal = hpInput.value.trim();
                let clean = hpVal.replace(/[^0-9]/g, '');
                if (clean.startsWith('62')) clean = '0' + clean.slice(2);
                else if (!clean.startsWith('0')) clean = '0' + clean;

                if (!/^08[1-9][0-9]{7,11}$/.test(clean)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nomor WhatsApp Tidak Valid',
                        text: 'Nomor WhatsApp calon siswa harus diawali 08/628 dan terdiri dari 10 sampai 14 digit angka.',
                        confirmButtonColor: '#0A4D68'
                    });
                    hpInput.focus();
                    return false;
                }
            }

            if (typeof validateTanggalLahir === 'function') {
                if (!validateTanggalLahir(true)) {
                    const tglInput = document.getElementById('tanggal_lahir');
                    if (tglInput) tglInput.focus();
                    return false;
                }
            }
        }

        // Validasi khusus Langkah 3 (Data Orang Tua)
        if (currentStep === 3) {
            const ortuInput = document.getElementById('no_hp_ortu');
            if (ortuInput) {
                const ortuVal = ortuInput.value.trim();
                let clean = ortuVal.replace(/[^0-9]/g, '');
                if (clean.startsWith('62')) clean = '0' + clean.slice(2);
                else if (!clean.startsWith('0')) clean = '0' + clean;

                if (!/^08[1-9][0-9]{7,11}$/.test(clean)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nomor WhatsApp Orang Tua Tidak Valid',
                        text: 'Nomor WhatsApp orang tua harus diawali 08/628 dan berupa nomor seluler Indonesia yang valid (10-14 digit).',
                        confirmButtonColor: '#0A4D68'
                    });
                    ortuInput.focus();
                    return false;
                }
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

    function cancelDoc(inputId, previewBoxId, previewImgId, previewIconId, previewNameId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = '';
        }
        const box = document.getElementById(previewBoxId);
        if (box) box.style.display = 'none';
        const img = previewImgId ? document.getElementById(previewImgId) : null;
        if (img) {
            img.src = '';
            img.style.display = 'none';
        }
        const icon = previewIconId ? document.getElementById(previewIconId) : null;
        if (icon) icon.style.display = 'none';
        const nameEl = previewNameId ? document.getElementById(previewNameId) : null;
        if (nameEl) nameEl.textContent = '';
    }

    // State Berkas KTP Orang Tua (Maksimal 2 foto)
    let selectedKtpFiles = [];

    function handleKtpChange(input) {
        if (!input.files || input.files.length === 0) return;
        
        if (input.files.length > 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Maksimal 2 Foto KTP',
                text: 'Anda memilih ' + input.files.length + ' foto. Sistem hanya akan memproses 2 foto pertama.',
                confirmButtonColor: '#0A4D68'
            });
        }

        selectedKtpFiles = Array.from(input.files).slice(0, 2);
        syncKtpInputFiles();
        renderKtpPreviews();
    }

    function handleKtpExtra(extraInput) {
        if (!extraInput.files || extraInput.files.length === 0) return;
        if (selectedKtpFiles.length >= 2) return;

        selectedKtpFiles.push(extraInput.files[0]);
        extraInput.value = '';
        syncKtpInputFiles();
        renderKtpPreviews();
    }

    function removeKtpFile(index) {
        if (index >= 0 && index < selectedKtpFiles.length) {
            selectedKtpFiles.splice(index, 1);
            syncKtpInputFiles();
            renderKtpPreviews();
        }
    }

    function cancelAllKtp() {
        selectedKtpFiles = [];
        syncKtpInputFiles();
        renderKtpPreviews();
    }

    function syncKtpInputFiles() {
        const mainInput = document.getElementById('berkas_ktp_ortu');
        if (!mainInput) return;
        try {
            const dt = new DataTransfer();
            selectedKtpFiles.forEach(function(file) {
                dt.items.add(file);
            });
            mainInput.files = dt.files;
        } catch (e) {
            console.warn('DataTransfer not supported:', e);
        }
    }

    function renderKtpPreviews() {
        const dropzone = document.getElementById('ktpDropzone');
        const container = document.getElementById('previewBoxKtpContainer');
        const mainInput = document.getElementById('berkas_ktp_ortu');
        if (!container) return;

        if (selectedKtpFiles.length === 0) {
            if (dropzone) dropzone.style.display = 'block';
            container.style.display = 'none';
            container.innerHTML = '';
            if (mainInput) mainInput.value = '';
            return;
        }

        if (dropzone) dropzone.style.display = 'none';
        container.style.display = 'block';
        container.innerHTML = '';

        const grid = document.createElement('div');
        grid.style.display = 'grid';
        grid.style.gridTemplateColumns = 'repeat(2, minmax(0, 1fr))';
        grid.style.gap = '8px';
        grid.style.width = '100%';
        grid.style.minWidth = '0';
        grid.style.boxSizing = 'border-box';

        selectedKtpFiles.forEach(function(file, idx) {
            const card = document.createElement('div');
            card.style.position = 'relative';
            card.style.background = '#FFFFFF';
            card.style.border = '1.5px solid #CBD5E1';
            card.style.borderRadius = '8px';
            card.style.padding = '6px 8px';
            card.style.display = 'flex';
            card.style.flexDirection = 'column';
            card.style.gap = '4px';
            card.style.minWidth = '0';
            card.style.width = '100%';
            card.style.boxSizing = 'border-box';
            card.style.overflow = 'hidden';

            // Close button with X
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'spmb-cancel-upload-btn';
            closeBtn.style.position = 'absolute';
            closeBtn.style.top = '4px';
            closeBtn.style.right = '4px';
            closeBtn.style.width = '22px';
            closeBtn.style.height = '22px';
            closeBtn.style.fontSize = '12px';
            closeBtn.style.zIndex = '5';
            closeBtn.style.boxShadow = '0 1px 3px rgba(0,0,0,0.2)';
            closeBtn.title = 'Hapus foto ini';
            closeBtn.innerHTML = '<i class="ph-bold ph-x"></i>';
            closeBtn.onclick = function(e) {
                e.stopPropagation();
                removeKtpFile(idx);
            };
            card.appendChild(closeBtn);

            // Preview box
            const previewBox = document.createElement('div');
            previewBox.style.height = '72px';
            previewBox.style.width = '100%';
            previewBox.style.maxWidth = '100%';
            previewBox.style.borderRadius = '6px';
            previewBox.style.overflow = 'hidden';
            previewBox.style.background = '#F8FAFC';
            previewBox.style.border = '1px solid #E2E8F0';
            previewBox.style.display = 'flex';
            previewBox.style.alignItems = 'center';
            previewBox.style.justifyContent = 'center';
            previewBox.style.boxSizing = 'border-box';

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.style.width = '100%';
                img.style.maxWidth = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                img.style.display = 'block';
                const reader = new FileReader();
                reader.onload = function(e) { img.src = e.target.result; };
                reader.readAsDataURL(file);
                previewBox.appendChild(img);
            } else {
                const iconDiv = document.createElement('div');
                iconDiv.style.color = '#DC2626';
                iconDiv.style.fontSize = '1.8rem';
                iconDiv.innerHTML = '<i class="ph-bold ph-file-pdf"></i>';
                previewBox.appendChild(iconDiv);
            }
            card.appendChild(previewBox);

            // Filename
            const nameDiv = document.createElement('div');
            nameDiv.style.fontSize = '0.72rem';
            nameDiv.style.fontWeight = '700';
            nameDiv.style.color = '#1E293B';
            nameDiv.style.whiteSpace = 'nowrap';
            nameDiv.style.overflow = 'hidden';
            nameDiv.style.textOverflow = 'ellipsis';
            nameDiv.style.width = '100%';
            nameDiv.style.maxWidth = '100%';
            nameDiv.style.boxSizing = 'border-box';
            nameDiv.title = file.name;
            nameDiv.textContent = file.name;
            card.appendChild(nameDiv);

            // Sub info
            const infoRow = document.createElement('div');
            infoRow.style.display = 'flex';
            infoRow.style.justifyContent = 'space-between';
            infoRow.style.alignItems = 'center';
            infoRow.style.fontSize = '0.68rem';
            infoRow.style.width = '100%';
            infoRow.style.minWidth = '0';
            infoRow.style.boxSizing = 'border-box';
            infoRow.innerHTML = '<span style="color:#16A34A; font-weight:700;"><i class="ph-bold ph-check"></i> Foto ' + (idx + 1) + '</span><span style="color:#64748B;">' + Math.round(file.size / 1024) + ' KB</span>';
            card.appendChild(infoRow);

            grid.appendChild(card);
        });

        // Jika baru 1 file, sediakan slot ke-2 berupa tombol "+ Foto KTP 2 (Opsional)"
        if (selectedKtpFiles.length === 1) {
            const addSlot = document.createElement('div');
            addSlot.style.border = '2px dashed #94A3B8';
            addSlot.style.background = '#F8FAFC';
            addSlot.style.borderRadius = '8px';
            addSlot.style.padding = '8px';
            addSlot.style.display = 'flex';
            addSlot.style.flexDirection = 'column';
            addSlot.style.alignItems = 'center';
            addSlot.style.justifyContent = 'center';
            addSlot.style.minHeight = '100%';
            addSlot.style.height = '100%';
            addSlot.style.minWidth = '0';
            addSlot.style.width = '100%';
            addSlot.style.boxSizing = 'border-box';
            addSlot.style.cursor = 'pointer';
            addSlot.style.transition = 'all 0.15s ease';
            addSlot.title = 'Tambah foto KTP kedua';
            addSlot.innerHTML = '<i class="ph-bold ph-plus-circle" style="font-size:1.5rem; color:#7C3AED;"></i><div style="font-weight:700; font-size:0.75rem; color:#475569; margin-top:4px;">+ Foto KTP 2</div><span style="font-size:0.68rem; color:#94A3B8;">(Opsional)</span>';
            addSlot.onmouseover = function() {
                addSlot.style.borderColor = '#7C3AED';
                addSlot.style.background = '#FAF5FF';
            };
            addSlot.onmouseout = function() {
                addSlot.style.borderColor = '#94A3B8';
                addSlot.style.background = '#F8FAFC';
            };
            addSlot.onclick = function() {
                const extra = document.getElementById('berkas_ktp_extra');
                if (extra) extra.click();
            };
            grid.appendChild(addSlot);
        }

        container.appendChild(grid);

        // Bottom action bar
        const bar = document.createElement('div');
        bar.style.display = 'flex';
        bar.style.justifyContent = 'space-between';
        bar.style.alignItems = 'center';
        bar.style.marginTop = '6px';
        bar.style.padding = '0 2px';
        bar.style.width = '100%';
        bar.style.minWidth = '0';
        bar.style.boxSizing = 'border-box';

        const countText = selectedKtpFiles.length === 2 
            ? '<span style="font-size:0.72rem; color:#16A34A; font-weight:700;"><i class="ph-bold ph-check-circle"></i> 2 Foto KTP terpilih (Lengkap)</span>'
            : '<span style="font-size:0.72rem; color:#64748B;"><i class="ph-bold ph-info"></i> 1 Foto terpilih. Bisa tambah 1 lagi jika ada.</span>';

        bar.innerHTML = countText + '<button type="button" onclick="cancelAllKtp()" style="background:none; border:none; color:#DC2626; font-size:0.72rem; font-weight:700; cursor:pointer; padding:0; display:flex; align-items:center; gap:3px;"><i class="ph-bold ph-trash"></i> Hapus Semua</button>';

        container.appendChild(bar);
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

    let phoneCheckTimer = null;
    let nisnCheckTimer = null;

    function checkPhoneRealtime(val) {
        const feedback = document.getElementById('no_hp_feedback');
        const input = document.getElementById('no_hp');
        const spinner = document.getElementById('no_hp_spinner');
        if (!feedback || !input) return;

        val = val.trim();
        if (!val) {
            feedback.className = 'spmb-input-feedback';
            feedback.innerHTML = '';
            input.classList.remove('is-invalid', 'is-valid');
            if (spinner) spinner.style.display = 'none';
            return;
        }

        // Normalisasi format lokal
        let clean = val.replace(/[^0-9]/g, '');
        if (clean.startsWith('62')) clean = '0' + clean.slice(2);
        else if (!clean.startsWith('0')) clean = '0' + clean;

        if (!/^08[1-9][0-9]{7,11}$/.test(clean)) {
            if (spinner) spinner.style.display = 'none';
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            feedback.className = 'spmb-input-feedback danger';
            feedback.innerHTML = '<i class="ph-bold ph-warning-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>Format nomor HP tidak valid (diawali 08/628 dan 10-14 digit angka).</span>';
            return;
        }

        if (spinner) spinner.style.display = 'none';
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        feedback.className = 'spmb-input-feedback success';
        feedback.innerHTML = '<i class="ph-bold ph-check-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>Format nomor WhatsApp valid.</span>';
    }

    function checkNisnRealtime(val) {
        const feedback = document.getElementById('nisn_feedback');
        const input = document.getElementById('nisn');
        const spinner = document.getElementById('nisn_spinner');
        if (!feedback || !input) return;

        val = val.trim();
        if (!val) {
            feedback.className = 'spmb-input-feedback';
            feedback.innerHTML = '';
            input.classList.remove('is-invalid', 'is-valid');
            if (spinner) spinner.style.display = 'none';
            isNisnRegistered = false;
            return;
        }

        if (!/^[0-9]{10}$/.test(val)) {
            if (spinner) spinner.style.display = 'none';
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            feedback.className = 'spmb-input-feedback danger';
            feedback.innerHTML = '<i class="ph-bold ph-warning-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>NISN harus terdiri dari 10 digit angka.</span>';
            isNisnRegistered = false;
            return;
        }

        if (spinner) spinner.style.display = 'inline-block';
        feedback.className = 'spmb-input-feedback checking';
        feedback.innerHTML = '<i class="ph-bold ph-spinner" style="font-size:1.1rem; flex-shrink:0; animation: spin 1s linear infinite;"></i><span>Memeriksa NISN...</span>';

        const tglInput = document.getElementById('tanggal_lahir');
        const tglVal = tglInput ? tglInput.value : '';

        fetch('cek-nohp.php?nisn=' + encodeURIComponent(val) + '&tanggal_lahir=' + encodeURIComponent(tglVal))
            .then(res => res.json())
            .then(data => {
                if (spinner) spinner.style.display = 'none';
                if (data.registered) {
                    isNisnRegistered = true;
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                    feedback.className = 'spmb-input-feedback danger';
                    feedback.innerHTML = '<i class="ph-bold ph-x-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>' + data.message + ' <a href="cek-status.php?nisn=' + encodeURIComponent(val) + '" target="_blank" style="color:#9F1239; font-weight:700; text-decoration:underline;">Cek Status</a>.</span>';
                } else if (data.valid) {
                    isNisnRegistered = false;
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                    feedback.className = 'spmb-input-feedback success';
                    feedback.innerHTML = '<i class="ph-bold ph-check-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>' + data.message + '</span>';
                }
            })
            .catch(() => {
                if (spinner) spinner.style.display = 'none';
                feedback.className = 'spmb-input-feedback';
                feedback.innerHTML = '';
            });
    }

    // Sinkronisasi dropdown jurusan: disable opsi yang sudah dipilih di dropdown lain
    function syncJurusanDropdowns() {
        const jurusan1 = document.getElementById('jurusan_1');
        const jurusan2 = document.getElementById('jurusan_2');
        if (!jurusan1 || !jurusan2) return;

        const val1 = jurusan1.value;
        const val2 = jurusan2.value;

        // Reset semua option di jurusan_2, lalu disable yang sudah dipilih di jurusan_1
        Array.from(jurusan2.options).forEach(opt => {
            if (opt.value === '') {
                opt.disabled = false; // placeholder tetap aktif
            } else {
                opt.disabled = (opt.value === val1);
            }
        });

        // Reset semua option di jurusan_1, lalu disable yang sudah dipilih di jurusan_2
        Array.from(jurusan1.options).forEach(opt => {
            if (opt.value === '') {
                opt.disabled = false;
            } else {
                opt.disabled = (opt.value === val2);
            }
        });

        // Jika jurusan_2 saat ini sama dengan jurusan_1, reset jurusan_2
        if (val1 && val2 && val1 === val2) {
            jurusan2.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkedJalur = document.querySelector('input[name="jalur"]:checked');
        if (checkedJalur) {
            toggleJalurPrestasi(checkedJalur.value);
        }

        // Pasang listener sinkronisasi jurusan
        const jurusan1 = document.getElementById('jurusan_1');
        const jurusan2 = document.getElementById('jurusan_2');
        if (jurusan1) {
            jurusan1.addEventListener('change', syncJurusanDropdowns);
        }
        if (jurusan2) {
            jurusan2.addEventListener('change', syncJurusanDropdowns);
        }
        // Jalankan sekali saat halaman dimuat (untuk handle kasus POST re-fill)
        syncJurusanDropdowns();

        // Listener Real-time Validasi No HP
        const hpInput = document.getElementById('no_hp');
        if (hpInput) {
            hpInput.addEventListener('input', function() {
                clearTimeout(phoneCheckTimer);
                phoneCheckTimer = setTimeout(() => {
                    checkPhoneRealtime(this.value);
                }, 400);
            });
            hpInput.addEventListener('blur', function() {
                clearTimeout(phoneCheckTimer);
                checkPhoneRealtime(this.value);
            });
            if (hpInput.value.trim() !== '') {
                checkPhoneRealtime(hpInput.value);
            }
        }

        // Listener Real-time Validasi NISN
        const nisnInput = document.getElementById('nisn');
        if (nisnInput) {
            nisnInput.addEventListener('input', function() {
                clearTimeout(nisnCheckTimer);
                nisnCheckTimer = setTimeout(() => {
                    checkNisnRealtime(this.value);
                }, 400);
            });
            nisnInput.addEventListener('blur', function() {
                clearTimeout(nisnCheckTimer);
                checkNisnRealtime(this.value);
            });
            if (nisnInput.value.trim() !== '') {
                checkNisnRealtime(nisnInput.value);
            }
        }

        // Listener Real-time Validasi Tanggal Lahir (13 s.d. 21 Tahun)
        window.validateTanggalLahir = function(showAlert = true) {
            const input = document.getElementById('tanggal_lahir');
            const feedback = document.getElementById('tgl_lahir_feedback');
            if (!input) return true;

            const val = input.value;
            const minD = input.getAttribute('min');
            const maxD = input.getAttribute('max');

            if (!val) {
                if (feedback) {
                    feedback.className = 'spmb-input-feedback';
                    feedback.innerHTML = '';
                }
                input.classList.remove('is-invalid', 'is-valid');
                return true;
            }

            // Hitung usia calon siswa
            const birthDate = new Date(val);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (minD && val < minD) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (feedback) {
                    feedback.className = 'spmb-input-feedback danger';
                    feedback.innerHTML = '<i class="ph-bold ph-warning-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>Batas usia terlewati: Usia calon siswa lebih dari 21 tahun (Maksimal 21 tahun per 1 Juli <?php echo $refYear; ?>).</span>';
                }
                if (showAlert && typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Usia Melebihi 21 Tahun',
                        text: 'Berdasarkan ketentuan Permendikbud PPDB SMK, batas usia calon peserta didik maksimal adalah 21 tahun per 1 Juli <?php echo $refYear; ?>.',
                        confirmButtonColor: '#0A4D68'
                    });
                }
                return false;
            }

            if (maxD && val > maxD) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (feedback) {
                    feedback.className = 'spmb-input-feedback danger';
                    feedback.innerHTML = '<i class="ph-bold ph-warning-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>Usia belum mencukupi: Usia calon siswa kurang dari 13 tahun (Minimal 13 tahun).</span>';
                }
                if (showAlert && typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Usia Kurang Dari 13 Tahun',
                        text: 'Berdasarkan ketentuan Permendikbud PPDB SMK, batas usia calon peserta didik minimal adalah 13 tahun.',
                        confirmButtonColor: '#0A4D68'
                    });
                }
                return false;
            }

            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            if (feedback) {
                feedback.className = 'spmb-input-feedback success';
                feedback.innerHTML = '<i class="ph-bold ph-check-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>Tanggal lahir valid' + (age > 0 ? ' (Usia ' + age + ' tahun)' : '') + '.</span>';
            }
            return true;
        };

        const tglInput = document.getElementById('tanggal_lahir');
        if (tglInput) {
            tglInput.addEventListener('change', function() {
                validateTanggalLahir(true);
            });
            tglInput.addEventListener('input', function() {
                validateTanggalLahir(false);
            });
            if (tglInput.value) {
                validateTanggalLahir(false);
            }
        }

        // Validasi submit form final
        const formEl = document.getElementById('spmbForm');
        if (formEl) {
            formEl.addEventListener('submit', function(e) {
                if (!validateTanggalLahir(true)) {
                    e.preventDefault();
                    goToStep(1);
                    if (tglInput) tglInput.focus();
                    return false;
                }
                if (isNisnRegistered) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'NISN Sudah Terdaftar',
                        text: 'NISN calon siswa sudah pernah didaftarkan. Silakan gunakan menu Cek Status Pendaftaran.',
                        confirmButtonColor: '#0A4D68'
                    });
                    goToStep(2);
                    if (nisnInput) nisnInput.focus();
                    return false;
                }
            });
        }

        <?php if (!empty($error)): ?>
        // Jika ada error backend (misal salah upload berkas), arahkan ke langkah terkait
        goToStep(<?php echo (!empty($no_hp) && strpos($error, 'WhatsApp') !== false) ? '2' : '4'; ?>);
        <?php endif; ?>
    });
    </script>

    <?php include __DIR__ . '/footer_minimal.php'; ?>
</body>
</html>
