<?php
// spmb/admin/tambah.php
// Tambah Pendaftar Manual oleh Admin SPMB
$adminPageTitle = 'Tambah Pendaftar';
$adminPageHeading = 'Input Pendaftar Baru (Manual / Offline)';

require_once __DIR__ . '/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $no_hp_ortu      = sanitize_input($_POST['no_hp_ortu'] ?? '');
    $status          = sanitize_input($_POST['status'] ?? 'Menunggu Verifikasi');

    if (empty($nisn) || empty($nik) || empty($nama_lengkap) || empty($jurusan_1)) {
        $error = "NISN, NIK, Nama Lengkap, dan Pilihan Jurusan 1 wajib diisi.";
    } else {
        $check = $pdo->prepare("SELECT COUNT(*) FROM `spmb_pendaftar` WHERE `nisn` = ?");
        $check->execute([$nisn]);
        if ($check->fetchColumn() > 0) {
            $error = "NISN $nisn sudah pernah terdaftar di sistem.";
        } else {
            $no_pendaftaran = generate_no_pendaftaran($pdo);

            $ins = $pdo->prepare("INSERT INTO `spmb_pendaftar` (
                `no_pendaftaran`, `nisn`, `nik`, `nama_lengkap`, `jenis_kelamin`,
                `tempat_lahir`, `tanggal_lahir`, `agama`, `no_hp`, `alamat`,
                `asal_sekolah`, `jurusan_1`, `jurusan_2`, `jalur`, `nama_ayah`,
                `nama_ibu`, `pekerjaan_ortu`, `no_hp_ortu`, `status`, `catatan_admin`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Diinput langsung oleh panitia di loket SPMB.')");

            $ins->execute([
                $no_pendaftaran, $nisn, $nik, $nama_lengkap, $jenis_kelamin ?: 'L',
                $tempat_lahir ?: 'Tasikmalaya', $tanggal_lahir ?: date('Y-m-d'), $agama,
                $no_hp ?: '-', $alamat ?: '-', $asal_sekolah ?: '-', $jurusan_1,
                $jurusan_2 ?: null, $jalur, $nama_ayah ?: '-', $nama_ibu ?: '-',
                $pekerjaan_ortu ?: '-', $no_hp_ortu ?: '-', $status
            ]);

            header("Location: pendaftar.php?msg=added");
            exit;
        }
    }
}
?>

<div style="max-width:900px; margin:0 auto;">
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title">
                <i class="ph-bold ph-user-plus"></i> Formulir Input Panitia Loket SPMB
            </h3>
            <a href="pendaftar.php" class="adm-btn adm-btn-sm adm-btn-secondary">
                <i class="ph-bold ph-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="adm-card-body">

            <?php if (!empty($error)): ?>
                <div class="spmb-alert spmb-alert-danger">
                    <i class="ph-bold ph-warning-circle" style="font-size:1.4rem;"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form action="tambah.php" method="POST">
                
                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">Jurusan Pilihan 1 <span class="required">*</span></label>
                        <select name="jurusan_1" class="spmb-select" required>
                            <option value="">-- Pilih Jurusan Utama --</option>
                            <?php foreach ($DAFTAR_JURUSAN as $k => $j): ?>
                                <option value="<?php echo $k; ?>"><?php echo $k; ?> - <?php echo $j['nama']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Jurusan Pilihan 2</label>
                        <select name="jurusan_2" class="spmb-select">
                            <option value="">-- Pilihan Alternatif (Opsional) --</option>
                            <?php foreach ($DAFTAR_JURUSAN as $k => $j): ?>
                                <option value="<?php echo $k; ?>"><?php echo $k; ?> - <?php echo $j['nama']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">Jalur Pendaftaran</label>
                        <select name="jalur" class="spmb-select">
                            <?php foreach ($DAFTAR_JALUR as $k => $v): ?>
                                <option value="<?php echo $k; ?>">Jalur <?php echo $k; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Status Awal</label>
                        <select name="status" class="spmb-select">
                            <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                            <option value="Diterima">Langsung Diterima</option>
                            <option value="Cadangan">Cadangan</option>
                        </select>
                    </div>
                </div>

                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">NISN Siswa <span class="required">*</span></label>
                        <input type="text" name="nisn" class="spmb-input" placeholder="10 Digit NISN" maxlength="10" required>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">NIK Siswa <span class="required">*</span></label>
                        <input type="text" name="nik" class="spmb-input" placeholder="16 Digit NIK" maxlength="16" required>
                    </div>
                </div>

                <div class="spmb-form-group">
                    <label class="spmb-label">Nama Lengkap Siswa <span class="required">*</span></label>
                    <input type="text" name="nama_lengkap" class="spmb-input" placeholder="Nama calon siswa" required>
                </div>

                <div class="spmb-form-row-3">
                    <div class="spmb-form-group">
                        <label class="spmb-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="spmb-select">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="spmb-input" placeholder="Kota Lahir">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="spmb-input">
                    </div>
                </div>

                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">Asal SMP / MTs</label>
                        <input type="text" name="asal_sekolah" class="spmb-input" placeholder="Nama SMP/MTs">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">No. WhatsApp Calon Siswa</label>
                        <input type="tel" name="no_hp" class="spmb-input" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="spmb-form-group">
                    <label class="spmb-label">Alamat Lengkap</label>
                    <textarea name="alamat" class="spmb-textarea" placeholder="Alamat rumah calon siswa..."></textarea>
                </div>

                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">Nama Ayah</label>
                        <input type="text" name="nama_ayah" class="spmb-input" placeholder="Nama Ayah">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Nama Ibu</label>
                        <input type="text" name="nama_ibu" class="spmb-input" placeholder="Nama Ibu">
                    </div>
                </div>

                <div class="spmb-form-row">
                    <div class="spmb-form-group">
                        <label class="spmb-label">No. WhatsApp Orang Tua</label>
                        <input type="tel" name="no_hp_ortu" class="spmb-input" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Pekerjaan Orang Tua</label>
                        <input type="text" name="pekerjaan_ortu" class="spmb-input" placeholder="Wiraswasta / Karyawan / dll">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                    <a href="pendaftar.php" class="adm-btn adm-btn-secondary">Batal</a>
                    <button type="submit" class="adm-btn adm-btn-primary" style="padding:14px 28px;">
                        <i class="ph-bold ph-check"></i> Simpan Pendaftar
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
