<?php
// spmb/edit.php
// Halaman Perbaikan / Edit Formulir Pendaftaran Siswa Baru
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'edit';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

$error = '';
$success = '';

// Ambil parameter akses
$noPendaftaran = sanitize_input($_GET['no'] ?? ($_POST['no_pendaftaran'] ?? ''));
$nisnInput     = sanitize_input($_GET['nisn'] ?? ($_POST['nisn_auth'] ?? ''));

$p = null;

// Jika parameter dikirim, verifikasi pendaftar
if (!empty($noPendaftaran) && !empty($nisnInput)) {
    $stmt = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `no_pendaftaran` = ? AND `nisn` = ?");
    $stmt->execute([$noPendaftaran, $nisnInput]);
    $p = $stmt->fetch();

    if (!$p) {
        $error = "Data pendaftar dengan No. Registrasi <strong>" . htmlspecialchars($noPendaftaran) . "</strong> dan NISN tersebut tidak ditemukan. Silakan periksa kembali.";
    }
}

// Handle pesan sukses jika redirect
$msg = sanitize_input($_GET['msg'] ?? '');

// Proses Simpan Perubahan Form Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_edit') {
    if (!$p) {
        $error = "Sesi verifikasi tidak valid. Harap masukkan Nomor Registrasi dan NISN Anda.";
    } elseif ($p['status'] === 'Diterima') {
        $error = "Pendaftaran Anda sudah dinyatakan LULUS (Diterima) dan tidak dapat diubah lagi. Silakan cetak kartu bukti pendaftaran.";
    } elseif ($p['status'] === 'Ditolak') {
        $error = "Status pendaftaran Anda telah ditolak dan tidak dapat diedit.";
    } else {
        // Ambil dan sanitasi data yang diupdate
        $nisnBaru         = sanitize_input($_POST['nisn'] ?? '');
        $nik              = sanitize_input($_POST['nik'] ?? '');
        $nama_lengkap     = sanitize_input($_POST['nama_lengkap'] ?? '');
        $jenis_kelamin    = sanitize_input($_POST['jenis_kelamin'] ?? '');
        $tempat_lahir     = sanitize_input($_POST['tempat_lahir'] ?? '');
        $tanggal_lahir    = sanitize_input($_POST['tanggal_lahir'] ?? '');
        $no_hp            = sanitize_input($_POST['no_hp'] ?? '');
        $alamat           = sanitize_input($_POST['alamat'] ?? '');
        $asal_sekolah     = sanitize_input($_POST['asal_sekolah'] ?? '');
        $jurusan_1        = sanitize_input($_POST['jurusan_1'] ?? '');
        $jurusan_2        = sanitize_input($_POST['jurusan_2'] ?? '');
        $jalur            = sanitize_input($_POST['jalur'] ?? 'Reguler');
        $nama_ayah        = sanitize_input($_POST['nama_ayah'] ?? '');
        $nama_ibu         = sanitize_input($_POST['nama_ibu'] ?? '');
        $pekerjaan_ortu   = sanitize_input($_POST['pekerjaan_ortu'] ?? '');
        $pekerjaan_lain   = sanitize_input($_POST['pekerjaan_ortu_lainnya'] ?? '');
        if ($pekerjaan_ortu === 'Lainnya' && !empty($pekerjaan_lain)) {
            $pekerjaan_ortu = $pekerjaan_lain;
        }
        $no_hp_ortu       = sanitize_input($_POST['no_hp_ortu'] ?? '');
        $penghasilan_ortu = sanitize_input($_POST['penghasilan_ortu'] ?? '');

        // Validasi wajib
        if (empty($nisnBaru) || empty($nik) || empty($nama_lengkap) || empty($jenis_kelamin) || 
            empty($tempat_lahir) || empty($tanggal_lahir) || empty($no_hp) || empty($alamat) || 
            empty($asal_sekolah) || empty($jurusan_1) || empty($nama_ayah) || empty($no_hp_ortu)) {
            $error = "Harap lengkapi semua kolom yang bertanda bintang merah (*).";
        } elseif (!preg_match('/^[0-9]{10}$/', $nisnBaru)) {
            $error = "NISN harus berupa 10 digit angka valid.";
        } elseif (!preg_match('/^[0-9]{16}$/', $nik)) {
            $error = "NIK harus berupa 16 digit angka valid sesuai KTP/KK.";
        } elseif ($jurusan_1 === $jurusan_2) {
            $error = "Jurusan Pilihan 1 dan Pilihan 2 tidak boleh sama.";
        } else {
            // Cek jika NISN diganti dan ternyata bentrok dengan pendaftar lain
            if ($nisnBaru !== $p['nisn']) {
                $checkNisn = $pdo->prepare("SELECT `id` FROM `spmb_pendaftar` WHERE `nisn` = ? AND `id` != ?");
                $checkNisn->execute([$nisnBaru, $p['id']]);
                if ($checkNisn->fetch()) {
                    $error = "NISN <strong>$nisnBaru</strong> sudah digunakan oleh pendaftar lain.";
                }
            }

            if (empty($error)) {
                // Upload File Helper Function (Opsional: Jika tidak diupload, tetap gunakan file lama)
                $uploadFileOptional = function($fieldKey, $prefix, $oldFile, $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'], $maxBytes = 3145728) use (&$error) {
                    if (!isset($_FILES[$fieldKey]) || $_FILES[$fieldKey]['error'] === UPLOAD_ERR_NO_FILE) {
                        return $oldFile; // Gunakan file lama
                    }

                    if ($_FILES[$fieldKey]['error'] !== UPLOAD_ERR_OK) {
                        $error = "Gagal mengunggah berkas $fieldKey (Kode error: {$_FILES[$fieldKey]['error']}).";
                        return $oldFile;
                    }

                    $fileTmp  = $_FILES[$fieldKey]['tmp_name'];
                    $fileName = $_FILES[$fieldKey]['name'];
                    $fileSize = $_FILES[$fieldKey]['size'];
                    $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (!in_array($ext, $allowed)) {
                        $error = "Format file $fieldKey tidak sesuai. Hanya diperbolehkan format " . strtoupper(implode(', ', $allowed)) . ".";
                        return $oldFile;
                    }

                    if ($fileSize > $maxBytes) {
                        $maxMB = round($maxBytes / (1024 * 1024));
                        $error = "Ukuran file $fieldKey melebihi batas maksimal ({$maxMB} MB).";
                        return $oldFile;
                    }

                    $newFileName = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $destination = __DIR__ . '/uploads/' . $newFileName;
                    if (move_uploaded_file($fileTmp, $destination)) {
                        return $newFileName;
                    } else {
                        $error = "Gagal menyimpan berkas $fieldKey ke server.";
                        return $oldFile;
                    }
                };

                $foto_path        = $uploadFileOptional('foto', 'foto', $p['foto'], ['jpg', 'jpeg', 'png', 'webp'], 2097152);
                $berkas_kk_path   = empty($error) ? $uploadFileOptional('berkas_kk', 'kk', $p['berkas_kk']) : $p['berkas_kk'];
                $berkas_akta_path = empty($error) ? $uploadFileOptional('berkas_akta', 'akta', $p['berkas_akta']) : $p['berkas_akta'];
                $berkas_ijazah_path = empty($error) ? $uploadFileOptional('berkas_ijazah', 'ijazah', $p['berkas_ijazah']) : $p['berkas_ijazah'];
                $berkas_ktp_path  = empty($error) ? $uploadFileOptional('berkas_ktp_ortu', 'ktp', $p['berkas_ktp_ortu']) : $p['berkas_ktp_ortu'];
                $berkas_kip_path  = empty($error) ? $uploadFileOptional('berkas_kip', 'kip', $p['berkas_kip']) : $p['berkas_kip'];
                $berkas_pres_path = empty($error) ? $uploadFileOptional('berkas_prestasi', 'prestasi', $p['berkas_prestasi']) : $p['berkas_prestasi'];

                if ($jalur === 'Prestasi' && empty($berkas_pres_path)) {
                    $error = "Untuk Jalur Prestasi, Anda wajib mengunggah Sertifikat / Piagam Prestasi.";
                }

                if (empty($error)) {
                    // Update catatan admin: catat riwayat perbaikan oleh siswa
                    $waktuPerbaikan = date('d/m/Y H:i');
                    $catatanBaru = trim($p['catatan_admin'] ?? '');
                    $catatanBaru .= "\n[Data & berkas diperbarui oleh calon siswa pada $waktuPerbaikan WIB]";

                    // Status otomatis dikembalikan ke 'Menunggu Verifikasi' agar diperiksa ulang oleh admin
                    $statusBaru = 'Menunggu Verifikasi';

                    $updateSql = "UPDATE `spmb_pendaftar` SET 
                        `nisn` = ?, `nik` = ?, `nama_lengkap` = ?, `jenis_kelamin` = ?,
                        `tempat_lahir` = ?, `tanggal_lahir` = ?, `no_hp` = ?, `alamat` = ?,
                        `asal_sekolah` = ?, `jurusan_1` = ?, `jurusan_2` = ?, `jalur` = ?,
                        `nama_ayah` = ?, `nama_ibu` = ?, `pekerjaan_ortu` = ?, `no_hp_ortu` = ?, `penghasilan_ortu` = ?,
                        `foto` = ?, `berkas_kk` = ?, `berkas_akta` = ?, `berkas_ijazah` = ?, `berkas_ktp_ortu` = ?,
                        `berkas_kip` = ?, `berkas_prestasi` = ?, `status` = ?, `catatan_admin` = ?,
                        `tenggat_perbaikan` = NULL
                        WHERE `id` = ?";

                    $updStmt = $pdo->prepare($updateSql);
                    $updStmt->execute([
                        $nisnBaru, $nik, $nama_lengkap, $jenis_kelamin,
                        $tempat_lahir, $tanggal_lahir, $no_hp, $alamat,
                        $asal_sekolah, $jurusan_1, $jurusan_2, $jalur,
                        $nama_ayah, $nama_ibu, $pekerjaan_ortu, $no_hp_ortu, $penghasilan_ortu,
                        $foto_path, $berkas_kk_path, $berkas_akta_path, $berkas_ijazah_path, $berkas_ktp_path,
                        $berkas_kip_path, $berkas_pres_path, $statusBaru, $catatanBaru,
                        $p['id']
                    ]);

                    // Redirect ke halaman cek status dengan pesan sukses
                    header("Location: cek-status.php?no=" . urlencode($p['no_pendaftaran']) . "&msg=edited");
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
    <title>Perbaiki Formulir Pendaftaran - SPMB SMKS SUKAPURA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="spmb.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .edit-sec-card {
            background: #FFFFFF;
            border: var(--border-thick);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-md);
            padding: 24px;
            margin-bottom: 24px;
        }
        .edit-sec-title {
            font-family: var(--font-heading);
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--nb-navy-dark);
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: var(--border-thin);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .doc-preview-item {
            background: #F8FAFC;
            border: 1.5px solid #CBD5E1;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body class="spmb-body">

    <?php include '../components/header.php'; ?>

    <main style="padding: 40px 0 80px;">
        <div class="spmb-container" style="max-width: 860px;">

            <!-- Breadcrumbs -->
            <div style="margin-bottom: 20px; font-size: 0.9rem; font-weight: 700; color: #555; display: flex; align-items: center; gap: 8px;">
                <a href="../index.php" style="color: inherit; text-decoration: none;">Beranda</a>
                <span>/</span>
                <a href="index.php" style="color: inherit; text-decoration: none;">SPMB</a>
                <span>/</span>
                <a href="cek-status.php<?php echo $p ? '?no=' . urlencode($p['no_pendaftaran']) : ''; ?>" style="color: inherit; text-decoration: none;">Cek Status</a>
                <span>/</span>
                <span style="color: var(--nb-navy-dark);">Perbaiki Formulir</span>
            </div>

            <!-- JIKA BELUM ADA DATA PENDAFTAR (INPUT NO REG & NISN) -->
            <?php if (!$p): ?>
                <div style="background:var(--nb-white); border:var(--border-thick); border-radius:var(--radius-card); box-shadow:var(--shadow-xl); padding:35px; text-align:center;">
                    <div style="width:70px; height:70px; background:#FEF3C7; border:var(--border-md); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:#D97706; font-size:2.2rem;">
                        <i class="ph-bold ph-pencil-simple-line"></i>
                    </div>
                    <h2 style="font-family:var(--font-heading); font-size:1.6rem; font-weight:800; color:var(--nb-navy-dark); margin-bottom:8px;">
                        Verifikasi Akses Perbaikan Data
                    </h2>
                    <p style="color:#555; font-size:0.95rem; max-width:520px; margin:0 auto 24px;">
                        Masukkan Nomor Registrasi dan NISN Anda yang terdaftar untuk membuka formulir perbaikan data dan unggah ulang berkas.
                    </p>

                    <?php if (!empty($error)): ?>
                        <div class="spmb-alert spmb-alert-danger" style="margin-bottom:20px; text-align:left;">
                            <i class="ph-bold ph-warning-circle" style="font-size:1.5rem;"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="edit.php" method="GET" style="max-width:440px; margin:0 auto; text-align:left;">
                        <div class="spmb-form-group">
                            <label class="spmb-label" for="input_no">Nomor Registrasi SPMB <span class="required">*</span></label>
                            <input type="text" name="no" id="input_no" class="spmb-input" placeholder="Contoh: REG-SKPR-XXXXX" value="<?php echo htmlspecialchars($noPendaftaran); ?>" required>
                        </div>
                        <div class="spmb-form-group">
                            <label class="spmb-label" for="input_nisn">10 Digit NISN <span class="required">*</span></label>
                            <input type="text" name="nisn" id="input_nisn" class="spmb-input" placeholder="Contoh: 0071234567" maxlength="10" value="<?php echo htmlspecialchars($nisnInput); ?>" required>
                        </div>
                        <button type="submit" class="spmb-btn spmb-btn-primary" style="width:100%; padding:14px; font-size:1rem;">
                            <i class="ph-bold ph-arrow-right"></i> Buka Formulir Edit
                        </button>
                    </form>
                </div>

            <?php else: ?>

                <!-- HEADER CARD -->
                <div style="background:var(--nb-navy-dark); color:#FFF; border:var(--border-thick); border-radius:var(--radius-card); box-shadow:var(--shadow-lg); padding:24px 28px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                    <div>
                        <span style="font-size:0.8rem; font-weight:700; color:#93C5FD; text-transform:uppercase; letter-spacing:0.5px;">Formulir Perbaikan Siswa</span>
                        <h1 style="font-family:var(--font-heading); font-size:1.6rem; font-weight:800; margin-top:2px;">
                            <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                        </h1>
                        <span style="font-size:0.88rem; color:#E2E8F0;">
                            No. Registrasi: <strong><?php echo htmlspecialchars($p['no_pendaftaran']); ?></strong> | NISN: <strong><?php echo htmlspecialchars($p['nisn']); ?></strong>
                        </span>
                    </div>
                    <div>
                        <?php echo get_status_badge_html($p['status']); ?>
                    </div>
                </div>

                <!-- ALERT CATATAN PANITIA JIKA PERLU PERBAIKAN -->
                <?php if ($p['status'] === 'Perlu Perbaikan'): ?>
                    <div style="background:#FEF3C7; border:2.5px solid #F59E0B; border-radius:var(--radius-card); box-shadow:var(--shadow-md); padding:20px; margin-bottom:28px; display:flex; gap:16px; align-items:flex-start;">
                        <i class="ph-bold ph-warning-circle" style="font-size:2.4rem; color:#D97706; flex-shrink:0;"></i>
                        <div>
                            <h3 style="font-family:var(--font-heading); font-size:1.2rem; font-weight:800; color:#78350F; margin-bottom:6px;">
                                Catatan Perbaikan dari Panitia SPMB:
                            </h3>
                            <div style="background:#FFFFFF; border:1.5px solid #F59E0B; border-radius:8px; padding:12px 16px; font-size:0.95rem; font-weight:600; color:#B45309; line-height:1.5; white-space:pre-line;">
                                <?php echo htmlspecialchars($p['catatan_admin'] ?: 'Harap periksa kembali isian identitas atau unggah ulang dokumen yang kurang jelas.'); ?>
                            </div>
                            <div style="font-size:0.85rem; color:#92400E; margin-top:10px;">
                                <i class="ph-bold ph-info"></i> Silakan sesuaikan kolom isian atau ganti pilihan jurusan di bawah ini, lalu klik <strong>"Simpan &amp; Ajukan Perbaikan"</strong>.
                            </div>

                            <?php if (!empty($p['tenggat_perbaikan'])): ?>
                                <div style="background:#FFF1F2; border:2px solid #E11D48; border-radius:10px; padding:14px 18px; margin-top:14px; display:flex; gap:12px; align-items:center;">
                                    <i class="ph-bold ph-alarm" style="font-size:2.2rem; color:#E11D48; flex-shrink:0;"></i>
                                    <div>
                                        <div style="font-weight:800; color:#9F1239; font-size:0.95rem;">TENGGAT WAKTU KONFIRMASI GANTI JURUSAN:</div>
                                        <div style="font-size:0.88rem; color:#BE123C; margin-top:3px; line-height:1.4;">
                                            Harap pilih jurusan lain dan simpan formulir perbaikan Anda sebelum <strong><?php echo date('d F Y', strtotime($p['tenggat_perbaikan'])) . ', pukul ' . date('H:i', strtotime($p['tenggat_perbaikan'])); ?> WIB</strong> (Batas Waktu: 1 Minggu). Jika melewati batas waktu tersebut tanpa pergantian jurusan, sistem akan otomatis menetapkan status pendaftaran menjadi <strong>DITOLAK</strong>.
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="spmb-alert spmb-alert-danger" style="margin-bottom:24px;">
                        <i class="ph-bold ph-warning-circle" style="font-size:1.6rem;"></i>
                        <div><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>

                <!-- FORM EDIT SISWA -->
                <form action="edit.php?no=<?php echo urlencode($p['no_pendaftaran']); ?>&nisn=<?php echo urlencode($p['nisn']); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_edit">
                    <input type="hidden" name="no_pendaftaran" value="<?php echo htmlspecialchars($p['no_pendaftaran']); ?>">
                    <input type="hidden" name="nisn_auth" value="<?php echo htmlspecialchars($p['nisn']); ?>">

                    <!-- BAGIAN 1: JALUR & JURUSAN -->
                    <div class="edit-sec-card">
                        <h3 class="edit-sec-title">
                            <i class="ph-bold ph-graduation-cap"></i> 1. Jalur &amp; Pilihan Kompetensi Keahlian
                        </h3>
                        
                        <div class="spmb-form-group">
                            <label class="spmb-label" for="jalur">Jalur Pendaftaran <span class="required">*</span></label>
                            <select name="jalur" id="jalur" class="spmb-select" required onchange="handleJalurChange(this.value)">
                                <?php foreach ($DAFTAR_JALUR as $kJalur => $vJalur): ?>
                                    <option value="<?php echo $kJalur; ?>" <?php echo ($p['jalur'] === $kJalur) ? 'selected' : ''; ?>>
                                        <?php echo $vJalur; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="jurusan_1">Jurusan Pilihan 1 (Utama) <span class="required">*</span></label>
                                <select name="jurusan_1" id="jurusan_1" class="spmb-select" required>
                                    <?php foreach ($DAFTAR_JURUSAN as $kode => $j): 
                                        $infoK = get_jurusan_info_kuota($pdo, $kode, $p['id']);
                                        $isPenuh = $infoK['penuh'];
                                        $selected = ($p['jurusan_1'] === $kode) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $kode; ?>" <?php echo $selected; ?>>
                                            <?php echo $kode; ?> - <?php echo $j['nama']; ?> <?php echo $isPenuh ? ' [⚠️ KUOTA PENUH]' : ' (Sisa: ' . $infoK['sisa'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="jurusan_2">Jurusan Pilihan 2 (Cadangan)</label>
                                <select name="jurusan_2" id="jurusan_2" class="spmb-select">
                                    <option value="">-- Tidak Memilih Pilihan 2 --</option>
                                    <?php foreach ($DAFTAR_JURUSAN as $kode => $j): 
                                        $infoK = get_jurusan_info_kuota($pdo, $kode, $p['id']);
                                        $isPenuh = $infoK['penuh'];
                                        $selected = ($p['jurusan_2'] === $kode) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $kode; ?>" <?php echo $selected; ?>>
                                            <?php echo $kode; ?> - <?php echo $j['nama']; ?> <?php echo $isPenuh ? ' [⚠️ KUOTA PENUH]' : ' (Sisa: ' . $infoK['sisa'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="spmb-input-helper">Pilihlah jurusan yang masih memiliki sisa kuota.</div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 2: BIODATA SISWA -->
                    <div class="edit-sec-card">
                        <h3 class="edit-sec-title">
                            <i class="ph-bold ph-user-circle"></i> 2. Biodata Calon Siswa
                        </h3>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="nisn">Nomor Induk Siswa Nasional (NISN) <span class="required">*</span></label>
                                <input type="text" name="nisn" id="nisn" class="spmb-input" maxlength="10" pattern="[0-9]{10}" placeholder="10 digit angka" value="<?php echo htmlspecialchars($p['nisn']); ?>" required>
                            </div>
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="nik">Nomor Induk Kependudukan (NIK) <span class="required">*</span></label>
                                <input type="text" name="nik" id="nik" class="spmb-input" maxlength="16" pattern="[0-9]{16}" placeholder="16 digit sesuai KK/KTP" value="<?php echo htmlspecialchars($p['nik']); ?>" required>
                            </div>
                        </div>

                        <div class="spmb-form-group">
                            <label class="spmb-label" for="nama_lengkap">Nama Lengkap Siswa <span class="required">*</span></label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" class="spmb-input" placeholder="Sesuai Akta Kelahiran / Ijazah" value="<?php echo htmlspecialchars($p['nama_lengkap']); ?>" required>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="tempat_lahir">Tempat Lahir <span class="required">*</span></label>
                                <input type="text" name="tempat_lahir" id="tempat_lahir" class="spmb-input" placeholder="Kota / Kabupaten" value="<?php echo htmlspecialchars($p['tempat_lahir']); ?>" required>
                            </div>
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="tanggal_lahir">Tanggal Lahir <span class="required">*</span></label>
                                <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="spmb-input" value="<?php echo htmlspecialchars($p['tanggal_lahir']); ?>" required>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="spmb-form-group">
                                <label class="spmb-label">Jenis Kelamin <span class="required">*</span></label>
                                <div style="display:flex; gap:20px; padding-top:8px;">
                                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:700;">
                                        <input type="radio" name="jenis_kelamin" value="L" <?php echo ($p['jenis_kelamin'] === 'L') ? 'checked' : ''; ?> required> Laki-laki
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:700;">
                                        <input type="radio" name="jenis_kelamin" value="P" <?php echo ($p['jenis_kelamin'] === 'P') ? 'checked' : ''; ?> required> Perempuan
                                    </label>
                                </div>
                            </div>
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="no_hp">No. WhatsApp Calon Siswa <span class="required">*</span></label>
                                <input type="tel" name="no_hp" id="no_hp" class="spmb-input" placeholder="Contoh: 081234567890" value="<?php echo htmlspecialchars($p['no_hp']); ?>" required>
                            </div>
                        </div>

                        <div class="spmb-form-group">
                            <label class="spmb-label" for="asal_sekolah">Asal Sekolah (SMP / MTs) <span class="required">*</span></label>
                            <input type="text" name="asal_sekolah" id="asal_sekolah" class="spmb-input" placeholder="Contoh: SMPN 1 Sukapura" value="<?php echo htmlspecialchars($p['asal_sekolah']); ?>" required>
                        </div>

                        <div class="spmb-form-group">
                            <label class="spmb-label" for="alamat">Alamat Lengkap Tempat Tinggal <span class="required">*</span></label>
                            <textarea name="alamat" id="alamat" class="spmb-textarea" rows="3" placeholder="Nama Jalan, Kampung/Dusun, RT/RW, Desa/Kelurahan, Kecamatan, Kota/Kab" required><?php echo htmlspecialchars($p['alamat']); ?></textarea>
                        </div>
                    </div>

                    <!-- BAGIAN 3: DATA ORANG TUA / WALI -->
                    <div class="edit-sec-card">
                        <h3 class="edit-sec-title">
                            <i class="ph-bold ph-users-three"></i> 3. Data Orang Tua / Wali
                        </h3>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="nama_ayah">Nama Ayah Kandung / Wali <span class="required">*</span></label>
                                <input type="text" name="nama_ayah" id="nama_ayah" class="spmb-input" placeholder="Nama lengkap ayah" value="<?php echo htmlspecialchars($p['nama_ayah']); ?>" required>
                            </div>
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="nama_ibu">Nama Ibu Kandung <span class="required">*</span></label>
                                <input type="text" name="nama_ibu" id="nama_ibu" class="spmb-input" placeholder="Nama lengkap ibu" value="<?php echo htmlspecialchars($p['nama_ibu']); ?>" required>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="no_hp_ortu">No. WhatsApp Orang Tua / Wali <span class="required">*</span></label>
                                <input type="tel" name="no_hp_ortu" id="no_hp_ortu" class="spmb-input" placeholder="Contoh: 081234567890" value="<?php echo htmlspecialchars($p['no_hp_ortu']); ?>" required>
                            </div>
                            <div class="spmb-form-group">
                                <label class="spmb-label" for="pekerjaan_ortu">Pekerjaan Utama</label>
                                <select name="pekerjaan_ortu" id="pekerjaan_ortu" class="spmb-select" onchange="togglePekerjaanLain(this.value)">
                                    <?php
                                    $jobs = ['PNS / TNI / POLRI', 'Karyawan Swasta', 'Wiraswasta / Pedagang', 'Petani / Peternak', 'Buruh Harian Lepas', 'Guru / Dosen', 'Ibu Rumah Tangga', 'Tidak Bekerja'];
                                    $isJobStandard = in_array($p['pekerjaan_ortu'], $jobs);
                                    ?>
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    <?php foreach ($jobs as $j): ?>
                                        <option value="<?php echo $j; ?>" <?php echo ($p['pekerjaan_ortu'] === $j) ? 'selected' : ''; ?>><?php echo $j; ?></option>
                                    <?php endforeach; ?>
                                    <option value="Lainnya" <?php echo (!$isJobStandard && !empty($p['pekerjaan_ortu'])) ? 'selected' : ''; ?>>Lainnya (Ketik Sendiri)</option>
                                </select>
                                <div id="div_pekerjaan_lain" style="margin-top:8px; <?php echo (!$isJobStandard && !empty($p['pekerjaan_ortu'])) ? '' : 'display:none;'; ?>">
                                    <input type="text" name="pekerjaan_ortu_lainnya" id="pekerjaan_ortu_lainnya" class="spmb-input" placeholder="Ketik jenis pekerjaan..." value="<?php echo (!$isJobStandard) ? htmlspecialchars($p['pekerjaan_ortu'] ?? '') : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="spmb-form-group">
                            <label class="spmb-label" for="penghasilan_ortu">Penghasilan Rata-rata per Bulan</label>
                            <select name="penghasilan_ortu" id="penghasilan_ortu" class="spmb-select">
                                <option value="">-- Pilih Rentang Penghasilan --</option>
                                <?php
                                $incomes = [
                                    '< Rp 1.000.000',
                                    'Rp 1.000.000 - Rp 2.500.000',
                                    'Rp 2.500.000 - Rp 5.000.000',
                                    'Rp 5.000.000 - Rp 10.000.000',
                                    '> Rp 10.000.000'
                                ];
                                foreach ($incomes as $inc):
                                ?>
                                    <option value="<?php echo $inc; ?>" <?php echo ($p['penghasilan_ortu'] === $inc) ? 'selected' : ''; ?>><?php echo $inc; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- BAGIAN 4: BERKAS & DOKUMEN -->
                    <div class="edit-sec-card">
                        <h3 class="edit-sec-title">
                            <i class="ph-bold ph-files"></i> 4. Unggah Ulang / Perbaikan Berkas Dokumen
                        </h3>
                        <div style="background:#F1F5F9; border:1px solid #CBD5E1; border-radius:8px; padding:12px 14px; margin-bottom:20px; font-size:0.88rem; color:#475569;">
                            <i class="ph-bold ph-info" style="color:var(--nb-navy-dark);"></i>
                            <strong>Petunjuk:</strong> Jika berkas sebelumnya sudah benar dan tidak diminta diperbaiki, <strong>kosongkan saja</strong> kolom unggah file. Unggah file baru hanya untuk dokumen yang salah atau buram. Format: JPG, PNG, WEBP, atau PDF (maks. 3MB).
                        </div>

                        <!-- 1. Pas Foto -->
                        <div class="doc-preview-item">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:6px;">
                                <label class="spmb-label" style="margin-bottom:0;" for="foto">Pas Foto Siswa (3x4)</label>
                                <?php if (!empty($p['foto'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($p['foto']); ?>" target="_blank" style="font-size:0.8rem; font-weight:700; color:var(--nb-navy); display:inline-flex; align-items:center; gap:4px;">
                                        <i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Lihat Pas Foto Terunggah
                                    </a>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="foto" id="foto" class="spmb-input" accept=".jpg,.jpeg,.png,.webp">
                            <div class="spmb-input-helper">Kosongkan jika tidak ingin mengganti pas foto.</div>
                        </div>

                        <!-- 2. Kartu Keluarga -->
                        <div class="doc-preview-item">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:6px;">
                                <label class="spmb-label" style="margin-bottom:0;" for="berkas_kk">Kartu Keluarga (KK)</label>
                                <?php if (!empty($p['berkas_kk'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($p['berkas_kk']); ?>" target="_blank" style="font-size:0.8rem; font-weight:700; color:var(--nb-navy); display:inline-flex; align-items:center; gap:4px;">
                                        <i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Lihat Berkas KK Saat Ini
                                    </a>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="berkas_kk" id="berkas_kk" class="spmb-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                            <div class="spmb-input-helper">Unggah file baru jika diminta foto KK yang lebih jelas.</div>
                        </div>

                        <!-- 3. Akta Kelahiran -->
                        <div class="doc-preview-item">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:6px;">
                                <label class="spmb-label" style="margin-bottom:0;" for="berkas_akta">Akta Kelahiran</label>
                                <?php if (!empty($p['berkas_akta'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($p['berkas_akta']); ?>" target="_blank" style="font-size:0.8rem; font-weight:700; color:var(--nb-navy); display:inline-flex; align-items:center; gap:4px;">
                                        <i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Lihat Akta Saat Ini
                                    </a>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="berkas_akta" id="berkas_akta" class="spmb-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        </div>

                        <!-- 4. Ijazah / SKL -->
                        <div class="doc-preview-item">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:6px;">
                                <label class="spmb-label" style="margin-bottom:0;" for="berkas_ijazah">Ijazah / Surat Keterangan Lulus (SKL)</label>
                                <?php if (!empty($p['berkas_ijazah'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($p['berkas_ijazah']); ?>" target="_blank" style="font-size:0.8rem; font-weight:700; color:var(--nb-navy); display:inline-flex; align-items:center; gap:4px;">
                                        <i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Lihat Berkas Ijazah Saat Ini
                                    </a>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="berkas_ijazah" id="berkas_ijazah" class="spmb-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        </div>

                        <!-- 5. KTP Ortu -->
                        <div class="doc-preview-item">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:6px;">
                                <label class="spmb-label" style="margin-bottom:0;" for="berkas_ktp_ortu">KTP Orang Tua / Wali</label>
                                <?php if (!empty($p['berkas_ktp_ortu'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($p['berkas_ktp_ortu']); ?>" target="_blank" style="font-size:0.8rem; font-weight:700; color:var(--nb-navy); display:inline-flex; align-items:center; gap:4px;">
                                        <i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Lihat KTP Ortu Saat Ini
                                    </a>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="berkas_ktp_ortu" id="berkas_ktp_ortu" class="spmb-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        </div>

                        <!-- 6. KIP (Opsional) -->
                        <div class="doc-preview-item">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:6px;">
                                <label class="spmb-label" style="margin-bottom:0;" for="berkas_kip">Kartu Indonesia Pintar (KIP / PIP) <span style="font-weight:400; font-size:0.85rem; color:#666;">(Opsional)</span></label>
                                <?php if (!empty($p['berkas_kip'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($p['berkas_kip']); ?>" target="_blank" style="font-size:0.8rem; font-weight:700; color:var(--nb-navy); display:inline-flex; align-items:center; gap:4px;">
                                        <i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Lihat KIP Saat Ini
                                    </a>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="berkas_kip" id="berkas_kip" class="spmb-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        </div>

                        <!-- 7. Sertifikat Prestasi -->
                        <div class="doc-preview-item" id="div_berkas_prestasi" style="<?php echo ($p['jalur'] === 'Prestasi' || !empty($p['berkas_prestasi'])) ? '' : 'display:none;'; ?>">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:6px;">
                                <label class="spmb-label" style="margin-bottom:0;" for="berkas_prestasi">Sertifikat / Piagam Kejuaraan (Jalur Prestasi)</label>
                                <?php if (!empty($p['berkas_prestasi'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($p['berkas_prestasi']); ?>" target="_blank" style="font-size:0.8rem; font-weight:700; color:var(--nb-navy); display:inline-flex; align-items:center; gap:4px;">
                                        <i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Lihat Piagam Prestasi
                                    </a>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="berkas_prestasi" id="berkas_prestasi" class="spmb-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        </div>
                    </div>

                    <!-- TOMBOL SIMPAN & PERBAIKI -->
                    <div style="background:var(--nb-white); border:var(--border-thick); border-radius:var(--radius-card); box-shadow:var(--shadow-lg); padding:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                        <a href="cek-status.php?no=<?php echo urlencode($p['no_pendaftaran']); ?>" class="spmb-btn spmb-btn-secondary">
                            <i class="ph-bold ph-arrow-left"></i> Batal &amp; Kembali
                        </a>
                        <button type="submit" class="spmb-btn spmb-btn-primary" style="padding:14px 28px; font-size:1.05rem; background:#F59E0B; color:#0F172A;" onclick="return confirm('Pastikan data dan berkas perbaikan sudah benar sebelum diajukan kembali ke panitia. Lanjutkan?');">
                            <i class="ph-bold ph-floppy-disk"></i> Simpan &amp; Ajukan Ulang Verifikasi
                        </button>
                    </div>

                </form>

                <script>
                function togglePekerjaanLain(val) {
                    const div = document.getElementById('div_pekerjaan_lain');
                    if (div) {
                        div.style.display = (val === 'Lainnya') ? 'block' : 'none';
                        if (val === 'Lainnya') {
                            document.getElementById('pekerjaan_ortu_lainnya').focus();
                        }
                    }
                }

                function handleJalurChange(jalur) {
                    const divPres = document.getElementById('div_berkas_prestasi');
                    if (divPres) {
                        divPres.style.display = (jalur === 'Prestasi') ? 'block' : 'none';
                    }
                }
                </script>

            <?php endif; ?>

        </div>
    </main>

    <?php include '../components/footer.php'; ?>
</body>
</html>
