<?php
// spmb/admin/berita.php
// Manajemen Berita Sekolah SMKS SUKAPURA
// Pengaturan & Publikasi Berita, Prestasi, Kegiatan, & Warta Vokasi

require_once __DIR__ . '/../config.php';
check_admin_login();
$pdo = get_db_connection();

// Path upload khusus gambar berita
$uploadDir = dirname(__DIR__, 2) . '/assets/berita/uploads/';
if (!file_exists($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

// Flash Message Helper
if (!isset($_SESSION['flash_msg'])) {
    $_SESSION['flash_msg'] = null;
}

// ============================================================================
// PROSES POST ACTIONS
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TOGGLE STATUS AKTIF (Diterbitkan vs Draf)
    if ($action === 'toggle_aktif') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newStatus = toggle_berita_aktif($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'is_aktif' => $newStatus,
                    'message'  => $newStatus ? 'Berita berhasil diterbitkan ke publik.' : 'Berita dialihkan ke draf (disembunyikan).'
                ]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Status Diperbarui',
                'text'  => $newStatus ? 'Berita kini diterbitkan dan tampil di halaman publik.' : 'Berita dialihkan ke draf.'
            ];
        }
        header("Location: berita.php");
        exit;
    }

    // 2. TOGGLE BERITA UTAMA (Featured)
    if ($action === 'toggle_featured') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newFeat = toggle_berita_featured($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'     => true,
                    'is_featured' => $newFeat,
                    'message'     => $newFeat ? 'Berita dijadikan sebagai Berita Utama paling atas!' : 'Status Berita Utama dicopot.'
                ]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Berita Utama Diperbarui',
                'text'  => $newFeat ? 'Berita kini disematkan sebagai Berita Utama paling atas di halaman Berita.' : 'Status Berita Utama dinonaktifkan.'
            ];
        }
        header("Location: berita.php");
        exit;
    }

    // 3. TAMBAH BERITA BARU
    if ($action === 'tambah') {
        $judul      = trim($_POST['judul'] ?? '');
        $kategori   = strtoupper(trim($_POST['kategori'] ?? 'UMUM'));
        $tanggal    = trim($_POST['tanggal'] ?? '');
        $penulis    = trim($_POST['penulis'] ?? 'Humas SMKS Sukapura');
        $ringkasan  = trim($_POST['ringkasan'] ?? '');
        $kontenRaw  = trim($_POST['konten'] ?? '');
        $warna      = trim($_POST['warna'] ?? 'card-yellow');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isAktif    = isset($_POST['is_aktif']) ? 1 : 0;
        $urutan     = (int)($_POST['urutan'] ?? 0);

        if (empty($tanggal)) {
            $tanggal = format_tanggal_indonesia(date('Y-m-d'), false);
        }

        if (empty($judul) || empty($ringkasan) || empty($kontenRaw)) {
            $_SESSION['flash_msg'] = [
                'type'  => 'error',
                'title' => 'Form Belum Lengkap',
                'text'  => 'Judul, ringkasan, dan isi konten berita wajib diisi.'
            ];
            header("Location: berita.php");
            exit;
        }

        // Split multi-line konten into array of paragraphs
        $paragraphs = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $kontenRaw))));
        if (empty($paragraphs)) {
            $paragraphs = [$kontenRaw];
        }

        // Upload gambar sampul
        $gambarPath = 'assets/berita/1.webp';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['gambar']['tmp_name'];
            $origName = $_FILES['gambar']['name'];
            $fileExt  = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExt, $allowed)) {
                $c = function_exists('get_cloudinary_config') ? get_cloudinary_config() : ['configured' => false];
                if ($c['configured']) {
                    $upCloud = upload_to_cloudinary($tmpName, null, 'smk_sukapura/berita');
                    if (!empty($upCloud['success']) && !empty($upCloud['url'])) {
                        $gambarPath = $upCloud['url'];
                    }
                }

                if ($gambarPath === 'assets/berita/1.webp') {
                    $newImgName = 'berita_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                    $destPath   = $uploadDir . $newImgName;
                    if (move_uploaded_file($tmpName, $destPath)) {
                        $gambarPath = 'assets/berita/uploads/' . $newImgName;
                    }
                }
            }
        }

        add_berita([
            'judul'       => $judul,
            'kategori'    => $kategori,
            'tanggal'     => $tanggal,
            'penulis'     => $penulis,
            'ringkasan'   => $ringkasan,
            'konten'      => $paragraphs,
            'gambar'      => $gambarPath,
            'warna'       => $warna,
            'is_featured' => $isFeatured,
            'is_aktif'    => $isAktif,
            'urutan'      => $urutan
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Berita Berhasil Diterbitkan!',
            'text'  => 'Artikel berita baru telah berhasil ditambahkan dan ' . ($isAktif ? 'langsung tayang di publik.' : 'tersimpan sebagai draf.')
        ];
        header("Location: berita.php");
        exit;
    }

    // 4. EDIT BERITA
    if ($action === 'edit') {
        $id   = (int)($_POST['id'] ?? 0);
        $curr = get_berita_by_id($id);

        if (!$curr) {
            $_SESSION['flash_msg'] = [
                'type'  => 'error',
                'title' => 'Berita Tidak Ditemukan',
                'text'  => 'Data berita yang ingin diedit tidak tersedia.'
            ];
            header("Location: berita.php");
            exit;
        }

        $judul      = trim($_POST['judul'] ?? '');
        $kategori   = strtoupper(trim($_POST['kategori'] ?? 'UMUM'));
        $tanggal    = trim($_POST['tanggal'] ?? $curr['tanggal']);
        $penulis    = trim($_POST['penulis'] ?? $curr['penulis']);
        $ringkasan  = trim($_POST['ringkasan'] ?? '');
        $kontenRaw  = trim($_POST['konten'] ?? '');
        $warna      = trim($_POST['warna'] ?? $curr['warna']);
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isAktif    = isset($_POST['is_aktif']) ? 1 : 0;
        $urutan     = (int)($_POST['urutan'] ?? $curr['urutan']);

        if (empty($judul) || empty($ringkasan) || empty($kontenRaw)) {
            $_SESSION['flash_msg'] = [
                'type'  => 'error',
                'title' => 'Form Belum Lengkap',
                'text'  => 'Judul, ringkasan, dan isi konten berita wajib diisi.'
            ];
            header("Location: berita.php");
            exit;
        }

        $paragraphs = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $kontenRaw))));
        if (empty($paragraphs)) {
            $paragraphs = [$kontenRaw];
        }

        $gambarPath = $curr['gambar'];
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['gambar']['tmp_name'];
            $origName = $_FILES['gambar']['name'];
            $fileExt  = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExt, $allowed)) {
                $c = function_exists('get_cloudinary_config') ? get_cloudinary_config() : ['configured' => false];
                $uploaded = false;
                if ($c['configured']) {
                    $upCloud = upload_to_cloudinary($tmpName, null, 'smk_sukapura/berita');
                    if (!empty($upCloud['success']) && !empty($upCloud['url'])) {
                        $gambarPath = $upCloud['url'];
                        $uploaded = true;
                    }
                }

                if (!$uploaded) {
                    $newImgName = 'berita_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                    $destPath   = $uploadDir . $newImgName;
                    if (move_uploaded_file($tmpName, $destPath)) {
                        if (!empty($curr['gambar']) && strpos($curr['gambar'], 'assets/berita/uploads/') !== false) {
                            $oldFile = dirname(__DIR__, 2) . '/' . ltrim($curr['gambar'], '/');
                            if (file_exists($oldFile)) @unlink($oldFile);
                        }
                        $gambarPath = 'assets/berita/uploads/' . $newImgName;
                    }
                }
            }
        }

        update_berita($id, [
            'judul'       => $judul,
            'kategori'    => $kategori,
            'tanggal'     => $tanggal,
            'penulis'     => $penulis,
            'ringkasan'   => $ringkasan,
            'konten'      => $paragraphs,
            'gambar'      => $gambarPath,
            'warna'       => $warna,
            'is_featured' => $isFeatured,
            'is_aktif'    => $isAktif,
            'urutan'      => $urutan
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Berita Berhasil Diperbarui!',
            'text'  => 'Perubahan pada artikel berita berhasil disimpan.'
        ];
        header("Location: berita.php");
        exit;
    }

    // 5. HAPUS BERITA
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_berita($id);
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Berita Dihapus',
                'text'  => 'Artikel berita dan berkas gambar telah dihapus dari sistem.'
            ];
        }
        header("Location: berita.php");
        exit;
    }
}

// Filter & Pencarian
$searchFilter   = trim($_GET['q'] ?? '');
$kategoriFilter = trim($_GET['kategori'] ?? '');
$statusFilter   = trim($_GET['status'] ?? '');

$allBerita = get_all_berita(
    !empty($kategoriFilter) && $kategoriFilter !== 'semua' ? $kategoriFilter : null,
    false,
    !empty($searchFilter) ? $searchFilter : null
);

// Jika filter status dipilih
if ($statusFilter === 'aktif') {
    $allBerita = array_filter($allBerita, fn($b) => $b['is_aktif'] == 1);
} elseif ($statusFilter === 'draf') {
    $allBerita = array_filter($allBerita, fn($b) => $b['is_aktif'] == 0);
}

// Statistik Keseluruhan
$totalStats = [
    'total'    => (int)$pdo->query("SELECT COUNT(*) FROM `berita`")->fetchColumn(),
    'aktif'    => (int)$pdo->query("SELECT COUNT(*) FROM `berita` WHERE `is_aktif` = 1")->fetchColumn(),
    'draf'     => (int)$pdo->query("SELECT COUNT(*) FROM `berita` WHERE `is_aktif` = 0")->fetchColumn(),
    'featured' => (int)$pdo->query("SELECT COUNT(*) FROM `berita` WHERE `is_featured` = 1")->fetchColumn(),
];

// Kategori warna badge helper
$kategoriColors = [
    'PRESTASI'         => ['bg' => '#DCFCE7', 'text' => '#166534', 'border' => '#22C55E'],
    'KEGIATAN'         => ['bg' => '#DBEAFE', 'text' => '#1E40AF', 'border' => '#3B82F6'],
    'AKADEMIK'         => ['bg' => '#F3E8FF', 'text' => '#6B21A8', 'border' => '#A855F7'],
    'PENERIMAAN SISWA' => ['bg' => '#FCE7F3', 'text' => '#9D174D', 'border' => '#EC4899'],
    'OLAHRAGA'         => ['bg' => '#FFEDD5', 'text' => '#9A3412', 'border' => '#F97316'],
    'PROGRAM'          => ['bg' => '#FEF9C3', 'text' => '#854D0E', 'border' => '#EAB308'],
    'UMUM'             => ['bg' => '#CCFBF1', 'text' => '#115E59', 'border' => '#14B8A6'],
];

// Flash Message dari session
$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

// Judul header yang pas, proporsional, dan ringkas di Topbar
$adminPageTitle   = 'Kelola Berita Sekolah';
$adminPageHeading = 'Berita Sekolah';
require_once __DIR__ . '/header.php';
?>

<style>
/* Switch Neubrutalism */
.nb-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}
.nb-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.nb-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #CBD5E1;
    border: 2px solid var(--adm-black);
    transition: .2s;
    border-radius: 24px;
}
.nb-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: var(--adm-black);
    transition: .2s;
    border-radius: 50%;
}
.nb-switch input:checked + .nb-slider {
    background-color: var(--adm-yellow);
}
.nb-switch input:checked + .nb-slider:before {
    transform: translateX(20px);
    background-color: var(--adm-black);
}

/* Modal Overlay & Card */
.custom-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.custom-modal-card {
    background: #fff;
    border: 3px solid var(--adm-black);
    border-radius: 16px;
    box-shadow: 10px 10px 0px var(--adm-black);
    width: 100%;
    max-width: 660px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: modalPop 0.25s ease-out;
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.94); }
    to { opacity: 1; transform: scale(1); }
}

.modal-header-neubrutal {
    background: var(--adm-yellow-light);
    border-bottom: 2.5px solid var(--adm-black);
    padding: 16px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-close-btn {
    background: #fff;
    border: 2px solid var(--adm-black);
    border-radius: 8px;
    width: 32px;
    height: 32px;
    font-size: 1.25rem;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 2px 2px 0px var(--adm-black);
    transition: transform 0.1s;
}
.modal-close-btn:hover {
    transform: translate(-1px, -1px);
    box-shadow: 3px 3px 0px var(--adm-black);
}

.table-thumb {
    width: 68px;
    height: 48px;
    object-fit: cover;
    border-radius: 6px;
    border: 1.5px solid var(--adm-black);
    box-shadow: 2px 2px 0px var(--adm-black);
    background: #F1F5F9;
}
.cat-pill {
    display: inline-block;
    padding: 3px 9px;
    font-size: 0.72rem;
    font-weight: 800;
    border-radius: 999px;
    border: 1.5px solid var(--adm-black);
    box-shadow: 1.5px 1.5px 0px var(--adm-black);
    text-transform: uppercase;
    white-space: nowrap;
}

.star-featured-btn {
    background: #fff;
    border: 2px solid var(--adm-black);
    border-radius: 8px;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 2px 2px 0px var(--adm-black);
    transition: all 0.15s ease;
    font-size: 1.1rem;
}
.star-featured-btn.is-active {
    background: var(--adm-yellow);
    color: var(--adm-black);
}
.star-featured-btn:hover {
    transform: translate(-1px, -1px);
    box-shadow: 3px 3px 0px var(--adm-black);
}

.image-preview-box {
    width: 100%;
    max-height: 180px;
    border: 2px dashed #94A3B8;
    border-radius: 8px;
    background: #F8FAFC;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-top: 8px;
}
.image-preview-box img {
    max-width: 100%;
    max-height: 180px;
    object-fit: contain;
}
</style>

<!-- TOP ACTION HEADER -->
<div class="adm-section-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--adm-black); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
            <i class="ph-bold ph-newspaper" style="color: var(--adm-navy);"></i> Kelola Berita Sekolah
        </h2>
        <p style="color: #64748B; font-size: 0.92rem;">Publikasikan artikel kegiatan, prestasi siswa, agenda akademik, dan berita resmi SMKS Sukapura.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="adm-btn adm-btn-yellow" onclick="openTambahModal()" style="font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-plus-circle" style="font-size: 1.25rem;"></i> Tambah Berita Baru
        </button>
        <a href="../../berita/index.php" target="_blank" class="adm-btn adm-btn-white" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;" title="Buka Halaman Berita Publik">
            <i class="ph-bold ph-arrow-square-out"></i> Berita Publik
        </a>
    </div>
</div>

<!-- METRICS CARDS (Standard Neubrutalism) -->
<div class="adm-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
    <!-- Stat 1: Total Berita -->
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: var(--adm-navy-dark);"><?php echo $totalStats['total']; ?></div>
            <div class="adm-stat-title">Total Berita</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #E0F2FE;">
            <i class="ph-bold ph-newspaper" style="color: var(--adm-navy);"></i>
        </div>
    </div>

    <!-- Stat 2: Diterbitkan (Aktif) -->
    <div class="adm-stat-card" style="border: 2.5px solid var(--adm-black); background: #F0FDF4;">
        <div>
            <div class="adm-stat-val" style="color: #15803D;"><?php echo $totalStats['aktif']; ?></div>
            <div class="adm-stat-title" style="font-weight: 800; color: #166534;">Diterbitkan (Publik)</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #DCFCE7; border-color: #16A34A;">
            <i class="ph-bold ph-check-circle" style="color: #15803D;"></i>
        </div>
    </div>

    <!-- Stat 3: Draf (Sembunyi) -->
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #DC2626;"><?php echo $totalStats['draf']; ?></div>
            <div class="adm-stat-title">Draf (Sembunyi)</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #FEE2E2;">
            <i class="ph-bold ph-eye-slash" style="color: #DC2626;"></i>
        </div>
    </div>

    <!-- Stat 4: Berita Utama -->
    <div class="adm-stat-card" style="background: #FFFDF0;">
        <div>
            <div class="adm-stat-val" style="color: #B45309;"><?php echo $totalStats['featured']; ?></div>
            <div class="adm-stat-title" style="font-weight: 800; color: #B45309;">Berita Utama</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: var(--adm-yellow);">
            <i class="ph-bold ph-star" style="color: var(--adm-black);"></i>
        </div>
    </div>
</div>

<!-- FILTER & PENCARIAN -->
<div class="adm-card" style="margin-bottom: 24px;">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-funnel"></i> Filter &amp; Pencarian Berita
        </h3>
        <?php if (!empty($searchFilter) || !empty($kategoriFilter) || !empty($statusFilter)): ?>
            <a href="berita.php" class="adm-btn adm-btn-sm adm-btn-white" style="display: inline-flex; align-items: center; gap: 4px;">
                <i class="ph-bold ph-arrow-counter-clockwise"></i> Reset Filter
            </a>
        <?php endif; ?>
    </div>
    <div class="adm-card-body" style="padding: 16px 22px;">
        <form method="GET" action="berita.php" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <!-- Kolom Pencarian -->
            <div style="flex: 1; min-width: 240px; position: relative;">
                <i class="ph-bold ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748B;"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchFilter); ?>" placeholder="Cari judul, ringkasan, atau penulis..." class="spmb-input" style="padding-left: 36px; height: 42px; margin-bottom: 0; width: 100%;">
            </div>

            <!-- Filter Kategori -->
            <div style="min-width: 170px;">
                <select name="kategori" class="spmb-input" style="height: 42px; margin-bottom: 0;" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <option value="PRESTASI" <?php echo $kategoriFilter === 'PRESTASI' ? 'selected' : ''; ?>>Prestasi</option>
                    <option value="KEGIATAN" <?php echo $kategoriFilter === 'KEGIATAN' ? 'selected' : ''; ?>>Kegiatan</option>
                    <option value="AKADEMIK" <?php echo $kategoriFilter === 'AKADEMIK' ? 'selected' : ''; ?>>Akademik</option>
                    <option value="PENERIMAAN SISWA" <?php echo $kategoriFilter === 'PENERIMAAN SISWA' ? 'selected' : ''; ?>>Penerimaan Siswa</option>
                    <option value="OLAHRAGA" <?php echo $kategoriFilter === 'OLAHRAGA' ? 'selected' : ''; ?>>Olahraga</option>
                    <option value="PROGRAM" <?php echo $kategoriFilter === 'PROGRAM' ? 'selected' : ''; ?>>Program</option>
                    <option value="UMUM" <?php echo $kategoriFilter === 'UMUM' ? 'selected' : ''; ?>>Umum</option>
                </select>
            </div>

            <!-- Filter Status -->
            <div style="min-width: 140px;">
                <select name="status" class="spmb-input" style="height: 42px; margin-bottom: 0;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?php echo $statusFilter === 'aktif' ? 'selected' : ''; ?>>Diterbitkan</option>
                    <option value="draf" <?php echo $statusFilter === 'draf' ? 'selected' : ''; ?>>Draf (Sembunyi)</option>
                </select>
            </div>

            <button type="submit" class="adm-btn adm-btn-yellow" style="height: 42px; padding: 0 20px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;">
                <i class="ph-bold ph-magnifying-glass"></i> Cari
            </button>
        </form>
    </div>
</div>

<!-- DATA TABLE CARD -->
<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-list-dashes"></i> Daftar Artikel Berita
            <span style="font-size: 0.8rem; background: var(--adm-yellow); color: #000; padding: 2px 8px; border-radius: 10px; border: 1px solid #000; font-weight: 800;"><?php echo count($allBerita); ?></span>
        </h3>
        <div style="font-size: 0.82rem; color: #64748B;">
            Tip: Klik bintang <i class="ph-bold ph-star" style="color: #D97706;"></i> untuk menetapkan <strong>Berita Utama</strong>
        </div>
    </div>

    <?php if (empty($allBerita)): ?>
        <div style="text-align: center; padding: 50px 20px;">
            <div style="width: 56px; height: 56px; background: #FEF9C3; border: 2px solid var(--adm-black); border-radius: 12px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; box-shadow: 2px 2px 0px var(--adm-black);">
                <i class="ph-bold ph-newspaper-clipping"></i>
            </div>
            <h4 style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 800; margin-bottom: 6px;">Tidak Ada Berita Ditemukan</h4>
            <p style="color: #64748B; font-size: 0.88rem; max-width: 400px; margin: 0 auto 16px;">
                Belum ada berita yang cocok dengan kriteria pencarian atau belum ada data tersimpan.
            </p>
            <button type="button" class="adm-btn adm-btn-yellow" onclick="openTambahModal()" style="font-weight: 800;">
                <i class="ph-bold ph-plus-circle"></i> Tambah Berita Baru
            </button>
        </div>
    <?php else: ?>
        <div class="adm-table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">Utama</th>
                        <th style="width: 90px;">Sampul</th>
                        <th>Informasi Berita</th>
                        <th style="width: 140px;">Kategori</th>
                        <th style="width: 130px;">Tanggal</th>
                        <th style="width: 110px; text-align: center;">Status</th>
                        <th style="width: 130px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allBerita as $item): 
                        $katKey = strtoupper($item['kategori']);
                        $c = $kategoriColors[$katKey] ?? ['bg' => '#E2E8F0', 'text' => '#1E293B', 'border' => '#94A3B8'];
                        
                        $thumbSrc = $item['gambar'];
                        if (!preg_match('/^https?:\/\//', $thumbSrc)) {
                            $thumbSrc = '../../' . ltrim($thumbSrc, '/');
                        }
                    ?>
                        <tr style="<?php echo $item['is_featured'] == 1 ? 'background: #FFFDF0;' : ''; ?>">
                            <!-- TOGGLE FEATURED / UTAMA -->
                            <td style="text-align: center;">
                                <form method="POST" action="berita.php" style="display: inline;" id="form-feat-<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="toggle_featured">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="button" class="star-featured-btn <?php echo $item['is_featured'] == 1 ? 'is-active' : ''; ?>" title="<?php echo $item['is_featured'] == 1 ? 'Berita Utama Aktif (Klik untuk copot)' : 'Klik untuk jadikan Berita Utama'; ?>" onclick="handleToggleFeatured(<?php echo $item['id']; ?>, this)">
                                        <i class="<?php echo $item['is_featured'] == 1 ? 'ph-fill ph-star' : 'ph-bold ph-star'; ?>"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- THUMBNAIL IMAGE -->
                            <td>
                                <img src="<?php echo htmlspecialchars($thumbSrc); ?>" alt="Sampul" class="table-thumb" onerror="this.src='../../assets/berita/1.webp';">
                            </td>

                            <!-- JUDUL & RINGKASAN -->
                            <td>
                                <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                                    <span><?php echo htmlspecialchars($item['judul']); ?></span>
                                    <?php if ($item['is_featured'] == 1): ?>
                                        <span style="background: var(--adm-yellow); color: #000; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 6px; border: 1px solid #000; display: inline-flex; align-items: center; gap: 3px;">
                                            <i class="ph-fill ph-star"></i> UTAMA
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div style="color: #64748B; font-size: 0.83rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 4px;">
                                    <?php echo htmlspecialchars($item['ringkasan']); ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #94A3B8;">
                                    <i class="ph-bold ph-user-circle"></i> <?php echo htmlspecialchars($item['penulis']); ?>
                                </div>
                            </td>

                            <!-- KATEGORI -->
                            <td>
                                <span class="cat-pill" style="background: <?php echo $c['bg']; ?>; color: <?php echo $c['text']; ?>; border-color: <?php echo $c['border']; ?>;">
                                    <?php echo htmlspecialchars($item['kategori']); ?>
                                </span>
                            </td>

                            <!-- TANGGAL -->
                            <td style="font-size: 0.85rem; color: #475569; white-space: nowrap;">
                                <i class="ph-bold ph-calendar-blank"></i> <?php echo htmlspecialchars($item['tanggal']); ?>
                            </td>

                            <!-- TOGGLE STATUS AKTIF -->
                            <td style="text-align: center;">
                                <label class="nb-switch" title="<?php echo $item['is_aktif'] == 1 ? 'Berita Diterbitkan' : 'Berita Draf (Disembunyikan)'; ?>">
                                    <input type="checkbox" <?php echo $item['is_aktif'] == 1 ? 'checked' : ''; ?> onchange="handleToggleAktif(<?php echo $item['id']; ?>, this)">
                                    <span class="nb-slider"></span>
                                </label>
                                <div id="status-label-<?php echo $item['id']; ?>" style="font-size: 0.72rem; font-weight: 800; margin-top: 2px; color: <?php echo $item['is_aktif'] == 1 ? '#16A34A' : '#94A3B8'; ?>;">
                                    <?php echo $item['is_aktif'] == 1 ? 'PUBLIK' : 'DRAF'; ?>
                                </div>
                            </td>

                            <!-- AKSI -->
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="../../berita/detail.php?id=<?php echo $item['id']; ?>" target="_blank" class="adm-btn adm-btn-sm adm-btn-white" style="padding: 6px 9px;" title="Lihat di Web">
                                        <i class="ph-bold ph-arrow-square-out"></i>
                                    </a>
                                    <button type="button" class="adm-btn adm-btn-sm adm-btn-yellow" title="Edit Berita" onclick='openEditModal(<?php echo json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>
                                    <form method="POST" action="berita.php" onsubmit="return confirmHapusBerita(event, this, '<?php echo addslashes($item['judul']); ?>');" style="display: inline;">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="adm-btn adm-btn-sm adm-btn-danger" title="Hapus Berita">
                                            <i class="ph-bold ph-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- =========================================================================
     MODAL: TAMBAH BERITA BARU
     ========================================================================= -->
<div id="modalTambah" class="custom-modal-overlay" onclick="if(event.target===this) closeTambahModal();">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; background: #FFE600; border: 2px solid var(--adm-black); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; box-shadow: 2px 2px 0px var(--adm-black);">
                    <i class="ph-bold ph-plus-circle"></i>
                </div>
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0;">Tambah Berita Baru</h3>
                    <div style="font-size: 0.75rem; color: #64748B;">Publikasikan artikel, kegiatan, atau prestasi sekolah baru</div>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeTambahModal()">&times;</button>
        </div>

        <form method="POST" action="berita.php" enctype="multipart/form-data" style="padding: 22px;">
            <input type="hidden" name="action" value="tambah">

            <div style="margin-bottom: 14px;">
                <label class="spmb-label">Judul Berita <span style="color: #DC2626;">*</span></label>
                <input type="text" name="judul" class="spmb-input" placeholder="Contoh: Siswa SMKS Sukapura Raih Juara 1 LKS..." required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label class="spmb-label">Kategori <span style="color: #DC2626;">*</span></label>
                    <select name="kategori" class="spmb-input" required>
                        <option value="PRESTASI">PRESTASI</option>
                        <option value="KEGIATAN" selected>KEGIATAN</option>
                        <option value="AKADEMIK">AKADEMIK</option>
                        <option value="PENERIMAAN SISWA">PENERIMAAN SISWA</option>
                        <option value="OLAHRAGA">OLAHRAGA</option>
                        <option value="PROGRAM">PROGRAM</option>
                        <option value="UMUM">UMUM</option>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Tanggal Publikasi</label>
                    <input type="text" name="tanggal" class="spmb-input" value="<?php echo format_tanggal_indonesia(date('Y-m-d'), false); ?>" placeholder="Contoh: 16 Oktober 2026">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label class="spmb-label">Penulis / Humas</label>
                    <input type="text" name="penulis" class="spmb-input" value="Humas SMKS Sukapura" placeholder="Contoh: Tim Humas / Guru Pembimbing">
                </div>
                <div>
                    <label class="spmb-label">Aksen Warna Kartu</label>
                    <select name="warna" class="spmb-input">
                        <option value="card-yellow">Kuning Emas (card-yellow)</option>
                        <option value="card-blue">Biru Langit (card-blue)</option>
                        <option value="card-green">Hijau Segar (card-green)</option>
                        <option value="card-pink">Pink Ceria (card-pink)</option>
                        <option value="card-purple">Ungu Modern (card-purple)</option>
                        <option value="card-orange">Oranye Hangat (card-orange)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="spmb-label">Foto / Gambar Sampul Berita</label>
                <input type="file" name="gambar" class="spmb-input" accept="image/jpeg,image/png,image/webp" onchange="previewUploadImage(this, 'tambah_img_preview')">
                <div style="font-size: 0.75rem; color: #64748B; margin-top: 3px;">Format: JPG, PNG, WEBP (Maks. 5MB). Jika dikosongkan, gambar bawaan akan digunakan.</div>
                <div id="tambah_img_preview" class="image-preview-box" style="display: none;"></div>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="spmb-label">Ringkasan Singkat (Lead / Excerpt) <span style="color: #DC2626;">*</span></label>
                <textarea name="ringkasan" class="spmb-input" rows="2" placeholder="Ringkasan 1-2 kalimat yang menarik untuk ditampilkan pada kartu berita..." required></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="spmb-label">Konten Lengkap Berita <span style="color: #DC2626;">*</span></label>
                <textarea name="konten" class="spmb-input" rows="7" placeholder="Tuliskan isi berita selengkapnya di sini...&#10;&#10;Pisahkan setiap paragraf dengan menekan tombol Enter 2 kali (baris baru kosong)." required></textarea>
                <div style="font-size: 0.75rem; color: #64748B; margin-top: 3px;">Pisahkan paragraf dengan baris baru ganda (Enter 2x) agar paragraf terbentuk rapi secara otomatis.</div>
            </div>

            <div style="background: #F8FAFC; border: 1.5px solid var(--adm-black); border-radius: 8px; padding: 12px; margin-bottom: 18px; display: flex; flex-direction: column; gap: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; color: #0F172A;">
                    <input type="checkbox" name="is_featured" value="1" style="width: 17px; height: 17px;">
                    <span><i class="ph-bold ph-star" style="color: #D97706;"></i> Jadikan Berita Utama (Featured Card Paling Atas)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer; color: #16A34A;">
                    <input type="checkbox" name="is_aktif" value="1" checked style="width: 17px; height: 17px;">
                    <span><i class="ph-bold ph-check-circle" style="color: #16A34A;"></i> Langsung Terbitkan ke Halaman Berita Publik</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeTambahModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-paper-plane-tilt"></i> Terbitkan Berita</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: EDIT BERITA
     ========================================================================= -->
<div id="modalEdit" class="custom-modal-overlay" onclick="if(event.target===this) closeEditModal();">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; background: var(--adm-yellow); border: 2px solid var(--adm-black); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; box-shadow: 2px 2px 0px var(--adm-black);">
                    <i class="ph-bold ph-pencil-simple"></i>
                </div>
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0;">Edit Berita</h3>
                    <div style="font-size: 0.75rem; color: #64748B;">Perbarui informasi artikel berita yang sudah ada</div>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>

        <form method="POST" action="berita.php" enctype="multipart/form-data" style="padding: 22px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <div style="margin-bottom: 14px;">
                <label class="spmb-label">Judul Berita <span style="color: #DC2626;">*</span></label>
                <input type="text" name="judul" id="edit_judul" class="spmb-input" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label class="spmb-label">Kategori <span style="color: #DC2626;">*</span></label>
                    <select name="kategori" id="edit_kategori" class="spmb-input" required>
                        <option value="PRESTASI">PRESTASI</option>
                        <option value="KEGIATAN">KEGIATAN</option>
                        <option value="AKADEMIK">AKADEMIK</option>
                        <option value="PENERIMAAN SISWA">PENERIMAAN SISWA</option>
                        <option value="OLAHRAGA">OLAHRAGA</option>
                        <option value="PROGRAM">PROGRAM</option>
                        <option value="UMUM">UMUM</option>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Tanggal Publikasi</label>
                    <input type="text" name="tanggal" id="edit_tanggal" class="spmb-input">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label class="spmb-label">Penulis / Humas</label>
                    <input type="text" name="penulis" id="edit_penulis" class="spmb-input">
                </div>
                <div>
                    <label class="spmb-label">Aksen Warna Kartu</label>
                    <select name="warna" id="edit_warna" class="spmb-input">
                        <option value="card-yellow">Kuning Emas (card-yellow)</option>
                        <option value="card-blue">Biru Langit (card-blue)</option>
                        <option value="card-green">Hijau Segar (card-green)</option>
                        <option value="card-pink">Pink Ceria (card-pink)</option>
                        <option value="card-purple">Ungu Modern (card-purple)</option>
                        <option value="card-orange">Oranye Hangat (card-orange)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="spmb-label">Foto / Gambar Sampul (Opsional jika ingin mengganti)</label>
                <input type="file" name="gambar" class="spmb-input" accept="image/jpeg,image/png,image/webp" onchange="previewUploadImage(this, 'edit_img_preview')">
                <div id="edit_current_img_info" style="font-size: 0.8rem; color: #475569; margin-top: 3px;"></div>
                <div id="edit_img_preview" class="image-preview-box" style="display: none;"></div>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="spmb-label">Ringkasan Singkat <span style="color: #DC2626;">*</span></label>
                <textarea name="ringkasan" id="edit_ringkasan" class="spmb-input" rows="2" required></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="spmb-label">Konten Lengkap Berita <span style="color: #DC2626;">*</span></label>
                <textarea name="konten" id="edit_konten" class="spmb-input" rows="7" required></textarea>
                <div style="font-size: 0.75rem; color: #64748B; margin-top: 3px;">Pisahkan paragraf dengan baris baru ganda (Enter 2x).</div>
            </div>

            <div style="background: #F8FAFC; border: 1.5px solid var(--adm-black); border-radius: 8px; padding: 12px; margin-bottom: 18px; display: flex; flex-direction: column; gap: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer;">
                    <input type="checkbox" name="is_featured" id="edit_is_featured" value="1" style="width: 17px; height: 17px;">
                    <span><i class="ph-bold ph-star" style="color: #D97706;"></i> Jadikan Berita Utama (Featured)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer;">
                    <input type="checkbox" name="is_aktif" id="edit_is_aktif" value="1" style="width: 17px; height: 17px;">
                    <span><i class="ph-bold ph-check-circle" style="color: #16A34A;"></i> Diterbitkan ke Publik</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Controls
function openTambahModal() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeTambahModal() {
    document.getElementById('modalTambah').style.display = 'none';
}

function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_judul').value = data.judul;
    document.getElementById('edit_kategori').value = data.kategori;
    document.getElementById('edit_tanggal').value = data.tanggal;
    document.getElementById('edit_penulis').value = data.penulis || 'Humas SMKS Sukapura';
    document.getElementById('edit_warna').value = data.warna || 'card-yellow';
    document.getElementById('edit_ringkasan').value = data.ringkasan;

    let kontenStr = '';
    if (typeof data.konten === 'string') {
        try {
            const parsed = JSON.parse(data.konten);
            if (Array.isArray(parsed)) {
                kontenStr = parsed.join("\n\n");
            } else {
                kontenStr = data.konten;
            }
        } catch (e) {
            kontenStr = data.konten;
        }
    } else if (Array.isArray(data.konten)) {
        kontenStr = data.konten.join("\n\n");
    }
    document.getElementById('edit_konten').value = kontenStr;

    document.getElementById('edit_is_featured').checked = (data.is_featured == 1);
    document.getElementById('edit_is_aktif').checked = (data.is_aktif == 1);

    const imgInfo = document.getElementById('edit_current_img_info');
    const previewBox = document.getElementById('edit_img_preview');
    if (data.gambar) {
        let fullPath = data.gambar.startsWith('http') ? data.gambar : '../../' + data.gambar.replace(/^\//, '');
        imgInfo.innerHTML = `Foto saat ini: <a href="${fullPath}" target="_blank" style="font-weight: 700; color: #0A4D68;">Lihat Gambar</a>`;
        previewBox.style.display = 'flex';
        previewBox.innerHTML = `<img src="${fullPath}" alt="Preview">`;
    } else {
        imgInfo.innerHTML = `<span style="color: #94A3B8;">Belum ada gambar</span>`;
        previewBox.style.display = 'none';
    }

    document.getElementById('modalEdit').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

// Live Image Preview Helper
function previewUploadImage(input, targetId) {
    const box = document.getElementById(targetId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            box.style.display = 'flex';
            box.innerHTML = `<img src="${e.target.result}" alt="Preview Foto">`;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        box.style.display = 'none';
        box.innerHTML = '';
    }
}

// AJAX Toggle Status Aktif
function handleToggleAktif(id, checkbox) {
    const originalChecked = checkbox.checked;
    const formData = new FormData();
    formData.append('action', 'toggle_aktif');
    formData.append('id', id);

    fetch('berita.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            checkbox.checked = (data.is_aktif == 1);
            const label = document.getElementById('status-label-' + id);
            if (label) {
                label.textContent = (data.is_aktif == 1) ? 'PUBLIK' : 'DRAF';
                label.style.color = (data.is_aktif == 1) ? '#16A34A' : '#94A3B8';
            }
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        } else {
            checkbox.checked = !originalChecked;
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat memperbarui status berita.'
            });
        }
    })
    .catch(err => {
        checkbox.checked = !originalChecked;
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Jaringan',
            text: 'Tidak dapat menghubungi server.'
        });
    });
}

// AJAX Toggle Featured
function handleToggleFeatured(id, btn) {
    const formData = new FormData();
    formData.append('action', 'toggle_featured');
    formData.append('id', id);

    fetch('berita.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat mengatur Berita Utama.'
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Jaringan',
            text: 'Tidak dapat menghubungi server.'
        });
    });
}

// Konfirmasi Hapus Berita
function confirmHapusBerita(e, form, judul) {
    if (e) e.preventDefault();
    Swal.fire({
        title: 'Hapus Berita Ini?',
        html: `Apakah Anda yakin ingin menghapus artikel berita:<br><strong>"${judul}"</strong>?<br><span style="color:#DC2626; font-size:0.85rem;">Tindakan ini tidak dapat dibatalkan. Berkas gambar terkait juga akan dihapus.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i class="ph-bold ph-trash"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

// SweetAlert Flash Message
<?php if ($flash): ?>
Swal.fire({
    icon: '<?php echo $flash['type']; ?>',
    title: '<?php echo addslashes($flash['title']); ?>',
    text: '<?php echo addslashes($flash['text']); ?>',
    confirmButtonColor: '#0A4D68'
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
