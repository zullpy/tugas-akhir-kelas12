<?php
// spmb/admin/galeri.php
// Manajemen Galeri Foto & Pemilihan Foto Tampil di Beranda

// Load config & auth DULU sebelum output apapun
require_once __DIR__ . '/../config.php';
check_admin_login();
$pdo = get_db_connection();

// Inisialisasi Kredensial Cloudinary
$cloudCfg = get_cloudinary_config();

// PROSES POST REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. SIMPAN PENGATURAN CLOUDINARY
    if ($action === 'save_cloudinary') {
        $cName = trim($_POST['cloud_name'] ?? '');
        $cKey  = trim($_POST['api_key'] ?? '');
        $cSec  = trim($_POST['api_secret'] ?? '');

        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        $ins->execute(['cloudinary_cloud_name', $cName]);
        $ins->execute(['cloudinary_api_key', $cKey]);
        $ins->execute(['cloudinary_api_secret', $cSec]);

        $_SESSION['flash_msg'] = [
            'type' => 'success',
            'title' => 'Konfigurasi Disimpan!',
            'text' => 'Kredensial Cloudinary berhasil diperbarui.'
        ];
        header("Location: galeri.php");
        exit;
    }

    // 1b. UJI KONEKSI CLOUDINARY
    if ($action === 'test_cloudinary') {
        $cName = trim($_POST['cloud_name'] ?? '');
        $cKey  = trim($_POST['api_key'] ?? '');
        $cSec  = trim($_POST['api_secret'] ?? '');

        $testRes = test_cloudinary_connection($cName, $cKey, $cSec);

        header('Content-Type: application/json');
        echo json_encode($testRes);
        exit;
    }

    // 2. TOGGLE TAMPILKAN DI BERANDA (Support AJAX & Form)
    if ($action === 'toggle_beranda') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newStatus = toggle_galeri_beranda($id);
            $countBeranda = (int)$pdo->query("SELECT COUNT(*) FROM `galeri_foto` WHERE `tampilkan_beranda` = 1")->fetchColumn();

            // Jika dipanggil via AJAX Fetch
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'status' => $newStatus,
                    'beranda_count' => $countBeranda,
                    'message' => $newStatus ? 'Foto berhasil dipasang di Beranda depan!' : 'Foto telah dicopot dari Beranda.'
                ]);
                exit;
            }

            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'text' => $newStatus ? 'Foto kini tampil di Beranda depan.' : 'Foto telah disembunyikan dari Beranda.'
            ];
        }
        header("Location: galeri.php");
        exit;
    }

    // 3. TAMBAH FOTO BARU
    if ($action === 'upload_foto') {
        $judul            = trim($_POST['judul'] ?? '');
        $kategoriSelect   = trim($_POST['kategori_select'] ?? 'lomba');
        $kategoriCustom   = trim($_POST['kategori_custom'] ?? '');
        $kategori         = !empty($kategoriCustom) ? strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $kategoriCustom)) : $kategoriSelect;
        $deskripsi        = trim($_POST['deskripsi'] ?? '');
        $tampilkanBeranda = isset($_POST['tampilkan_beranda']) ? 1 : 0;
        $urutan           = (int)($_POST['urutan'] ?? 0);

        if (empty($judul)) {
            $judul = 'Dokumentasi ' . ucfirst($kategori);
        }

        if (!isset($_FILES['file_foto']) || $_FILES['file_foto']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Gagal Mengunggah',
                'text' => 'Pilih file foto yang valid untuk diunggah.'
            ];
            header("Location: galeri.php");
            exit;
        }

        $tmpFile   = $_FILES['file_foto']['tmp_name'];
        $origName  = $_FILES['file_foto']['name'];
        $fileExt   = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $validExts = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($fileExt, $validExts)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Format Tidak Didukung',
                'text' => 'Format file harus berupa JPG, PNG, atau WebP.'
            ];
            header("Location: galeri.php");
            exit;
        }

        if ($_FILES['file_foto']['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Ukuran Terlalu Besar',
                'text' => 'Ukuran maksimal file foto adalah 10 MB.'
            ];
            header("Location: galeri.php");
            exit;
        }

        $savedPath       = null;
        $publicId        = null;
        $usedStorage     = 'local';
        $cloudinaryError = '';

        // Coba upload ke Cloudinary jika sudah dikonfigurasi
        if ($cloudCfg['configured']) {
            $cleanName = pathinfo($origName, PATHINFO_FILENAME);
            $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '_', $cleanName);
            $cPubId    = 'galeri_' . time() . '_' . substr($cleanName, 0, 20);

            $cResult = upload_to_cloudinary($tmpFile, $cPubId, 'smk_sukapura/galeri/' . $kategori);
            if ($cResult['success']) {
                $savedPath   = $cResult['url'];
                $publicId    = $cResult['public_id'];
                $usedStorage = 'cloudinary';
            } else {
                $cloudinaryError = $cResult['message'] ?? 'Gagal upload ke Cloudinary';
            }
        }

        // Fallback simpan lokal jika Cloudinary belum disetel atau gagal
        if (!$savedPath) {
            $uploadDir = dirname(dirname(__DIR__)) . '/assets/galeri/uploads';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $newFileName = 'galeri_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $destination = $uploadDir . '/' . $newFileName;

            if (move_uploaded_file($tmpFile, $destination)) {
                $savedPath = 'assets/galeri/uploads/' . $newFileName;
                $usedStorage = 'local';
            } else {
                $_SESSION['flash_msg'] = [
                    'type' => 'error',
                    'title' => 'Gagal Menyimpan File',
                    'text' => 'Tidak dapat menyimpan file ke direktori server.'
                ];
                header("Location: galeri.php");
                exit;
            }
        }

        // Simpan ke database
        add_galeri_photo($judul, $kategori, $savedPath, $deskripsi, $tampilkanBeranda, $publicId, $urutan);

        // Ambil data foto yang baru disimpan
        $newId = (int)$pdo->lastInsertId();
        $newFoto = $pdo->prepare("SELECT * FROM `galeri_foto` WHERE `id` = ?");
        $newFoto->execute([$newId]);
        $newFotoData = $newFoto->fetch(PDO::FETCH_ASSOC);

        $hasCloudinaryFailure = (!empty($cloudinaryError) && $usedStorage === 'local');
        if ($usedStorage === 'cloudinary') {
            $storageNote = 'Tersimpan aman di Cloud CDN (Cloudinary).';
        } elseif ($hasCloudinaryFailure) {
            $storageNote = 'PERHATIAN: Upload Cloudinary gagal (' . $cloudinaryError . '). Foto dialihkan dan disimpan ke server lokal.';
        } else {
            $storageNote = 'Tersimpan di server lokal.';
        }

        // Jika dipanggil via AJAX, kembalikan JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success'       => true,
                'warning'       => $hasCloudinaryFailure,
                'foto'          => $newFotoData,
                'is_cloudinary' => $usedStorage === 'cloudinary',
                'cloudinary_error' => $cloudinaryError,
                'message'       => $storageNote . ($tampilkanBeranda ? ' Foto dipasang di Beranda.' : '')
            ]);
            exit;
        }

        $_SESSION['flash_msg'] = [
            'type'  => $hasCloudinaryFailure ? 'warning' : 'success',
            'title' => $hasCloudinaryFailure ? 'Tersimpan di Lokal (Cloudinary Gagal)' : 'Foto Berhasil Ditambahkan!',
            'text'  => $storageNote . ($tampilkanBeranda ? ' Foto ini langsung dipasang di Beranda.' : '')
        ];
        header("Location: galeri.php");
        exit;
    }

    // 4. EDIT INFO FOTO
    if ($action === 'edit_foto') {
        $id               = (int)($_POST['id'] ?? 0);
        $judul            = trim($_POST['judul'] ?? '');
        $kategoriSelect   = trim($_POST['kategori_select'] ?? 'lomba');
        $kategoriCustom   = trim($_POST['kategori_custom'] ?? '');
        $kategori         = !empty($kategoriCustom) ? strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $kategoriCustom)) : $kategoriSelect;
        $deskripsi        = trim($_POST['deskripsi'] ?? '');
        $tampilkanBeranda = isset($_POST['tampilkan_beranda']) ? 1 : 0;
        $urutan           = (int)($_POST['urutan'] ?? 0);

        if ($id > 0 && !empty($judul)) {
            update_galeri_photo($id, $judul, $kategori, $deskripsi, $tampilkanBeranda, $urutan);

            // Jika dipanggil via AJAX, kembalikan JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $updatedFoto = $pdo->prepare("SELECT * FROM `galeri_foto` WHERE `id` = ?");
                $updatedFoto->execute([$id]);
                $updatedData = $updatedFoto->fetch(PDO::FETCH_ASSOC);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'foto' => $updatedData, 'message' => 'Informasi foto berhasil diperbarui.']);
                exit;
            }

            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Data Diperbarui',
                'text' => 'Informasi foto galeri berhasil diperbarui.'
            ];
        }
        header("Location: galeri.php");
        exit;
    }

    // 5. HAPUS FOTO
    if ($action === 'delete_foto') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_galeri_photo($id);

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Foto berhasil dihapus.']);
                exit;
            }

            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Foto Dihapus',
                'text' => 'Foto telah berhasil dihapus dari galeri.'
            ];
        }
        header("Location: galeri.php");
        exit;
    }
}

// Sekarang baru tampilkan HTML (setelah semua AJAX/redirect sudah exit)
$adminPageTitle   = 'Kelola Galeri & Beranda';
$adminPageHeading = 'Manajemen Galeri & Foto Beranda';
require_once __DIR__ . '/header.php';

// PARAMETER FILTER & TAMPILAN
$filterKategori = $_GET['kategori'] ?? 'semua';
$filterBeranda  = isset($_GET['beranda']) ? (int)$_GET['beranda'] : -1; // -1: semua, 1: beranda, 0: bukan beranda
$searchQuery    = trim($_GET['q'] ?? '');

// Kueri Foto
$sql = "SELECT * FROM `galeri_foto` WHERE 1=1";
$params = [];

if (!empty($filterKategori) && $filterKategori !== 'semua') {
    $sql .= " AND `kategori` = ?";
    $params[] = $filterKategori;
}

if ($filterBeranda === 1) {
    $sql .= " AND `tampilkan_beranda` = 1";
} elseif ($filterBeranda === 0) {
    $sql .= " AND `tampilkan_beranda` = 0";
}

if (!empty($searchQuery)) {
    $sql .= " AND (`judul` LIKE ? OR `deskripsi` LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sql .= " ORDER BY `tampilkan_beranda` DESC, `urutan` ASC, `id` DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarFoto = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung Statistik
$statTotal     = (int)$pdo->query("SELECT COUNT(*) FROM `galeri_foto`")->fetchColumn();
$statBeranda   = (int)$pdo->query("SELECT COUNT(*) FROM `galeri_foto` WHERE `tampilkan_beranda` = 1")->fetchColumn();
$statKategori  = (int)$pdo->query("SELECT COUNT(DISTINCT `kategori`) FROM `galeri_foto`")->fetchColumn();

// Kategori Terdaftar
$kategoriList = [
    'lomba'     => 'Kegiatan Lomba',
    'istigosah' => 'Kegiatan Keagamaan & Istigosah',
    'porsekas'  => 'Pekan Olahraga & Seni (Porsekas)',
    'sertijab'  => 'Serah Terima Jabatan OSIS',
    'tka'       => 'Akademik & Ujian (TKA)',
    'upacara'   => 'Upacara Bendera',
    'expo'      => 'Pameran & Expo Karya',
    'prestasi'  => 'Prestasi Siswa'
];

$distinctKat = $pdo->query("SELECT DISTINCT `kategori` FROM `galeri_foto`")->fetchAll(PDO::FETCH_COLUMN);
foreach ($distinctKat as $dk) {
    if (!isset($kategoriList[$dk])) {
        $kategoriList[$dk] = ucfirst($dk);
    }
}
?>

<!-- FLASH NOTIFICATION -->
<?php if (!empty($_SESSION['flash_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: '<?php echo $_SESSION['flash_msg']['type']; ?>',
                title: '<?php echo addslashes($_SESSION['flash_msg']['title']); ?>',
                text: '<?php echo addslashes($_SESSION['flash_msg']['text']); ?>',
                confirmButtonColor: '#0A4D68'
            });
        });
    </script>
    <?php unset($_SESSION['flash_msg']); ?>
<?php endif; ?>

<!-- TOP ACTION BAR -->
<div class="adm-section-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--adm-black); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
            <i class="ph-bold ph-images" style="color: var(--adm-navy);"></i> Kelola Galeri &amp; Beranda
        </h2>
        <p style="color: #64748B; font-size: 0.92rem;">Unggah foto dokumentasi sekolah dan tentukan foto terbaik untuk ditampilkan langsung di Beranda.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="adm-btn adm-btn-yellow" onclick="openUploadModal()" style="font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-plus-circle" style="font-size: 1.25rem;"></i> Tambah Foto Baru
        </button>
        <button type="button" class="adm-btn adm-btn-white" onclick="openCloudinaryModal()" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-cloud" style="color: #0284C7; font-size: 1.25rem;"></i> Konfigurasi Cloudinary
        </button>
        <a href="../../index.php#galeri" target="_blank" class="adm-btn adm-btn-white" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;" title="Lihat Seksi Galeri di Beranda Depan">
            <i class="ph-bold ph-globe"></i> Beranda Sekolah
        </a>
    </div>
</div>

<!-- METRICS CARDS -->
<div class="adm-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 28px;">
    <!-- Stat 1: Total Foto -->
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: var(--adm-navy-dark);"><?php echo $statTotal; ?></div>
            <div class="adm-stat-title">Total Koleksi Foto</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #E0F2FE;">
            <i class="ph-bold ph-images" style="color: var(--adm-navy);"></i>
        </div>
    </div>

    <!-- Stat 2: Foto di Beranda -->
    <div class="adm-stat-card" style="border: 2.5px solid var(--adm-black); background: #FFFDF0;">
        <div>
            <div class="adm-stat-val" style="color: #B45309;" id="stat-beranda-val"><?php echo $statBeranda; ?></div>
            <div class="adm-stat-title" style="font-weight: 800; color: #000;">Tampil di Beranda</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: var(--adm-yellow);">
            <i class="ph-bold ph-star" style="color: var(--adm-black);"></i>
        </div>
    </div>

    <!-- Stat 3: Kategori -->
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: var(--adm-purple);"><?php echo $statKategori; ?></div>
            <div class="adm-stat-title">Kategori Kegiatan</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #F3E8FF;">
            <i class="ph-bold ph-folder-notch-open" style="color: var(--adm-purple);"></i>
        </div>
    </div>

    <!-- Stat 4: Status Cloudinary -->
    <div class="adm-stat-card" style="cursor: pointer;" onclick="openCloudinaryModal()" title="Klik untuk mengonfigurasi Cloudinary">
        <div>
            <?php if ($cloudCfg['configured']): ?>
                <div style="font-size: 1.15rem; font-weight: 800; color: #15803D; font-family: var(--font-heading); margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <span style="width: 10px; height: 10px; background: #22C55E; border-radius: 50%; display: inline-block;"></span> Aktif (Cloud)
                </div>
                <div class="adm-stat-title" style="font-size: 0.8rem;"><?php echo htmlspecialchars($cloudCfg['cloud_name']); ?></div>
            <?php else: ?>
                <div style="font-size: 1.05rem; font-weight: 800; color: #D97706; font-family: var(--font-heading); margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <span style="width: 10px; height: 10px; background: #F59E0B; border-radius: 50%; display: inline-block;"></span> Server Lokal
                </div>
                <div class="adm-stat-title" style="font-size: 0.8rem;">Klik untuk hubungkan Cloudinary</div>
            <?php endif; ?>
        </div>
        <div class="adm-stat-icon-wrap" style="background: <?php echo $cloudCfg['configured'] ? '#DCFCE7' : '#FEF3C7'; ?>;">
            <i class="ph-bold ph-cloud-arrow-up" style="color: <?php echo $cloudCfg['configured'] ? '#15803D' : '#D97706'; ?>;"></i>
        </div>
    </div>
</div>

<!-- FILTER & SEARCH BAR OVERHAUL -->
<div class="adm-card" style="margin-bottom: 26px; padding: 18px 22px;">
    <form method="GET" action="galeri.php" id="filterForm" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        
        <!-- Left Filter: Tabs Beranda -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 800; font-size: 0.82rem; color: #64748B; text-transform: uppercase; margin-right: 2px;">
                <i class="ph-bold ph-funnel"></i> Status:
            </span>
            <a href="galeri.php?kategori=<?php echo urlencode($filterKategori); ?>&q=<?php echo urlencode($searchQuery); ?>" 
               class="adm-btn <?php echo ($filterBeranda === -1) ? 'adm-btn-navy' : 'adm-btn-white'; ?>" 
               style="padding: 7px 14px; font-size: 0.85rem;">
               Semua (<?php echo $statTotal; ?>)
            </a>
            <a href="galeri.php?beranda=1&kategori=<?php echo urlencode($filterKategori); ?>&q=<?php echo urlencode($searchQuery); ?>" 
               class="adm-btn <?php echo ($filterBeranda === 1) ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" 
               style="padding: 7px 14px; font-size: 0.85rem;">
               <i class="ph-bold ph-star-fill"></i> Tampil di Beranda (<?php echo $statBeranda; ?>)
            </a>
            <a href="galeri.php?beranda=0&kategori=<?php echo urlencode($filterKategori); ?>&q=<?php echo urlencode($searchQuery); ?>" 
               class="adm-btn <?php echo ($filterBeranda === 0) ? 'adm-btn-navy' : 'adm-btn-white'; ?>" 
               style="padding: 7px 14px; font-size: 0.85rem;">
               Khusus Galeri
            </a>
        </div>

        <!-- Right Filter: Kategori & Search with Premium Inputs -->
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            
            <!-- Category Dropdown with Icon -->
            <div style="min-width: 200px;">
                <div class="adm-input-icon-group">
                    <i class="ph-bold ph-folder input-icon-left"></i>
                    <select name="kategori" onchange="document.getElementById('filterForm').submit();" class="adm-select" style="padding-left: 38px; font-size: 0.88rem; font-weight: 700; height: 44px;">
                        <option value="semua">-- Semua Kategori --</option>
                        <?php foreach ($kategoriList as $kKey => $kLabel): ?>
                            <option value="<?php echo $kKey; ?>" <?php echo ($filterKategori === $kKey) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if ($filterBeranda !== -1): ?>
                <input type="hidden" name="beranda" value="<?php echo $filterBeranda; ?>">
            <?php endif; ?>

            <!-- Search Group with Icon -->
            <div style="display: flex; gap: 8px;">
                <div class="adm-input-icon-group" style="width: 220px;">
                    <i class="ph-bold ph-magnifying-glass input-icon-left"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Cari judul foto..." class="adm-input" style="padding-left: 38px; font-size: 0.88rem; height: 44px;">
                </div>
                <button type="submit" class="adm-btn adm-btn-navy" style="padding: 0 16px; height: 44px; display: flex; align-items: center; justify-content: center;" title="Cari">
                    <i class="ph-bold ph-arrow-right" style="font-size: 1.1rem;"></i>
                </button>
            </div>

            <?php if (!empty($searchQuery) || $filterKategori !== 'semua' || $filterBeranda !== -1): ?>
                <a href="galeri.php" class="adm-btn adm-btn-white" style="height: 44px; padding: 0 14px; display: flex; align-items: center; justify-content: center;" title="Reset Filter">
                    <i class="ph-bold ph-arrow-counter-clockwise"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- PHOTO GRID SECTION -->
<?php if (empty($daftarFoto)): ?>
    <div id="empty-galeri-state" class="adm-card" style="padding: 50px 20px; text-align: center;">
        <div style="width: 74px; height: 74px; background: #F1F5F9; border: 2.5px solid var(--adm-black); border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #64748B; box-shadow: 3px 3px 0px var(--adm-black);">
            <i class="ph-bold ph-image-broken"></i>
        </div>
        <h3 style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; margin-bottom: 6px;">Tidak Ada Foto Ditemukan</h3>
        <p style="color: #64748B; font-size: 0.9rem; max-width: 460px; margin: 0 auto 20px;">
            Belum ada foto yang cocok dengan filter atau kata kunci pencarian Anda.
        </p>
        <button type="button" class="adm-btn adm-btn-yellow" onclick="openUploadModal()">
            <i class="ph-bold ph-plus-circle"></i> Tambah Foto Baru Sekarang
        </button>
    </div>
<?php else: ?>
    <div id="galeri-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px;">
        <?php foreach ($daftarFoto as $f): ?>
            <?php 
                $isBeranda = (int)$f['tampilkan_beranda'] === 1;
                $imgSrc = $f['file_path'];
                if (!preg_match('/^https?:\/\//', $imgSrc)) {
                    $imgSrc = '../../' . ltrim($imgSrc, '/');
                }
                $isCloudinary = (!empty($f['public_id']) || strpos($f['file_path'], 'cloudinary.com') !== false);
            ?>
            <div class="adm-card photo-card" id="card-foto-<?php echo $f['id']; ?>" style="display: flex; flex-direction: column; overflow: hidden; padding: 0; transition: transform 0.2s, box-shadow 0.2s; position: relative;">
                
                <!-- Card Header Image -->
                <div style="position: relative; width: 100%; height: 210px; background: #053B50; overflow: hidden; border-bottom: var(--border-thick);">
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                         alt="<?php echo htmlspecialchars($f['judul']); ?>" 
                         loading="lazy"
                         style="width: 100%; height: 100%; object-fit: cover; cursor: pointer; transition: transform 0.3s ease;"
                         onclick="previewFullImage('<?php echo htmlspecialchars($imgSrc); ?>', '<?php echo htmlspecialchars(addslashes($f['judul'])); ?>')"
                         onmouseover="this.style.transform='scale(1.06)'"
                         onmouseout="this.style.transform='scale(1)'">

                    <!-- Category Badge -->
                    <span style="position: absolute; top: 12px; left: 12px; background: #0A4D68; color: #fff; font-size: 0.72rem; font-weight: 800; padding: 4px 10px; border-radius: 6px; border: 1.5px solid var(--adm-black); box-shadow: 2px 2px 0px var(--adm-black); text-transform: uppercase;">
                        <?php echo htmlspecialchars($kategoriList[$f['kategori']] ?? ucfirst($f['kategori'])); ?>
                    </span>

                    <!-- Cloudinary / Local Indicator Badge -->
                    <span style="position: absolute; top: 12px; right: 12px; background: <?php echo $isCloudinary ? '#10B981' : '#F59E0B'; ?>; color: #fff; font-size: 0.7rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; border: 1.5px solid var(--adm-black); display: flex; align-items: center; gap: 4px; box-shadow: 2px 2px 0px var(--adm-black);" title="<?php echo $isCloudinary ? 'Disimpan di Cloudinary CDN' : 'Disimpan di Server Lokal'; ?>">
                        <i class="ph-bold <?php echo $isCloudinary ? 'ph-cloud-check' : 'ph-hard-drive'; ?>"></i>
                        <?php echo $isCloudinary ? 'Cloud' : 'Lokal'; ?>
                    </span>

                    <!-- Beranda Indicator Ribbon -->
                    <div id="beranda-ribbon-<?php echo $f['id']; ?>" style="display: <?php echo $isBeranda ? 'flex' : 'none'; ?>; position: absolute; bottom: 10px; left: 12px; background: var(--adm-yellow); color: var(--adm-black); font-size: 0.72rem; font-weight: 800; padding: 4px 9px; border-radius: 6px; border: 1.5px solid var(--adm-black); box-shadow: 2px 2px 0px var(--adm-black); align-items: center; gap: 4px;">
                        <i class="ph-bold ph-star-fill"></i> TAMPIL DI BERANDA
                    </div>
                </div>

                <!-- Card Content -->
                <div style="padding: 16px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h4 style="font-family: var(--font-heading); font-size: 1.02rem; font-weight: 800; color: var(--adm-black); margin-bottom: 6px; line-height: 1.35;">
                            <?php echo htmlspecialchars($f['judul']); ?>
                        </h4>
                        <?php if (!empty($f['deskripsi'])): ?>
                            <p style="color: #64748B; font-size: 0.82rem; margin-bottom: 12px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo htmlspecialchars($f['deskripsi']); ?>
                            </p>
                        <?php else: ?>
                            <div style="font-size: 0.76rem; color: #94A3B8; margin-bottom: 12px; display: flex; align-items: center; gap: 4px;">
                                <i class="ph-bold ph-calendar"></i> <?php echo date('d M Y', strtotime($f['created_at'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Interactive Control Bar -->
                    <div style="padding-top: 14px; border-top: 1.5px dashed #CBD5E1; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        
                        <!-- Toggle Beranda Button -->
                        <button type="button" 
                                class="adm-btn btn-toggle-beranda" 
                                id="btn-toggle-<?php echo $f['id']; ?>"
                                onclick="toggleBerandaAjax(<?php echo $f['id']; ?>)"
                                style="font-size: 0.78rem; font-weight: 800; padding: 7px 12px; display: inline-flex; align-items: center; gap: 6px; background: <?php echo $isBeranda ? 'var(--adm-yellow)' : '#F1F5F9'; ?>; color: var(--adm-black);">
                            <i class="ph-bold <?php echo $isBeranda ? 'ph-check-circle' : 'ph-circle'; ?>" id="icon-toggle-<?php echo $f['id']; ?>" style="font-size: 1rem;"></i>
                            <span id="text-toggle-<?php echo $f['id']; ?>"><?php echo $isBeranda ? 'Di Beranda' : '+ Pasang di Beranda'; ?></span>
                        </button>

                        <!-- Action Buttons: Edit & Delete -->
                        <div style="display: flex; gap: 6px;">
                            <button type="button" 
                                    class="adm-btn adm-btn-white" 
                                    style="padding: 7px 10px; font-size: 0.9rem;"
                                    title="Ubah Info Foto"
                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($f)); ?>)">
                                <i class="ph-bold ph-pencil-simple"></i>
                            </button>
                            <button type="button" 
                                    class="adm-btn" 
                                    style="padding: 7px 10px; font-size: 0.9rem; background: #FEE2E2; color: #DC2626; border-color: var(--adm-black);"
                                    title="Hapus Foto"
                                    onclick="confirmDeletePhoto(<?php echo $f['id']; ?>, '<?php echo htmlspecialchars(addslashes($f['judul'])); ?>')">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ===================================================================== -->
<!-- MODAL: UPLOAD FOTO BARU (OVERHAULED ULTRA MODERN) -->
<!-- ===================================================================== -->
<div id="modalUpload" class="custom-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center; padding: 20px; overflow: hidden;">
    <div class="adm-card modal-inner-scroll" style="width: 100%; max-width: 580px; max-height: 92vh; overflow-y: auto; background: #fff; border: 3px solid var(--adm-black); box-shadow: 10px 10px 0px var(--adm-black); padding: 0;">
        
        <!-- Modal Top Accent Banner -->
        <div style="background: var(--adm-yellow); border-bottom: var(--border-thick); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; background: #fff; border: 2px solid var(--adm-black); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 2px 2px 0px var(--adm-black);">
                    <i class="ph-bold ph-cloud-arrow-up"></i>
                </div>
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800; color: var(--adm-black); margin: 0; line-height: 1.2;">
                        Tambah Foto Galeri Baru
                    </h3>
                    <div style="font-size: 0.76rem; font-weight: 700; color: #475569;">Unggah ke galeri &amp; pilih untuk beranda</div>
                </div>
            </div>
            <button type="button" onclick="closeUploadModal()" style="background: #fff; border: 2px solid var(--adm-black); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; cursor: pointer; color: var(--adm-black); font-weight: 800; box-shadow: 2px 2px 0px var(--adm-black);">&times;</button>
        </div>

        <form method="POST" action="galeri.php" enctype="multipart/form-data" id="formUploadFoto" style="padding: 24px;">
            <input type="hidden" name="action" value="upload_foto">

            <!-- Premium Dropzone File Picker -->
            <div class="adm-form-group">
                <label class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-image"></i> Berkas Foto <span class="label-req">*</span></span>
                    <span class="label-hint">JPG, PNG, WebP (Maks 10MB)</span>
                </label>
                
                <div id="dropzone" class="adm-dropzone" onclick="document.getElementById('file_foto').click()">
                    <div class="adm-dropzone-icon-wrap">
                        <i class="ph-bold ph-cloud-arrow-up"></i>
                    </div>
                    <div class="adm-dropzone-title">Tarik &amp; Lepaskan foto ke sini, atau klik untuk memilih</div>
                    <div class="adm-dropzone-sub">Pilih foto dokumentasi dengan resolusi jernih</div>
                    <div class="adm-dropzone-badges">
                    </div>
                    <input type="file" name="file_foto" id="file_foto" accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="previewSelectedImage(this)" required>
                </div>

                <!-- Live Preview Card -->
                <div id="image-preview-box" style="display: none; border: 2px solid var(--adm-black); border-radius: 12px; overflow: hidden; background: #053B50; position: relative; box-shadow: 3px 3px 0px var(--adm-black);">
                    <div style="height: 190px; width: 100%; overflow: hidden; background: #021e29; display: flex; align-items: center; justify-content: center;">
                        <img id="image-preview" src="" alt="Preview" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                    </div>
                    <div style="padding: 10px 14px; background: #FFF; border-top: 2px solid var(--adm-black); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                            <i class="ph-bold ph-check-circle" style="color: #10B981; font-size: 1.2rem; flex-shrink: 0;"></i>
                            <div style="font-size: 0.82rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" id="preview-filename">foto_terpilih.jpg</div>
                        </div>
                        <button type="button" onclick="resetFilePicker(event)" class="adm-btn" style="background: #FEE2E2; color: #DC2626; padding: 4px 10px; font-size: 0.78rem; font-weight: 800;">
                            <i class="ph-bold ph-trash"></i> Ganti
                        </button>
                    </div>
                </div>
            </div>

            <!-- Judul Foto -->
            <div class="adm-form-group">
                <label for="judul" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-tag"></i> Judul / Nama Kegiatan <span class="label-req">*</span></span>
                </label>
                <div class="adm-input-icon-group">
                    <i class="ph-bold ph-textbox input-icon-left"></i>
                    <input type="text" name="judul" id="judul" placeholder="Contoh: Upacara Bendera HUT RI Ke-81" class="adm-input" required>
                </div>
            </div>

            <!-- Kategori Foto -->
            <div class="adm-form-group">
                <label for="kategori_select" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-folder"></i> Kategori Kegiatan <span class="label-req">*</span></span>
                    <span class="label-hint">Tentukan album kegiatan</span>
                </label>
                <div class="adm-input-icon-group">
                    <i class="ph-bold ph-squares-four input-icon-left"></i>
                    <select name="kategori_select" id="kategori_select" class="adm-select" onchange="toggleCustomCategory(this.value)">
                        <?php foreach ($kategoriList as $kKey => $kLabel): ?>
                            <option value="<?php echo $kKey; ?>"><?php echo htmlspecialchars($kLabel); ?></option>
                        <?php endforeach; ?>
                        <option value="_custom">+ Kategori Kustom Baru...</option>
                    </select>
                </div>
                <div id="custom-kategori-wrap" style="display: none; margin-top: 8px;">
                    <div class="adm-input-icon-group">
                        <i class="ph-bold ph-pencil-simple input-icon-left"></i>
                        <input type="text" name="kategori_custom" id="kategori_custom" placeholder="Tuliskan nama kategori baru..." class="adm-input">
                    </div>
                </div>
            </div>

            <!-- Deskripsi Singkat -->
            <div class="adm-form-group">
                <label for="deskripsi" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-text-align-left"></i> Keterangan Singkat (Opsional)</span>
                </label>
                <textarea name="deskripsi" id="deskripsi" rows="2" placeholder="Tuliskan catatan singkat tentang momen atau peristiwa kegiatan ini..." class="adm-input" style="resize: vertical;"></textarea>
            </div>

            <!-- Urutan Tampilan -->
            <div class="adm-form-group">
                <label for="urutan" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-sort-ascending"></i> Urutan Penayangan</span>
                    <span class="label-hint">Angka lebih kecil tampil lebih dulu</span>
                </label>
                <div class="adm-input-icon-group" style="width: 140px;">
                    <i class="ph-bold ph-hash input-icon-left"></i>
                    <input type="number" name="urutan" id="urutan" value="0" class="adm-input">
                </div>
            </div>

            <!-- Storage Destination Badge -->
            <div style="margin-top: 18px; margin-bottom: 22px; padding: 12px 14px; background: #F8FAFC; border: 1.5px solid var(--adm-black); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; font-weight: 700; color: #334155;">
                    <i class="ph-bold ph-hard-drives" style="font-size: 1.1rem; color: var(--adm-navy);"></i>
                    Penyimpanan Target:
                </div>
                <span style="font-size: 0.78rem; font-weight: 800; background: <?php echo $cloudCfg['configured'] ? '#DCFCE7' : '#FEF3C7'; ?>; color: <?php echo $cloudCfg['configured'] ? '#15803D' : '#92400E'; ?>; border: 1.5px solid currentColor; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="ph-bold <?php echo $cloudCfg['configured'] ? 'ph-cloud-check' : 'ph-hard-drive'; ?>"></i>
                    <?php echo $cloudCfg['configured'] ? 'Cloudinary CDN' : 'Server Lokal'; ?>
                </span>
            </div>

            <!-- Modal Action Footer -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: var(--border-thick); padding-top: 18px;">
                <button type="button" class="adm-btn adm-btn-white" onclick="closeUploadModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-yellow" style="font-weight: 800; padding: 12px 24px;">
                    <i class="ph-bold ph-upload-simple" style="font-size: 1.15rem;"></i> Unggah Sekarang
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL: EDIT FOTO (OVERHAULED ULTRA MODERN) -->
<!-- ===================================================================== -->
<div id="modalEdit" class="custom-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center; padding: 20px; overflow: hidden;">
    <div class="adm-card modal-inner-scroll" style="width: 100%; max-width: 540px; max-height: 92vh; overflow-y: auto; background: #fff; border: 3px solid var(--adm-black); box-shadow: 10px 10px 0px var(--adm-black); padding: 0;">
        
        <div style="background: #E0F2FE; border-bottom: var(--border-thick); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; background: #fff; border: 2px solid var(--adm-black); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 2px 2px 0px var(--adm-black);">
                    <i class="ph-bold ph-pencil-simple"></i>
                </div>
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800; color: var(--adm-black); margin: 0; line-height: 1.2;">
                        Ubah Informasi Foto
                    </h3>
                    <div style="font-size: 0.76rem; font-weight: 700; color: #475569;">Edit metadata judul &amp; status beranda</div>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" style="background: #fff; border: 2px solid var(--adm-black); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; cursor: pointer; color: var(--adm-black); font-weight: 800; box-shadow: 2px 2px 0px var(--adm-black);">&times;</button>
        </div>

        <form method="POST" action="galeri.php" id="formEditFoto" style="padding: 24px;">
            <input type="hidden" name="action" value="edit_foto">
            <input type="hidden" name="id" id="edit_id" value="">

            <div class="adm-form-group">
                <label for="edit_judul" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-tag"></i> Judul Foto <span class="label-req">*</span></span>
                </label>
                <div class="adm-input-icon-group">
                    <i class="ph-bold ph-textbox input-icon-left"></i>
                    <input type="text" name="judul" id="edit_judul" class="adm-input" required>
                </div>
            </div>

            <div class="adm-form-group">
                <label for="edit_kategori_select" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-folder"></i> Kategori Galeri <span class="label-req">*</span></span>
                </label>
                <div class="adm-input-icon-group">
                    <i class="ph-bold ph-squares-four input-icon-left"></i>
                    <select name="kategori_select" id="edit_kategori_select" class="adm-select">
                        <?php foreach ($kategoriList as $kKey => $kLabel): ?>
                            <option value="<?php echo $kKey; ?>"><?php echo htmlspecialchars($kLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="adm-form-group">
                <label for="edit_deskripsi" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-text-align-left"></i> Keterangan / Deskripsi</span>
                </label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2" class="adm-input" style="resize: vertical;"></textarea>
            </div>

            <div class="adm-form-group">
                <label for="edit_urutan" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-sort-ascending"></i> Urutan Tampilan</span>
                </label>
                <div class="adm-input-icon-group" style="width: 140px;">
                    <i class="ph-bold ph-hash input-icon-left"></i>
                    <input type="number" name="urutan" id="edit_urutan" value="0" class="adm-input">
                </div>
            </div>

            <!-- Modern Toggle Switch Card -->
            <div class="adm-toggle-card" onclick="document.getElementById('edit_tampilkan_beranda').click()">
                <div class="adm-toggle-info">
                    <div class="adm-toggle-icon">
                        <i class="ph-bold ph-star-fill"></i>
                    </div>
                    <div>
                        <div class="adm-toggle-label">Tampilkan di Halaman Beranda</div>
                        <div class="adm-toggle-sub">Tampilkan foto ini di seksi depan homepage.</div>
                    </div>
                </div>
                <label class="adm-switch" onclick="event.stopPropagation()">
                    <input type="checkbox" name="tampilkan_beranda" id="edit_tampilkan_beranda" value="1">
                    <span class="adm-switch-slider"></span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; border-top: var(--border-thick); padding-top: 18px;">
                <button type="button" class="adm-btn adm-btn-white" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-navy" style="font-weight: 800; padding: 12px 24px;">
                    <i class="ph-bold ph-check"></i> Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL: KONFIGURASI CLOUDINARY (OVERHAULED ULTRA MODERN) -->
<!-- ===================================================================== -->
<div id="modalCloudinary" class="custom-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center; padding: 20px; overflow: hidden;">
    <div class="adm-card" style="width: 100%; max-width: 560px; background: #fff; border: 3px solid var(--adm-black); box-shadow: 10px 10px 0px var(--adm-black); padding: 0;">
        
        <div style="background: #E0F2FE; border-bottom: var(--border-thick); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; background: #fff; border: 2px solid var(--adm-black); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 2px 2px 0px var(--adm-black);">
                    <i class="ph-bold ph-cloud" style="color: #0284C7;"></i>
                </div>
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800; color: var(--adm-black); margin: 0; line-height: 1.2;">
                        Konfigurasi Cloudinary CDN
                    </h3>
                    <div style="font-size: 0.76rem; font-weight: 700; color: #475569;">Penyimpanan foto cloud &amp; optimasi CDN</div>
                </div>
            </div>
            <button type="button" onclick="closeCloudinaryModal()" style="background: #fff; border: 2px solid var(--adm-black); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; cursor: pointer; color: var(--adm-black); font-weight: 800; box-shadow: 2px 2px 0px var(--adm-black);">&times;</button>
        </div>

        <form method="POST" action="galeri.php" style="padding: 24px;">
            <input type="hidden" name="action" value="save_cloudinary">

            <div style="margin-bottom: 18px; padding: 12px 14px; background: #F8FAFC; border: 1.5px solid var(--adm-black); border-radius: 10px; font-size: 0.84rem; color: #475569; line-height: 1.5;">
                <i class="ph-bold ph-info" style="color: #0284C7;"></i>
                Cloudinary membuat loading gambar website menjadi <strong>jauh lebih kencang</strong> dan menghemat kuota penyimpanan hosting Anda.
            </div>

            <div class="adm-form-group">
                <label for="cloud_name" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-cloud"></i> Cloud Name <span class="label-req">*</span></span>
                </label>
                <div class="adm-input-icon-group">
                    <i class="ph-bold ph-globe input-icon-left"></i>
                    <input type="text" name="cloud_name" id="cloud_name" value="<?php echo htmlspecialchars($cloudCfg['cloud_name']); ?>" placeholder="Contoh: smksukapura" class="adm-input" required style="font-family: monospace;">
                </div>
            </div>

            <div class="adm-form-group">
                <label for="api_key" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-key"></i> API Key <span class="label-req">*</span></span>
                </label>
                <div class="adm-input-icon-group">
                    <i class="ph-bold ph-fingerprint input-icon-left"></i>
                    <input type="text" name="api_key" id="api_key" value="<?php echo htmlspecialchars($cloudCfg['api_key']); ?>" placeholder="Contoh: 123456789012345" class="adm-input" required style="font-family: monospace;">
                </div>
            </div>

            <div class="adm-form-group">
                <label for="api_secret" class="adm-form-label">
                    <span class="label-title"><i class="ph-bold ph-lock-key"></i> API Secret <span class="label-req">*</span></span>
                </label>
                <div class="adm-input-icon-group">
                    <i class="ph-bold ph-shield-check input-icon-left"></i>
                    <input type="password" name="api_secret" id="api_secret" value="<?php echo htmlspecialchars($cloudCfg['api_secret']); ?>" placeholder="Contoh: abcdEFGHIJKLMnop12345" class="adm-input" required style="font-family: monospace; padding-right: 42px;">
                    <button type="button" onclick="togglePasswordVisibility('api_secret', this)" style="position: absolute; right: 12px; background: none; border: none; cursor: pointer; color: #64748B; font-size: 1.15rem; display: flex; align-items: center;" title="Lihat/Sembunyikan Secret">
                        <i class="ph-bold ph-eye"></i>
                    </button>
                </div>
            </div>

            <div style="background: #FFFDF0; border: 1.5px solid var(--adm-black); border-radius: 8px; padding: 10px 14px; font-size: 0.78rem; color: #475569; margin-bottom: 20px;">
                <i class="ph-bold ph-lightbulb" style="color: var(--adm-yellow);"></i> <strong>Dapatkan Kunci:</strong> Buat akun gratis di <a href="https://cloudinary.com/users/login" target="_blank" style="color: #0284C7; font-weight: 800; text-decoration: underline;">cloudinary.com</a>. Jika dikosongkan, file akan tersimpan di server lokal.
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; border-top: var(--border-thick); padding-top: 18px; flex-wrap: wrap;">
                <button type="button" class="adm-btn adm-btn-warning" onclick="testCloudinaryConnection()" style="font-weight: 800; padding: 11px 18px;">
                    <i class="ph-bold ph-plugs-connected"></i> Uji Koneksi
                </button>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="adm-btn adm-btn-white" onclick="closeCloudinaryModal()">Tutup</button>
                    <button type="submit" class="adm-btn adm-btn-navy" style="font-weight: 800; padding: 11px 24px;">
                        <i class="ph-bold ph-floppy-disk"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- ===================================================================== -->
<!-- LIGHTBOX PREVIEW -->
<!-- ===================================================================== -->
<div id="fullPreviewModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.88); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;" onclick="closePreviewModal()">
    <div style="position: relative; max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; align-items: center;" onclick="event.stopPropagation()">
        <button type="button" onclick="closePreviewModal()" style="position: absolute; top: -45px; right: 0; background: #fff; border: 2px solid #000; border-radius: 50%; width: 36px; height: 36px; font-size: 1.25rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 2px 2px 0px #000;">&times;</button>
        <img id="fullPreviewImg" src="" alt="Full Preview" style="max-width: 100%; max-height: 80vh; border-radius: 8px; border: 3px solid #000; box-shadow: 6px 6px 0px #000; object-fit: contain; background: #000;">
        <div id="fullPreviewTitle" style="color: #fff; font-family: var(--font-heading); font-size: 1.05rem; font-weight: 800; margin-top: 14px; text-align: center; text-shadow: 1px 1px 3px #000;"></div>
    </div>
</div>

<script>
// ─── Kategori Label Map (untuk card inject) ───
const kategoriLabels = <?php echo json_encode($kategoriList); ?>;

// ─── Modal Helpers ───
function openUploadModal() {
    document.getElementById('modalUpload').style.display = 'flex';
}
function closeUploadModal() {
    document.getElementById('modalUpload').style.display = 'none';
    // Reset form
    document.getElementById('formUploadFoto').reset();
    resetFilePicker();
    document.getElementById('custom-kategori-wrap').style.display = 'none';
}

function openEditModal(foto) {
    document.getElementById('edit_id').value = foto.id;
    document.getElementById('edit_judul').value = foto.judul;
    document.getElementById('edit_kategori_select').value = foto.kategori;
    document.getElementById('edit_deskripsi').value = foto.deskripsi || '';
    document.getElementById('edit_urutan').value = foto.urutan || 0;
    document.getElementById('edit_tampilkan_beranda').checked = (parseInt(foto.tampilkan_beranda) === 1);
    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

function openCloudinaryModal() {
    document.getElementById('modalCloudinary').style.display = 'flex';
}
function closeCloudinaryModal() {
    document.getElementById('modalCloudinary').style.display = 'none';
}

function testCloudinaryConnection() {
    const cloudName = document.getElementById('cloud_name').value.trim();
    const apiKey    = document.getElementById('api_key').value.trim();
    const apiSecret = document.getElementById('api_secret').value.trim();

    if (!cloudName || !apiKey || !apiSecret) {
        Swal.fire({
            icon: 'warning',
            title: 'Kredensial Belum Lengkap',
            text: 'Harap isi Cloud Name, API Key, dan API Secret sebelum menguji koneksi.'
        });
        return;
    }

    Swal.fire({
        title: 'Menguji Koneksi Cloudinary...',
        html: '<div style="display:flex; justify-content:center; padding:15px;"><i class="ph-bold ph-spinner" style="font-size:2rem; animation:spin 1s linear infinite;"></i></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    const fd = new FormData();
    fd.append('action', 'test_cloudinary');
    fd.append('cloud_name', cloudName);
    fd.append('api_key', apiKey);
    fd.append('api_secret', apiSecret);

    fetch('galeri.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Koneksi Berhasil!' : 'Koneksi Gagal',
            text: data.message
        });
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menghubungi Server',
            text: 'Terjadi kesalahan saat memproses pengujian koneksi.'
        });
    });
}

// ─── Helper: Toast Notification ───
function showToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 2500, timerProgressBar: true
    });
    Toast.fire({ icon, title });
}

// ─── Helper: Format tanggal ───
function formatTanggal(dateStr) {
    const d = new Date(dateStr);
    const bln = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return d.getDate() + ' ' + bln[d.getMonth()] + ' ' + d.getFullYear();
}

// ─── Build Photo Card HTML (utk inject ke grid) ───
function buildPhotoCard(f, isCloudinary) {
    const isBeranda = parseInt(f.tampilkan_beranda) === 1;
    const imgSrc = /^https?:\/\//.test(f.file_path) ? f.file_path : '../../' + f.file_path.replace(/^\//, '');
    const katLabel = kategoriLabels[f.kategori] || (f.kategori.charAt(0).toUpperCase() + f.kategori.slice(1));
    const fJson = JSON.stringify(f).replace(/"/g, '&quot;');
    return `
    <div class="adm-card photo-card" id="card-foto-${f.id}" style="display: flex; flex-direction: column; overflow: hidden; padding: 0; transition: transform 0.2s, box-shadow 0.2s; position: relative; opacity: 0; transform: scale(0.9);">
        <div style="position: relative; width: 100%; height: 210px; background: #053B50; overflow: hidden; border-bottom: var(--border-thick);">
            <img src="${imgSrc}" alt="${f.judul}" loading="lazy"
                 style="width: 100%; height: 100%; object-fit: cover; cursor: pointer; transition: transform 0.3s ease;"
                 onclick="previewFullImage('${imgSrc}', '${f.judul.replace(/'/g, "&#39;")}')"
                 onmouseover="this.style.transform='scale(1.06)'"
                 onmouseout="this.style.transform='scale(1)'">
            <span style="position: absolute; top: 12px; left: 12px; background: #0A4D68; color: #fff; font-size: 0.72rem; font-weight: 800; padding: 4px 10px; border-radius: 6px; border: 1.5px solid var(--adm-black); box-shadow: 2px 2px 0px var(--adm-black); text-transform: uppercase;">
                ${katLabel}
            </span>
            <span style="position: absolute; top: 12px; right: 12px; background: ${isCloudinary ? '#10B981' : '#F59E0B'}; color: #fff; font-size: 0.7rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; border: 1.5px solid var(--adm-black); display: flex; align-items: center; gap: 4px; box-shadow: 2px 2px 0px var(--adm-black);">
                <i class="ph-bold ${isCloudinary ? 'ph-cloud-check' : 'ph-hard-drive'}"></i> ${isCloudinary ? 'Cloud' : 'Lokal'}
            </span>
            <div id="beranda-ribbon-${f.id}" style="display: ${isBeranda ? 'flex' : 'none'}; position: absolute; bottom: 10px; left: 12px; background: var(--adm-yellow); color: var(--adm-black); font-size: 0.72rem; font-weight: 800; padding: 4px 9px; border-radius: 6px; border: 1.5px solid var(--adm-black); box-shadow: 2px 2px 0px var(--adm-black); align-items: center; gap: 4px;">
                <i class="ph-bold ph-star-fill"></i> TAMPIL DI BERANDA
            </div>
        </div>
        <div style="padding: 16px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h4 id="title-foto-${f.id}" style="font-family: var(--font-heading); font-size: 1.02rem; font-weight: 800; color: var(--adm-black); margin-bottom: 6px; line-height: 1.35;">${f.judul}</h4>
                ${f.deskripsi
                    ? `<p id="desc-foto-${f.id}" style="color: #64748B; font-size: 0.82rem; margin-bottom: 12px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${f.deskripsi}</p>`
                    : `<div id="desc-foto-${f.id}" style="font-size: 0.76rem; color: #94A3B8; margin-bottom: 12px; display: flex; align-items: center; gap: 4px;"><i class="ph-bold ph-calendar"></i> ${formatTanggal(f.created_at)}</div>`
                }
            </div>
            <div style="padding-top: 14px; border-top: 1.5px dashed #CBD5E1; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                <button type="button" class="adm-btn btn-toggle-beranda" id="btn-toggle-${f.id}"
                        onclick="toggleBerandaAjax(${f.id})"
                        style="font-size: 0.78rem; font-weight: 800; padding: 7px 12px; display: inline-flex; align-items: center; gap: 6px; background: ${isBeranda ? 'var(--adm-yellow)' : '#F1F5F9'}; color: var(--adm-black);">
                    <i class="ph-bold ${isBeranda ? 'ph-check-circle' : 'ph-circle'}" id="icon-toggle-${f.id}" style="font-size: 1rem;"></i>
                    <span id="text-toggle-${f.id}">${isBeranda ? 'Di Beranda' : '+ Pasang di Beranda'}</span>
                </button>
                <div style="display: flex; gap: 6px;">
                    <button type="button" class="adm-btn adm-btn-white" style="padding: 7px 10px; font-size: 0.9rem;" title="Ubah Info Foto"
                            onclick="openEditModal(${fJson})">
                        <i class="ph-bold ph-pencil-simple"></i>
                    </button>
                    <button type="button" class="adm-btn" style="padding: 7px 10px; font-size: 0.9rem; background: #FEE2E2; color: #DC2626; border-color: var(--adm-black);" title="Hapus Foto"
                            onclick="confirmDeletePhoto(${f.id}, '${f.judul.replace(/'/g, "&#39;")}')">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>`;
}

// ─── AJAX Upload Form ───
document.addEventListener('DOMContentLoaded', function() {
    const formUpload = document.getElementById('formUploadFoto');
    if (formUpload) {
        formUpload.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = formUpload.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph-bold ph-spinner" style="animation: spin 1s linear infinite;"></i> Mengunggah...';

            const fd = new FormData(formUpload);
            fetch('galeri.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ph-bold ph-upload-simple" style="font-size: 1.15rem;"></i> Unggah Sekarang';

                if (data.success) {
                    closeUploadModal();
                    showToast('success', data.message || 'Foto berhasil ditambahkan!');

                    // Inject card baru ke grid
                    const emptyState = document.getElementById('empty-galeri-state');
                    if (emptyState) { location.reload(); return; }

                    const grid = document.getElementById('galeri-grid');
                    if (grid) {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = buildPhotoCard(data.foto, data.is_cloudinary).trim();
                        const newCard = tempDiv.firstElementChild;
                        grid.prepend(newCard);
                        // Animate in
                        requestAnimationFrame(() => {
                            newCard.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
                            newCard.style.opacity = '1';
                            newCard.style.transform = 'scale(1)';
                        });

                        // Update stat total
                        const statTot = document.querySelector('.adm-stat-val');
                        if (statTot) statTot.textContent = parseInt(statTot.textContent || 0) + 1;
                    } else {
                        location.reload();
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan.' });
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ph-bold ph-upload-simple" style="font-size: 1.15rem;"></i> Unggah Sekarang';
                Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: 'Tidak dapat menghubungi server.' });
            });
        });
    }

    // ─── AJAX Edit Form ───
    const formEdit = document.getElementById('formEditFoto');
    if (formEdit) {
        formEdit.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = formEdit.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph-bold ph-spinner"></i> Menyimpan...';

            const fd = new FormData(formEdit);
            fetch('galeri.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ph-bold ph-check"></i> Simpan Perubahan';

                if (data.success) {
                    closeEditModal();
                    showToast('success', data.message || 'Data berhasil diperbarui!');

                    const f = data.foto;
                    const fId = f.id;

                    // Update judul
                    const titleEl = document.getElementById('title-foto-' + fId);
                    if (titleEl) titleEl.textContent = f.judul;

                    // Update deskripsi
                    const descEl = document.getElementById('desc-foto-' + fId);
                    if (descEl) {
                        if (f.deskripsi) {
                            descEl.textContent = f.deskripsi;
                            descEl.style.webkitLineClamp = 2;
                        } else {
                            descEl.innerHTML = '<i class="ph-bold ph-calendar"></i> ' + formatTanggal(f.created_at);
                        }
                    }

                    // Update status beranda (kalau berubah)
                    const isBeranda = parseInt(f.tampilkan_beranda) === 1;
                    const btn = document.getElementById('btn-toggle-' + fId);
                    const icon = document.getElementById('icon-toggle-' + fId);
                    const text = document.getElementById('text-toggle-' + fId);
                    const ribbon = document.getElementById('beranda-ribbon-' + fId);
                    if (btn) btn.style.background = isBeranda ? 'var(--adm-yellow)' : '#F1F5F9';
                    if (icon) icon.className = 'ph-bold ' + (isBeranda ? 'ph-check-circle' : 'ph-circle');
                    if (text) text.textContent = isBeranda ? 'Di Beranda' : '+ Pasang di Beranda';
                    if (ribbon) ribbon.style.display = isBeranda ? 'flex' : 'none';

                    // Update data di tombol edit (supaya konsisten kalau dibuka lagi)
                    const editBtn = document.querySelector(`#card-foto-${fId} button[onclick^="openEditModal"]`);
                    if (editBtn) {
                        editBtn.setAttribute('onclick', `openEditModal(${JSON.stringify(f).replace(/"/g, '&quot;')})`);
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan.' });
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ph-bold ph-check"></i> Simpan Perubahan';
                Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: 'Tidak dapat menghubungi server.' });
            });
        });
    }
});

function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ph-bold ph-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'ph-bold ph-eye';
    }
}

// Live Image Preview with File Details
function previewSelectedImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('preview-filename').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            document.getElementById('image-preview-box').style.display = 'block';
            document.getElementById('dropzone').style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
}

function resetFilePicker(e) {
    if (e) e.stopPropagation();
    document.getElementById('file_foto').value = '';
    document.getElementById('image-preview-box').style.display = 'none';
    document.getElementById('dropzone').style.display = 'block';
}

// Drag & Drop Highlight Effects
const dropzone = document.getElementById('dropzone');
if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.background = '#FEF9C3';
            dropzone.style.borderColor = '#0A4D68';
        }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.background = '#F8FAFC';
            dropzone.style.borderColor = 'var(--adm-black)';
        }, false);
    });
    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            document.getElementById('file_foto').files = files;
            previewSelectedImage(document.getElementById('file_foto'));
        }
    }, false);
}

function toggleCustomCategory(val) {
    const wrap = document.getElementById('custom-kategori-wrap');
    if (val === '_custom') {
        wrap.style.display = 'block';
        document.getElementById('kategori_custom').focus();
    } else {
        wrap.style.display = 'none';
    }
}

// Preview Full Image
function previewFullImage(src, title) {
    document.getElementById('fullPreviewImg').src = src;
    document.getElementById('fullPreviewTitle').textContent = title;
    document.getElementById('fullPreviewModal').style.display = 'flex';
}
function closePreviewModal() {
    document.getElementById('fullPreviewModal').style.display = 'none';
}

// Fast AJAX Toggle Beranda
function toggleBerandaAjax(fotoId) {
    const btn = document.getElementById('btn-toggle-' + fotoId);
    const icon = document.getElementById('icon-toggle-' + fotoId);
    const text = document.getElementById('text-toggle-' + fotoId);
    const ribbon = document.getElementById('beranda-ribbon-' + fotoId);
    const statBeranda = document.getElementById('stat-beranda-val');

    btn.style.opacity = '0.6';

    const formData = new FormData();
    formData.append('action', 'toggle_beranda');
    formData.append('id', fotoId);

    fetch('galeri.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.style.opacity = '1';
        if (data.success) {
            if (data.status === 1) {
                btn.style.background = 'var(--adm-yellow)';
                icon.className = 'ph-bold ph-check-circle';
                text.textContent = 'Di Beranda';
                if (ribbon) ribbon.style.display = 'flex';
            } else {
                btn.style.background = '#F1F5F9';
                icon.className = 'ph-bold ph-circle';
                text.textContent = '+ Pasang di Beranda';
                if (ribbon) ribbon.style.display = 'none';
            }

            if (statBeranda && data.beranda_count !== undefined) {
                statBeranda.textContent = data.beranda_count;
            }

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: data.message
            });
        }
    })
    .catch(err => {
        btn.style.opacity = '1';
        console.error(err);
    });
}

// SweetAlert Delete Confirmation
function confirmDeletePhoto(fotoId, judul) {
    Swal.fire({
        title: 'Hapus Foto Ini?',
        text: 'Foto "' + judul + '" akan dihapus secara permanen dari galeri.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_foto');
            formData.append('id', fotoId);

            fetch('galeri.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById('card-foto-' + fotoId);
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';
                        setTimeout(() => card.remove(), 300);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Foto berhasil dihapus.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            })
            .catch(err => {
                console.error(err);
                const f = document.createElement('form');
                f.method = 'POST';
                f.action = 'galeri.php';
                const a = document.createElement('input');
                a.type = 'hidden';
                a.name = 'action';
                a.value = 'delete_foto';
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'id';
                i.value = fotoId;
                f.appendChild(a);
                f.appendChild(i);
                document.body.appendChild(f);
                f.submit();
            });
        }
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
