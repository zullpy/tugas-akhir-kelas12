<?php
// setting/cloudinary.php
// Pengaturan Penyimpanan Foto Cloudinary CDN SMKS SUKAPURA

require_once __DIR__ . '/../spmb/config.php';
check_admin_login(true, '../spmb/admin/login.php');

$pdo      = get_db_connection();
$settings = get_all_settings();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'cloudinary') {
        $cName = trim($_POST['cloudinary_cloud_name'] ?? '');
        $cKey  = trim($_POST['cloudinary_api_key'] ?? '');
        $cSec  = trim($_POST['cloudinary_api_secret'] ?? '');

        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        $ins->execute(['cloudinary_cloud_name', $cName]);
        $ins->execute(['cloudinary_api_key', $cKey]);
        $ins->execute(['cloudinary_api_secret', $cSec]);

        $settings = get_all_settings();
        $success = "Pengaturan Cloudinary CDN berhasil disimpan!";

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success]);
            exit;
        }
    } elseif ($action === 'test_cloudinary') {
        $cName = trim($_POST['cloudinary_cloud_name'] ?? '');
        $cKey  = trim($_POST['cloudinary_api_key'] ?? '');
        $cSec  = trim($_POST['cloudinary_api_secret'] ?? '');

        $res = test_cloudinary_connection($cName, $cKey, $cSec);

        header('Content-Type: application/json');
        echo json_encode($res);
        exit;
    }
}

$cCfg = get_cloudinary_config();

$adminPageTitle   = 'Pengaturan Cloudinary CDN';
$adminPageHeading = 'Penyimpanan Cloud (Cloudinary CDN)';
require_once __DIR__ . '/../spmb/admin/header.php';
?>

<?php $activeTab = 'cloudinary.php'; require_once __DIR__ . '/tabs.php'; ?>

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

<div style="display:grid; grid-template-columns:1.4fr 1fr; gap:24px;">

    <!-- KOLOM KIRI: FORM PENGATURAN CLOUDINARY -->
    <div>
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-cloud" style="color:#0284C7;"></i> Kredensial Akun Cloudinary
                </h3>
                <span class="spmb-badge" style="background:<?php echo $cCfg['configured'] ? '#DCFCE7' : '#FEF3C7'; ?>; color:<?php echo $cCfg['configured'] ? '#166534' : '#92400E'; ?>; font-weight:800; font-size:0.75rem; border:1px solid currentColor;">
                    <i class="ph-bold <?php echo $cCfg['configured'] ? 'ph-check-circle' : 'ph-info'; ?>"></i>
                    <?php echo $cCfg['configured'] ? 'TERHUBUNG (' . htmlspecialchars($cCfg['cloud_name']) . ')' : 'SERVER LOKAL (DEFAULT)'; ?>
                </span>
            </div>
            <div class="adm-card-body">
                <p style="font-size:0.88rem; color:#64748B; margin-bottom:20px; line-height:1.6;">
                    Simpan seluruh foto galeri dokumentasi dan aset sekolah secara otomatis di Cloudinary. Akses gambar akan jauh lebih cepat melalui Content Delivery Network (CDN) global dan menghemat ruang penyimpanan hosting server Anda.
                </p>

                <form id="form-cloudinary" action="cloudinary.php" method="POST">
                    <input type="hidden" name="action" value="cloudinary">

                    <div class="adm-form-group">
                        <label class="adm-form-label">
                            <span class="label-title"><i class="ph-bold ph-cloud"></i> Cloud Name <span style="color:#DC2626;">*</span></span>
                            <span class="label-hint">Nama akun Cloudinary Anda</span>
                        </label>
                        <div class="adm-input-icon-group">
                            <i class="ph-bold ph-globe input-icon-left"></i>
                            <input type="text" name="cloudinary_cloud_name" class="adm-input" placeholder="Contoh: smksukapura" value="<?php echo htmlspecialchars($settings['cloudinary_cloud_name'] ?? env('CLOUDINARY_CLOUD_NAME', '')); ?>" style="font-family:monospace; font-size:0.95rem; font-weight:700;">
                        </div>
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-form-label">
                            <span class="label-title"><i class="ph-bold ph-key"></i> API Key <span style="color:#DC2626;">*</span></span>
                            <span class="label-hint">Kunci API publik</span>
                        </label>
                        <div class="adm-input-icon-group">
                            <i class="ph-bold ph-fingerprint input-icon-left"></i>
                            <input type="text" name="cloudinary_api_key" class="adm-input" placeholder="Contoh: 123456789012345" value="<?php echo htmlspecialchars($settings['cloudinary_api_key'] ?? env('CLOUDINARY_API_KEY', '')); ?>" style="font-family:monospace; font-size:0.95rem; font-weight:700;">
                        </div>
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-form-label">
                            <span class="label-title"><i class="ph-bold ph-lock-key"></i> API Secret <span style="color:#DC2626;">*</span></span>
                            <span class="label-hint">Kunci rahasia API</span>
                        </label>
                        <div class="adm-input-icon-group">
                            <i class="ph-bold ph-shield-check input-icon-left"></i>
                            <input type="password" name="cloudinary_api_secret" id="settings_cloud_secret" class="adm-input" placeholder="Masukkan API Secret" value="<?php echo htmlspecialchars($settings['cloudinary_api_secret'] ?? env('CLOUDINARY_API_SECRET', '')); ?>" style="font-family:monospace; font-size:0.95rem; font-weight:700; padding-right:44px;">
                            <button type="button" onclick="const f = document.getElementById('settings_cloud_secret'); const ic = this.querySelector('i'); if (f.type==='password') { f.type='text'; ic.className='ph-bold ph-eye-slash'; } else { f.type='password'; ic.className='ph-bold ph-eye'; }" style="position:absolute; right:14px; background:none; border:none; cursor:pointer; color:#64748B; font-size:1.15rem; display:flex; align-items:center;">
                                <i class="ph-bold ph-eye"></i>
                            </button>
                        </div>
                        <span class="spmb-help" style="margin-top:6px; display:block;">
                            Dapatkan kredensial gratis di dashboard <a href="https://cloudinary.com" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">Cloudinary.com</a>. Jika dikosongkan, foto tetap disimpan di folder lokal server.
                        </span>
                    </div>

                    <div style="margin-top:24px; display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="submit" class="adm-btn adm-btn-navy" style="font-weight:800; padding:12px 22px;">
                            <i class="ph-bold ph-floppy-disk"></i> Simpan Pengaturan
                        </button>
                        <button type="button" onclick="testCloudinarySetting()" class="adm-btn adm-btn-warning" style="font-weight:800; padding:12px 20px;">
                            <i class="ph-bold ph-plugs-connected"></i> Uji Koneksi CDN
                        </button>
                        <a href="../spmb/admin/galeri.php" class="adm-btn adm-btn-yellow" style="font-weight:800; padding:12px 20px;">
                            <i class="ph-bold ph-images"></i> Kelola Foto Galeri
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: PANDUAN CEPAT -->
    <div>
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-info" style="color:#0284C7;"></i> Panduan Cloudinary
                </h3>
            </div>
            <div class="adm-card-body" style="font-size:0.85rem; color:#475569; line-height:1.6;">
                <ol style="padding-left:18px; margin:0; display:flex; flex-direction:column; gap:12px;">
                    <li>
                        <strong>Daftar Akun Gratis:</strong>
                        <div>Kunjungi <a href="https://cloudinary.com" target="_blank" style="color:#0284C7; font-weight:700; text-decoration:underline;">Cloudinary.com</a> dan buat akun gratis (Free tier hingga 25 GB penyimpanan).</div>
                    </li>
                    <li>
                        <strong>Buka Dashboard:</strong>
                        <div>Di halaman Dashboard / Settings, salin nilai <strong>Cloud name</strong>, <strong>API Key</strong>, dan <strong>API Secret</strong>.</div>
                    </li>
                    <li>
                        <strong>Simpan &amp; Uji:</strong>
                        <div>Tempelkan ke formulir di samping, klik <em>Simpan Pengaturan</em>, lalu klik tombol <em>Uji Koneksi CDN</em> untuk memastikan koneksi berhasil.</div>
                    </li>
                    <li>
                        <strong>Otomatisasi CDN:</strong>
                        <div>Setiap kali admin mengunggah foto baru di menu Galeri, sistem otomatis mengoptimalkan ukuran gambar dan mengunggahnya ke Cloudinary CDN.</div>
                    </li>
                </ol>
            </div>
        </div>
    </div>

</div>

<script>
function testCloudinarySetting() {
    const form = document.getElementById('form-cloudinary');
    const cName = form.querySelector('[name="cloudinary_cloud_name"]').value.trim();
    const cKey  = form.querySelector('[name="cloudinary_api_key"]').value.trim();
    const cSec  = form.querySelector('[name="cloudinary_api_secret"]').value.trim();

    if (!cName || !cKey || !cSec) {
        Swal.fire({
            icon: 'warning',
            title: 'Kredensial Belum Lengkap',
            text: 'Silakan isi Cloud Name, API Key, dan API Secret sebelum menguji koneksi.'
        });
        return;
    }

    Swal.fire({
        title: 'Menguji Koneksi...',
        text: 'Menghubungi server Cloudinary API...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const fd = new FormData();
    fd.append('action', 'test_cloudinary');
    fd.append('cloudinary_cloud_name', cName);
    fd.append('cloudinary_api_key', cKey);
    fd.append('cloudinary_api_secret', cSec);

    fetch('cloudinary.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Koneksi Berhasil!',
                html: data.message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Gagal',
                html: data.message
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menghubungi Server',
            text: err.message || 'Terjadi kesalahan jaringan.'
        });
    });
}
</script>

<?php require_once __DIR__ . '/../spmb/admin/footer.php'; ?>
