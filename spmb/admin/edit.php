<?php
// spmb/admin/edit.php
// Edit Data Calon Peserta Didik oleh Admin SPMB SMKS SUKAPURA
$adminPageTitle = 'Edit Data Pendaftar';
$adminPageHeading = 'Edit Data Calon Siswa';

require_once __DIR__ . '/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<div class='spmb-alert spmb-alert-danger'>ID Pendaftar tidak valid.</div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `id` = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    echo "<div class='spmb-alert spmb-alert-danger'>Data calon siswa tidak ditemukan.</div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$success = '';
$error   = '';

// Handle Form Submit Update Data oleh Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_edit') {
    $nisn             = sanitize_input($_POST['nisn'] ?? '');
    $nik              = sanitize_input($_POST['nik'] ?? '');
    $nama_lengkap     = sanitize_input($_POST['nama_lengkap'] ?? '');
    $jenis_kelamin    = sanitize_input($_POST['jenis_kelamin'] ?? '');
    $tempat_lahir     = sanitize_input($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir    = sanitize_input($_POST['tanggal_lahir'] ?? '');
    $agama            = sanitize_input($_POST['agama'] ?? 'Islam');
    $no_hp            = sanitize_input($_POST['no_hp'] ?? '');
    $alamat           = sanitize_input($_POST['alamat'] ?? '');
    $asal_sekolah     = sanitize_input($_POST['asal_sekolah'] ?? '');
    $jurusan_1        = sanitize_input($_POST['jurusan_1'] ?? '');
    $jurusan_2        = sanitize_input($_POST['jurusan_2'] ?? '');
    $jurusan_diterima = sanitize_input($_POST['jurusan_diterima'] ?? '');
    $jalur            = sanitize_input($_POST['jalur'] ?? 'Reguler');

    $nama_ayah        = sanitize_input($_POST['nama_ayah'] ?? '');
    $nama_ibu         = sanitize_input($_POST['nama_ibu'] ?? '');
    $pekerjaan_ortu   = sanitize_input($_POST['pekerjaan_ortu'] ?? '');
    $no_hp_ortu       = sanitize_input($_POST['no_hp_ortu'] ?? '');
    $penghasilan_ortu = sanitize_input($_POST['penghasilan_ortu'] ?? '');

    // Validasi Kolom Wajib
    if (empty($nisn) || empty($nik) || empty($nama_lengkap) || empty($jenis_kelamin) || 
        empty($tempat_lahir) || empty($tanggal_lahir) || empty($no_hp) || empty($alamat) || 
        empty($asal_sekolah) || empty($jurusan_1)) {
        $error = "Harap lengkapi semua kolom bertanda bintang (*) yang wajib diisi.";
    } else {
        // Cek apakah NISN atau NIK sudah digunakan oleh pendaftar lain
        $cekDup = $pdo->prepare("SELECT `id` FROM `spmb_pendaftar` WHERE (`nisn` = ? OR `nik` = ?) AND `id` != ?");
        $cekDup->execute([$nisn, $nik, $id]);
        if ($cekDup->fetch()) {
            $error = "NISN atau NIK tersebut telah digunakan oleh pendaftar lain di sistem.";
        } else {
            // Upload helper untuk admin
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $uploadBerkas = function($fieldKey, $prefix, $oldFile, $allowed = ['jpg','jpeg','png','webp','pdf'], $maxBytes = 3145728) use (&$error, $uploadDir) {
                if (!isset($_FILES[$fieldKey]) || $_FILES[$fieldKey]['error'] === UPLOAD_ERR_NO_FILE) {
                    return $oldFile;
                }
                if ($_FILES[$fieldKey]['error'] !== UPLOAD_ERR_OK) {
                    $error = "Gagal mengunggah berkas $fieldKey (Error code: " . $_FILES[$fieldKey]['error'] . ").";
                    return $oldFile;
                }
                $fileTmp  = $_FILES[$fieldKey]['tmp_name'];
                $fileName = $_FILES[$fieldKey]['name'];
                $fileSize = $_FILES[$fieldKey]['size'];
                $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    $error = "Format berkas $fieldKey tidak sesuai. Hanya diperbolehkan format " . strtoupper(implode(', ', $allowed)) . ".";
                    return $oldFile;
                }
                if ($fileSize > $maxBytes) {
                    $maxMB = round($maxBytes / (1024 * 1024));
                    $error = "Ukuran berkas $fieldKey melebihi batas maksimal ({$maxMB} MB).";
                    return $oldFile;
                }
                $newFileName = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    return $newFileName;
                }
                return $oldFile;
            };

            $foto_path          = $uploadBerkas('foto', 'foto', $p['foto'], ['jpg','jpeg','png','webp'], 2097152);
            $berkas_kk_path     = empty($error) ? $uploadBerkas('berkas_kk', 'kk', $p['berkas_kk']) : $p['berkas_kk'];
            $berkas_akta_path   = empty($error) ? $uploadBerkas('berkas_akta', 'akta', $p['berkas_akta']) : $p['berkas_akta'];
            $berkas_ijazah_path = empty($error) ? $uploadBerkas('berkas_ijazah', 'ijazah', $p['berkas_ijazah']) : $p['berkas_ijazah'];
            $berkas_ktp_path    = empty($error) ? $uploadBerkas('berkas_ktp_ortu', 'ktp', $p['berkas_ktp_ortu']) : $p['berkas_ktp_ortu'];
            $berkas_kip_path    = empty($error) ? $uploadBerkas('berkas_kip', 'kip', $p['berkas_kip']) : $p['berkas_kip'];
            $berkas_pres_path   = empty($error) ? $uploadBerkas('berkas_prestasi', 'prestasi', $p['berkas_prestasi']) : $p['berkas_prestasi'];

            if (empty($error)) {
                $jurusanDiterimaVal = !empty($jurusan_diterima) ? $jurusan_diterima : null;

                $upd = $pdo->prepare("UPDATE `spmb_pendaftar` SET 
                    `nisn` = ?, `nik` = ?, `nama_lengkap` = ?, `jenis_kelamin` = ?,
                    `tempat_lahir` = ?, `tanggal_lahir` = ?, `agama` = ?, `no_hp` = ?, `alamat` = ?,
                    `asal_sekolah` = ?, `jurusan_1` = ?, `jurusan_2` = ?, `jurusan_diterima` = ?, `jalur` = ?,
                    `nama_ayah` = ?, `nama_ibu` = ?, `pekerjaan_ortu` = ?, `no_hp_ortu` = ?, `penghasilan_ortu` = ?,
                    `foto` = ?, `berkas_kk` = ?, `berkas_akta` = ?, `berkas_ijazah` = ?, `berkas_ktp_ortu` = ?,
                    `berkas_kip` = ?, `berkas_prestasi` = ?
                    WHERE `id` = ?");

                $upd->execute([
                    $nisn, $nik, $nama_lengkap, $jenis_kelamin,
                    $tempat_lahir, $tanggal_lahir, $agama, $no_hp, $alamat,
                    $asal_sekolah, $jurusan_1, $jurusan_2, $jurusanDiterimaVal, $jalur,
                    $nama_ayah, $nama_ibu, $pekerjaan_ortu, $no_hp_ortu, $penghasilan_ortu,
                    $foto_path, $berkas_kk_path, $berkas_akta_path, $berkas_ijazah_path, $berkas_ktp_path,
                    $berkas_kip_path, $berkas_pres_path,
                    $id
                ]);

                $success = "Data pendaftar <strong>" . htmlspecialchars($nama_lengkap) . "</strong> berhasil diperbarui oleh Administrator!";

                // Refresh data pendaftar
                $stmt->execute([$id]);
                $p = $stmt->fetch();
            }
        }
    }
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; gap:10px; align-items:center;">
        <a href="detail.php?id=<?php echo $p['id']; ?>" class="adm-btn adm-btn-secondary">
            <i class="ph-bold ph-arrow-left"></i> Kembali ke Detail
        </a>
        <a href="pendaftar.php" class="adm-btn adm-btn-secondary">
            <i class="ph-bold ph-list"></i> Semua Pendaftar
        </a>
    </div>
    <div>
        <span class="spmb-badge" style="background:var(--adm-yellow-light); border:var(--border-thin); font-weight:800; font-size:0.85rem;">
            No. Registrasi: <?php echo htmlspecialchars($p['no_pendaftaran']); ?>
        </span>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="spmb-alert spmb-alert-danger" style="margin-bottom:20px;">
        <i class="ph-bold ph-warning-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $error; ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="spmb-alert spmb-alert-success" style="margin-bottom:20px;">
        <i class="ph-bold ph-check-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $success; ?></div>
    </div>
<?php endif; ?>

<form action="edit.php?id=<?php echo $p['id']; ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="admin_edit">

    <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px;">

        <!-- KOLOM KIRI: DATA SISWA & ORANG TUA -->
        <div>
            <!-- BAGIAN 1: IDENTITAS UTAMA SISWA -->
            <div class="adm-card" style="margin-bottom:24px;">
                <div class="adm-card-header">
                    <h3 class="adm-card-title">
                        <i class="ph-bold ph-user-circle"></i> 1. Identitas Calon Siswa
                    </h3>
                </div>
                <div class="adm-card-body">
                    <div class="spmb-form-row">
                        <div class="spmb-form-group">
                            <label class="spmb-label">Nomor Induk Siswa Nasional (NISN) <span class="required">*</span></label>
                            <input type="text" name="nisn" class="spmb-input" maxlength="10" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($p['nisn']); ?>" required>
                        </div>
                        <div class="spmb-form-group">
                            <label class="spmb-label">Nomor Induk Kependudukan (NIK) <span class="required">*</span></label>
                            <input type="text" name="nik" class="spmb-input" maxlength="16" pattern="[0-9]{16}" value="<?php echo htmlspecialchars($p['nik']); ?>" required>
                        </div>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Nama Lengkap Siswa <span class="required">*</span></label>
                        <input type="text" name="nama_lengkap" class="spmb-input" value="<?php echo htmlspecialchars($p['nama_lengkap']); ?>" required style="font-weight:700;">
                    </div>

                    <div class="spmb-form-row">
                        <div class="spmb-form-group">
                            <label class="spmb-label">Jenis Kelamin <span class="required">*</span></label>
                            <select name="jenis_kelamin" class="spmb-select" required>
                                <option value="L" <?php echo ($p['jenis_kelamin'] === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo ($p['jenis_kelamin'] === 'P') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="spmb-form-group">
                            <label class="spmb-label">Agama</label>
                            <select name="agama" class="spmb-select">
                                <?php 
                                $listAgama = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
                                foreach ($listAgama as $ag): ?>
                                    <option value="<?php echo $ag; ?>" <?php echo (($p['agama'] ?? '') === $ag) ? 'selected' : ''; ?>><?php echo $ag; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="spmb-form-row">
                        <div class="spmb-form-group">
                            <label class="spmb-label">Tempat Lahir <span class="required">*</span></label>
                            <input type="text" name="tempat_lahir" class="spmb-input" value="<?php echo htmlspecialchars($p['tempat_lahir']); ?>" required>
                        </div>
                        <div class="spmb-form-group">
                            <label class="spmb-label">Tanggal Lahir <span class="required">*</span></label>
                            <input type="date" name="tanggal_lahir" class="spmb-input" value="<?php echo htmlspecialchars($p['tanggal_lahir']); ?>" required>
                        </div>
                    </div>

                    <div class="spmb-form-row">
                        <div class="spmb-form-group">
                            <label class="spmb-label">Nomor WhatsApp Siswa <span class="required">*</span></label>
                            <input type="text" name="no_hp" class="spmb-input" value="<?php echo htmlspecialchars($p['no_hp']); ?>" required>
                        </div>
                        <div class="spmb-form-group">
                            <label class="spmb-label">Asal SMP / MTs <span class="required">*</span></label>
                            <input type="text" name="asal_sekolah" class="spmb-input" value="<?php echo htmlspecialchars($p['asal_sekolah']); ?>" required>
                        </div>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Alamat Lengkap Tempat Tinggal <span class="required">*</span></label>
                        <textarea name="alamat" class="spmb-input" rows="3" required><?php echo htmlspecialchars($p['alamat']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: DATA ORANG TUA / WALI -->
            <div class="adm-card">
                <div class="adm-card-header">
                    <h3 class="adm-card-title">
                        <i class="ph-bold ph-users"></i> 2. Data Orang Tua / Wali
                    </h3>
                </div>
                <div class="adm-card-body">
                    <div class="spmb-form-row">
                        <div class="spmb-form-group">
                            <label class="spmb-label">Nama Ayah Kandung <span class="required">*</span></label>
                            <input type="text" name="nama_ayah" class="spmb-input" value="<?php echo htmlspecialchars($p['nama_ayah']); ?>" required>
                        </div>
                        <div class="spmb-form-group">
                            <label class="spmb-label">Nama Ibu Kandung</label>
                            <input type="text" name="nama_ibu" class="spmb-input" value="<?php echo htmlspecialchars($p['nama_ibu']); ?>">
                        </div>
                    </div>

                    <div class="spmb-form-row">
                        <div class="spmb-form-group">
                            <label class="spmb-label">Pekerjaan Orang Tua</label>
                            <input type="text" name="pekerjaan_ortu" class="spmb-input" value="<?php echo htmlspecialchars($p['pekerjaan_ortu'] ?? ''); ?>">
                        </div>
                        <div class="spmb-form-group">
                            <label class="spmb-label">Nomor WhatsApp Orang Tua</label>
                            <input type="text" name="no_hp_ortu" class="spmb-input" value="<?php echo htmlspecialchars($p['no_hp_ortu'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Penghasilan Orang Tua</label>
                        <select name="penghasilan_ortu" class="spmb-select">
                            <?php 
                            $listGaji = ['< Rp 1.000.000', 'Rp 1.000.000 - Rp 2.500.000', 'Rp 2.500.000 - Rp 5.000.000', '> Rp 5.000.000'];
                            foreach ($listGaji as $gaji): ?>
                                <option value="<?php echo $gaji; ?>" <?php echo (($p['penghasilan_ortu'] ?? '') === $gaji) ? 'selected' : ''; ?>><?php echo $gaji; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: PILIHAN JURUSAN & UPLOAD BERKAS -->
        <div>
            <!-- PILIHAN JURUSAN & JALUR -->
            <div class="adm-card" style="margin-bottom:24px;">
                <div class="adm-card-header">
                    <h3 class="adm-card-title">
                        <i class="ph-bold ph-graduation-cap"></i> Pilihan Jurusan &amp; Jalur
                    </h3>
                </div>
                <div class="adm-card-body">
                    <div class="spmb-form-group">
                        <label class="spmb-label">Jalur Pendaftaran <span class="required">*</span></label>
                        <select name="jalur" class="spmb-select" required>
                            <?php foreach ($DAFTAR_JALUR as $kJalur => $vJalur): ?>
                                <option value="<?php echo $kJalur; ?>" <?php echo ($p['jalur'] === $kJalur) ? 'selected' : ''; ?>>
                                    <?php echo $vJalur; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Jurusan Pilihan 1 (Utama) <span class="required">*</span></label>
                        <select name="jurusan_1" class="spmb-select" required>
                            <?php foreach ($DAFTAR_JURUSAN as $kode => $j): 
                                $infoK = get_jurusan_info_kuota($pdo, $kode, $p['id']);
                            ?>
                                <option value="<?php echo $kode; ?>" <?php echo ($p['jurusan_1'] === $kode) ? 'selected' : ''; ?>>
                                    <?php echo $kode; ?> - <?php echo $j['nama']; ?> (Sisa: <?php echo $infoK['sisa']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Jurusan Pilihan 2 (Cadangan)</label>
                        <select name="jurusan_2" class="spmb-select">
                            <option value="">-- Tidak Memilih Pilihan 2 --</option>
                            <?php foreach ($DAFTAR_JURUSAN as $kode => $j): 
                                $infoK = get_jurusan_info_kuota($pdo, $kode, $p['id']);
                            ?>
                                <option value="<?php echo $kode; ?>" <?php echo ($p['jurusan_2'] === $kode) ? 'selected' : ''; ?>>
                                    <?php echo $kode; ?> - <?php echo $j['nama']; ?> (Sisa: <?php echo $infoK['sisa']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($p['status'] === 'Diterima'): ?>
                        <div class="spmb-form-group">
                            <label class="spmb-label">Jurusan Diterima Resmi</label>
                            <select name="jurusan_diterima" class="spmb-select" style="font-weight:800;">
                                <option value="">-- Sesuai Pilihan 1 --</option>
                                <?php foreach ($DAFTAR_JURUSAN as $kode => $j): ?>
                                    <option value="<?php echo $kode; ?>" <?php echo ($p['jurusan_diterima'] === $kode) ? 'selected' : ''; ?>>
                                        <?php echo $kode; ?> - <?php echo $j['nama']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- GANTI DOKUMEN & FOTO BERKAS -->
            <div class="adm-card" style="margin-bottom:24px;">
                <div class="adm-card-header">
                    <h3 class="adm-card-title">
                        <i class="ph-bold ph-files"></i> Pengelolaan Berkas Siswa
                    </h3>
                </div>
                <div class="adm-card-body" style="font-size:0.88rem;">
                    
                    <!-- FOTO RESMI -->
                    <div style="margin-bottom:14px; padding-bottom:12px; border-bottom:1px dashed #E2E8F0;">
                        <label class="spmb-label">Pas Foto Calon Siswa</label>
                        <?php if (!empty($p['foto']) && file_exists(__DIR__ . '/../uploads/' . $p['foto'])): ?>
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                <img src="../uploads/<?php echo htmlspecialchars($p['foto']); ?>" alt="Foto" style="width:45px; height:60px; object-fit:cover; border-radius:4px; border:1px solid #000;">
                                <a href="../uploads/<?php echo htmlspecialchars($p['foto']); ?>" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">Lihat Foto Saat Ini</a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" class="spmb-input" style="padding:6px;">
                    </div>

                    <!-- KARTU KELUARGA -->
                    <div style="margin-bottom:14px; padding-bottom:12px; border-bottom:1px dashed #E2E8F0;">
                        <label class="spmb-label">Scan/Foto Kartu Keluarga (KK)</label>
                        <?php if (!empty($p['berkas_kk']) && file_exists(__DIR__ . '/../uploads/' . $p['berkas_kk'])): ?>
                            <div style="margin-bottom:6px;">
                                <a href="../uploads/<?php echo htmlspecialchars($p['berkas_kk']); ?>" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">
                                    <i class="ph-bold ph-file-pdf"></i> Lihat Berkas KK Saat Ini
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="berkas_kk" accept=".jpg,.jpeg,.png,.webp,.pdf" class="spmb-input" style="padding:6px;">
                    </div>

                    <!-- AKTA KELAHIRAN -->
                    <div style="margin-bottom:14px; padding-bottom:12px; border-bottom:1px dashed #E2E8F0;">
                        <label class="spmb-label">Scan/Foto Akta Kelahiran</label>
                        <?php if (!empty($p['berkas_akta']) && file_exists(__DIR__ . '/../uploads/' . $p['berkas_akta'])): ?>
                            <div style="margin-bottom:6px;">
                                <a href="../uploads/<?php echo htmlspecialchars($p['berkas_akta']); ?>" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">
                                    <i class="ph-bold ph-file-pdf"></i> Lihat Akta Saat Ini
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="berkas_akta" accept=".jpg,.jpeg,.png,.webp,.pdf" class="spmb-input" style="padding:6px;">
                    </div>

                    <!-- IJAZAH / SKL -->
                    <div style="margin-bottom:14px; padding-bottom:12px; border-bottom:1px dashed #E2E8F0;">
                        <label class="spmb-label">Scan/Foto Ijazah / SKL</label>
                        <?php if (!empty($p['berkas_ijazah']) && file_exists(__DIR__ . '/../uploads/' . $p['berkas_ijazah'])): ?>
                            <div style="margin-bottom:6px;">
                                <a href="../uploads/<?php echo htmlspecialchars($p['berkas_ijazah']); ?>" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">
                                    <i class="ph-bold ph-file-pdf"></i> Lihat Ijazah Saat Ini
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="berkas_ijazah" accept=".jpg,.jpeg,.png,.webp,.pdf" class="spmb-input" style="padding:6px;">
                    </div>

                    <!-- KTP ORTU -->
                    <div style="margin-bottom:14px; padding-bottom:12px; border-bottom:1px dashed #E2E8F0;">
                        <label class="spmb-label">Scan/Foto KTP Orang Tua</label>
                        <?php if (!empty($p['berkas_ktp_ortu']) && file_exists(__DIR__ . '/../uploads/' . $p['berkas_ktp_ortu'])): ?>
                            <div style="margin-bottom:6px;">
                                <a href="../uploads/<?php echo htmlspecialchars($p['berkas_ktp_ortu']); ?>" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">
                                    <i class="ph-bold ph-file-pdf"></i> Lihat KTP Ortu Saat Ini
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="berkas_ktp_ortu" accept=".jpg,.jpeg,.png,.webp,.pdf" class="spmb-input" style="padding:6px;">
                    </div>

                    <!-- KIP / PRESTASI -->
                    <div style="margin-bottom:10px;">
                        <label class="spmb-label">Sertifikat Prestasi (Jalur Prestasi)</label>
                        <?php if (!empty($p['berkas_prestasi']) && file_exists(__DIR__ . '/../uploads/' . $p['berkas_prestasi'])): ?>
                            <div style="margin-bottom:6px;">
                                <a href="../uploads/<?php echo htmlspecialchars($p['berkas_prestasi']); ?>" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">
                                    <i class="ph-bold ph-file-pdf"></i> Lihat Piagam Prestasi Saat Ini
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="berkas_prestasi" accept=".jpg,.jpeg,.png,.webp,.pdf" class="spmb-input" style="padding:6px;">
                    </div>

                </div>
            </div>

            <button type="submit" class="adm-btn adm-btn-primary" style="width:100%; padding:14px; font-size:1.05rem;">
                <i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan Data
            </button>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>
