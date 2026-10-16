<?php
// spmb/admin/pengumuman.php
// Manajemen Pengumuman Sekolah (Bawah Sambutan & Popup Masuk Web)
// SMKS SUKAPURA Kab. Tasikmalaya

require_once __DIR__ . '/../config.php';
check_admin_login();
$pdo = get_db_connection();

// Path upload khusus pengumuman
$uploadDir = dirname(__DIR__, 2) . '/assets/pengumuman/uploads/';
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

    // 1. SIMPAN PENGATURAN POPUP (HANYA SAKLAR, SUMBER PENGUMUMAN & FREKUENSI)
    if ($action === 'save_popup') {
        $aktif    = isset($_POST['popup_pengumuman_aktif']) ? '1' : '0';
        $mode     = trim($_POST['popup_pengumuman_mode'] ?? 'sekali_sehari');
        $sourceId = trim($_POST['popup_pengumuman_id'] ?? 'auto');

        update_popup_pengumuman_config([
            'popup_pengumuman_aktif' => $aktif,
            'popup_pengumuman_mode'  => $mode,
            'popup_pengumuman_id'    => $sourceId
        ]);

        // Jika admin memilih pengumuman spesifik di dropdown, sinkronkan is_popup di tabel
        if ($sourceId !== 'auto' && is_numeric($sourceId)) {
            $pdo->exec("UPDATE `pengumuman` SET `is_popup` = 0");
            $up = $pdo->prepare("UPDATE `pengumuman` SET `is_popup` = 1 WHERE `id` = ?");
            $up->execute([(int)$sourceId]);
        } elseif ($sourceId === 'auto') {
            $pdo->exec("UPDATE `pengumuman` SET `is_popup` = 0");
        }

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Pengaturan Popup Disimpan!',
            'text'  => ($aktif === '1') ? 'Popup pengumuman masuk web aktif dan otomatis mengambil konten dari pengumuman beranda.' : 'Popup pengumuman masuk web telah dinonaktifkan.'
        ];
        header("Location: pengumuman.php?tab=popup");
        exit;
    }

    // 2. TOGGLE AKTIF PENGUMUMAN BERANDA (AJAX / Form)
    if ($action === 'toggle_aktif') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newStatus = toggle_pengumuman_aktif($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'is_aktif' => $newStatus,
                    'message'  => $newStatus ? 'Pengumuman berhasil diaktifkan di Beranda.' : 'Pengumuman berhasil disembunyikan.'
                ]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Status Diperbarui',
                'text'  => $newStatus ? 'Pengumuman kini aktif di bawah sambutan.' : 'Pengumuman disembunyikan.'
            ];
        }
        header("Location: pengumuman.php?tab=beranda");
        exit;
    }

    // 3. TOGGLE PINNED PENGUMUMAN
    if ($action === 'toggle_pinned') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newPin = toggle_pengumuman_pinned($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'   => true,
                    'is_pinned' => $newPin,
                    'message'   => $newPin ? 'Pengumuman disematkan di posisi utama!' : 'Sematkan dicopot.'
                ]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Status Pinned Diperbarui',
                'text'  => $newPin ? 'Pengumuman disematkan ke posisi utama.' : 'Pengumuman diturunkan ke urutan normal.'
            ];
        }
        header("Location: pengumuman.php?tab=beranda");
        exit;
    }

    // 4. TOGGLE JADIKAN POPUP MASUK WEB
    if ($action === 'toggle_popup') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newPopup = toggle_pengumuman_popup($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'is_popup' => $newPopup,
                    'message'  => $newPopup ? 'Pengumuman ini kini dijadikan Popup Masuk Web!' : 'Popup dikembalikan ke mode otomatis.'
                ]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Popup Web Diperbarui',
                'text'  => $newPopup ? 'Pengumuman ini kini otomatis tampil sebagai Popup saat pengunjung masuk web.' : 'Pengumuman tidak lagi dijadikan popup khusus (kembali ke otomatis).'
            ];
        }
        header("Location: pengumuman.php?tab=beranda");
        exit;
    }

    // 5. TAMBAH PENGUMUMAN BARU
    if ($action === 'tambah') {
        $judul      = trim($_POST['judul'] ?? '');
        $kategori   = trim($_POST['kategori'] ?? 'Umum');
        $isi        = trim($_POST['isi'] ?? '');
        $tanggal    = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $linkTautan = trim($_POST['link_tautan'] ?? '');
        $teksTautan = trim($_POST['teks_tautan'] ?? 'Lihat Detail');
        $isPinned   = isset($_POST['is_pinned']) ? 1 : 0;
        $isPopup    = isset($_POST['is_popup']) ? 1 : 0;
        $isAktif    = isset($_POST['is_aktif']) ? 1 : 0;
        $urutan     = (int)($_POST['urutan'] ?? 0);

        if (empty($judul) || empty($isi)) {
            $_SESSION['flash_msg'] = [
                'type'  => 'error',
                'title' => 'Form Belum Lengkap',
                'text'  => 'Judul dan isi pengumuman wajib diisi.'
            ];
            header("Location: pengumuman.php?tab=beranda");
            exit;
        }

        $lampiranPath = null;
        if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['file_lampiran']['tmp_name'];
            $origName = $_FILES['file_lampiran']['name'];
            $fileExt  = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed  = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp', 'zip'];

            if (in_array($fileExt, $allowed)) {
                $newFileName  = 'lampiran_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $destPath     = $uploadDir . $newFileName;
                if (move_uploaded_file($tmpName, $destPath)) {
                    $lampiranPath = 'assets/pengumuman/uploads/' . $newFileName;
                }
            }
        }

        $gambarPath = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['gambar']['tmp_name'];
            $origName = $_FILES['gambar']['name'];
            $fileExt  = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExt, $allowed)) {
                $newImgName   = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $destPath     = $uploadDir . $newImgName;
                if (move_uploaded_file($tmpName, $destPath)) {
                    $gambarPath = 'assets/pengumuman/uploads/' . $newImgName;
                }
            }
        }

        add_pengumuman([
            'judul'         => $judul,
            'kategori'      => $kategori,
            'isi'           => $isi,
            'tanggal'       => $tanggal,
            'file_lampiran' => $lampiranPath,
            'link_tautan'   => $linkTautan,
            'teks_tautan'   => $teksTautan,
            'gambar'        => $gambarPath,
            'is_pinned'     => $isPinned,
            'is_popup'      => $isPopup,
            'is_aktif'      => $isAktif,
            'urutan'        => $urutan
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Pengumuman Berhasil Diterbitkan!',
            'text'  => 'Pengumuman baru telah disimpan dan otomatis tampil di Beranda' . ($isPopup ? ' serta sebagai Popup Masuk Web!' : '.')
        ];
        header("Location: pengumuman.php?tab=beranda");
        exit;
    }

    // 6. EDIT PENGUMUMAN
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $curr = get_pengumuman_by_id($id);

        if (!$curr) {
            $_SESSION['flash_msg'] = [
                'type'  => 'error',
                'title' => 'Pengumuman Tidak Ditemukan',
                'text'  => 'Data pengumuman yang ingin diedit tidak tersedia.'
            ];
            header("Location: pengumuman.php?tab=beranda");
            exit;
        }

        $judul      = trim($_POST['judul'] ?? '');
        $kategori   = trim($_POST['kategori'] ?? 'Umum');
        $isi        = trim($_POST['isi'] ?? '');
        $tanggal    = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $linkTautan = trim($_POST['link_tautan'] ?? '');
        $teksTautan = trim($_POST['teks_tautan'] ?? 'Lihat Detail');
        $isPinned   = isset($_POST['is_pinned']) ? 1 : 0;
        $isPopup    = isset($_POST['is_popup']) ? 1 : 0;
        $isAktif    = isset($_POST['is_aktif']) ? 1 : 0;
        $urutan     = (int)($_POST['urutan'] ?? 0);

        $lampiranPath = $curr['file_lampiran'];
        if (isset($_POST['hapus_lampiran']) && $_POST['hapus_lampiran'] === '1') {
            if (!empty($lampiranPath) && strpos($lampiranPath, 'assets/pengumuman/uploads/') !== false) {
                $f = dirname(__DIR__, 2) . '/' . ltrim($lampiranPath, '/');
                if (file_exists($f)) @unlink($f);
            }
            $lampiranPath = null;
        }

        if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['file_lampiran']['tmp_name'];
            $origName = $_FILES['file_lampiran']['name'];
            $fileExt  = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed  = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp', 'zip'];

            if (in_array($fileExt, $allowed)) {
                $newFileName  = 'lampiran_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $destPath     = $uploadDir . $newFileName;
                if (move_uploaded_file($tmpName, $destPath)) {
                    if (!empty($lampiranPath) && strpos($lampiranPath, 'assets/pengumuman/uploads/') !== false) {
                        $f = dirname(__DIR__, 2) . '/' . ltrim($lampiranPath, '/');
                        if (file_exists($f)) @unlink($f);
                    }
                    $lampiranPath = 'assets/pengumuman/uploads/' . $newFileName;
                }
            }
        }

        $gambarPath = $curr['gambar'];
        if (isset($_POST['hapus_gambar']) && $_POST['hapus_gambar'] === '1') {
            if (!empty($gambarPath) && strpos($gambarPath, 'assets/pengumuman/uploads/') !== false) {
                $g = dirname(__DIR__, 2) . '/' . ltrim($gambarPath, '/');
                if (file_exists($g)) @unlink($g);
            }
            $gambarPath = null;
        }

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['gambar']['tmp_name'];
            $origName = $_FILES['gambar']['name'];
            $fileExt  = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExt, $allowed)) {
                $newImgName   = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $destPath     = $uploadDir . $newImgName;
                if (move_uploaded_file($tmpName, $destPath)) {
                    if (!empty($gambarPath) && strpos($gambarPath, 'assets/pengumuman/uploads/') !== false) {
                        $g = dirname(__DIR__, 2) . '/' . ltrim($gambarPath, '/');
                        if (file_exists($g)) @unlink($g);
                    }
                    $gambarPath = 'assets/pengumuman/uploads/' . $newImgName;
                }
            }
        }

        update_pengumuman($id, [
            'judul'         => $judul,
            'kategori'      => $kategori,
            'isi'           => $isi,
            'tanggal'       => $tanggal,
            'file_lampiran' => $lampiranPath,
            'link_tautan'   => $linkTautan,
            'teks_tautan'   => $teksTautan,
            'gambar'        => $gambarPath,
            'is_pinned'     => $isPinned,
            'is_popup'      => $isPopup,
            'is_aktif'      => $isAktif,
            'urutan'        => $urutan
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Pengumuman Berhasil Diperbarui!',
            'text'  => 'Perubahan pengumuman tersimpan dan langsung disinkronkan ke Beranda serta Popup.'
        ];
        header("Location: pengumuman.php?tab=beranda");
        exit;
    }

    // 7. HAPUS PENGUMUMAN
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_pengumuman($id);
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Pengumuman Dihapus',
                'text'  => 'Pengumuman telah dihapus dari sistem.'
            ];
        }
        header("Location: pengumuman.php?tab=beranda");
        exit;
    }
}

// Ambil Tab Aktif (default: beranda)
$activeTab = $_GET['tab'] ?? 'beranda';
if (!in_array($activeTab, ['beranda', 'popup'])) {
    $activeTab = 'beranda';
}

// Ambil data untuk tampilan
$pengumumanList = get_all_pengumuman();
$popupConfig    = get_popup_pengumuman_config();

$totalPengumuman = count($pengumumanList);
$activeCount     = 0;
$pinnedCount     = 0;
$popupSourceItem = null;

foreach ($pengumumanList as $p) {
    if ($p['is_aktif'] == 1) $activeCount++;
    if ($p['is_pinned'] == 1) $pinnedCount++;
    if ($popupConfig['id'] > 0 && $p['id'] == $popupConfig['id']) {
        $popupSourceItem = $p;
    }
}

// Flash Message dari session
$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

$adminPageTitle   = 'Manajemen Pengumuman & Popup Website';
$adminPageHeading = 'Pengumuman Website &amp; Popup Masuk';
require_once __DIR__ . '/header.php';
?>

<style>
/* Custom Neubrutalism Tab and Cards for Pengumuman */
.peng-tab-nav {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    border-bottom: 2.5px solid var(--adm-black);
    padding-bottom: 12px;
    flex-wrap: wrap;
}
.peng-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    font-family: var(--font-heading);
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--adm-black);
    background: #fff;
    border: 2px solid var(--adm-black);
    border-radius: var(--radius-btn);
    box-shadow: 3px 3px 0px var(--adm-black);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}
.peng-tab-btn:hover {
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px var(--adm-black);
}
.peng-tab-btn.active {
    background: var(--adm-yellow);
    transform: translate(1px, 1px);
    box-shadow: 2px 2px 0px var(--adm-black);
}

/* Stat Cards */
.peng-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.peng-stat-card {
    background: #fff;
    border: 2.5px solid var(--adm-black);
    border-radius: var(--radius-box);
    box-shadow: 4px 4px 0px var(--adm-black);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.peng-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    border: 2px solid var(--adm-black);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

/* Toggle Switch Neubrutalism */
.nb-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
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
    transition: .25s;
    border-radius: 26px;
}
.nb-slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border: 2px solid var(--adm-black);
    transition: .25s;
    border-radius: 50%;
}
input:checked + .nb-slider {
    background-color: #22C55E;
}
input:checked + .nb-slider:before {
    transform: translateX(20px);
}

/* Badge Categories */
.cat-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1.5px solid var(--adm-black);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.cat-badge-spmb { background: #0A4D68; color: #fff; }
.cat-badge-akademik { background: #8B5CF6; color: #fff; }
.cat-badge-kegiatan { background: #F97316; color: #fff; }
.cat-badge-prestasi { background: #FACC15; color: #000; }
.cat-badge-libur { background: #EF4444; color: #fff; }
.cat-badge-umum { background: #E2E8F0; color: #000; }

/* Modal Custom Styles */
.custom-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 16px;
    overflow-y: auto;
}
.custom-modal-card {
    background: #fff;
    border: 3.5px solid var(--adm-black);
    border-radius: 14px;
    box-shadow: 10px 10px 0px var(--adm-black);
    width: 100%;
    max-width: 640px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: modalPop 0.25s ease-out;
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}

/* Popup Live Preview Frame */
.preview-popup-box {
    background: #FFFDF0;
    border: 3px solid #000;
    border-radius: 14px;
    box-shadow: 6px 6px 0px #000;
    overflow: hidden;
    max-width: 100%;
    margin: 0 auto;
}

/* Sync info banner */
.sync-banner {
    background: #EFF6FF;
    border: 2px solid #3B82F6;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 14px;
}

/* Popup Settings Grid and Cards Spacing */

.popup-card-left{
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
@media (max-width: 992px) {
    .popup-config-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
}
.popup-card-left {
    padding: 28px 32px !important;
}

@media (max-width: 640px) {
    .popup-card-left{
        padding: 20px 18px !important;
    }
}
</style>

<!-- TAB NAVIGATION -->
<div class="peng-tab-nav">
    <a href="?tab=beranda" class="peng-tab-btn <?php echo $activeTab === 'beranda' ? 'active' : ''; ?>">
        <i class="ph-bold ph-megaphone-simple"></i>
        <span>Pengumuman Beranda</span>
        <span style="background:<?php echo $activeTab === 'beranda' ? '#000' : '#E2E8F0'; ?>; color:<?php echo $activeTab === 'beranda' ? '#fff' : '#000'; ?>; font-size:0.75rem; padding:2px 7px; border-radius:10px; font-weight:800;"><?php echo $activeCount; ?></span>
    </a>
    <a href="?tab=popup" class="peng-tab-btn <?php echo $activeTab === 'popup' ? 'active' : ''; ?>">
        <i class="ph-bold ph-bell-ringing"></i>
        <span>Pengaturan Popup Masuk Web</span>
        <span style="background:<?php echo $popupConfig['aktif'] ? '#22C55E' : '#EF4444'; ?>; color:#fff; font-size:0.75rem; padding:2px 8px; border-radius:10px; font-weight:800; border:1px solid #000;">
            <?php echo $popupConfig['aktif'] ? 'AKTIF' : 'NONAKTIF'; ?>
        </span>
    </a>
</div>

<!-- =========================================================================
     TAB 1: PENGUMUMAN BERANDA (DI BAWAH SAMBUTAN KEPALA SEKOLAH)
     ========================================================================= -->
<?php if ($activeTab === 'beranda'): ?>

    <!-- STATS ROW -->
    <div class="peng-stat-grid">
        <div class="peng-stat-card">
            <div class="peng-stat-icon" style="background:#FEF9C3; color:#CA8A04;">
                <i class="ph-bold ph-megaphone"></i>
            </div>
            <div>
                <div style="font-size:0.8rem; font-weight:700; color:#64748B;">TOTAL PENGUMUMAN</div>
                <div style="font-family:var(--font-heading); font-size:1.6rem; font-weight:800; line-height:1.1;"><?php echo $totalPengumuman; ?></div>
            </div>
        </div>

        <div class="peng-stat-card">
            <div class="peng-stat-icon" style="background:#DCFCE7; color:#16A34A;">
                <i class="ph-bold ph-check-circle"></i>
            </div>
            <div>
                <div style="font-size:0.8rem; font-weight:700; color:#64748B;">TAMPIL DI BERANDA</div>
                <div style="font-family:var(--font-heading); font-size:1.6rem; font-weight:800; line-height:1.1; color:#16A34A;"><?php echo $activeCount; ?></div>
            </div>
        </div>

        <div class="peng-stat-card">
            <div class="peng-stat-icon" style="background:#FEE2E2; color:#DC2626;">
                <i class="ph-bold ph-push-pin"></i>
            </div>
            <div>
                <div style="font-size:0.8rem; font-weight:700; color:#64748B;">DISEMATKAN (PINNED)</div>
                <div style="font-family:var(--font-heading); font-size:1.6rem; font-weight:800; line-height:1.1;"><?php echo $pinnedCount; ?></div>
            </div>
        </div>

        <div class="peng-stat-card">
            <div class="peng-stat-icon" style="background:#E0F2FE; color:#0284C7;">
                <i class="ph-bold ph-bell-ringing"></i>
            </div>
            <div>
                <div style="font-size:0.8rem; font-weight:700; color:#64748B;">POPUP MASUK WEB</div>
                <div style="font-weight:800; font-size:0.95rem; color:#0A4D68;">
                    <?php echo $popupConfig['aktif'] ? '<span style="color:#16A34A;">● Sinkron Otomatis</span>' : '<span style="color:#DC2626;">● Nonaktif</span>'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- INFO BANNER SINKRONISASI OTOMATIS -->
    <div class="sync-banner">
        <i class="ph-bold ph-info" style="font-size:1.5rem; color:#2563EB; flex-shrink:0;"></i>
        <div style="font-size:0.88rem; color:#1E3A8A; line-height:1.45;">
            <strong>Pengumuman Popup Tersinkronisasi Otomatis!</strong> Anda cukup membuat pengumuman sekali di bawah ini. Pengumuman utama/pinned atau pengumuman yang ditandai dengan ikon lonceng <i class="ph-bold ph-bell-ringing" style="color:#D97706;"></i> akan otomatis tampil sebagai <strong>Popup Masuk Web</strong> tanpa perlu mengisi ulang.
        </div>
    </div>

    <!-- ACTION BAR -->
    <div class="adm-card" style="margin-bottom: 24px; padding: 18px 24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <h3 style="font-family:var(--font-heading); font-size:1.25rem; font-weight:800; margin-bottom:4px;">
                    Daftar Pengumuman Sekolah
                </h3>
                <p style="font-size:0.88rem; color:#475569;">
                    Kelola informasi dan edaran resmi yang ditampilkan di Beranda utama tepat di bawah sambutan kepala sekolah.
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="../../index.php#pengumuman" target="_blank" class="adm-btn adm-btn-secondary" style="display:inline-flex; align-items:center; gap:8px;">
                    <i class="ph-bold ph-arrow-square-out"></i> Lihat di Web
                </a>
                <button type="button" class="adm-btn adm-btn-primary" onclick="openTambahModal()" style="display:inline-flex; align-items:center; gap:8px;">
                    <i class="ph-bold ph-plus-circle"></i> Tambah Pengumuman Baru
                </button>
            </div>
        </div>
    </div>

    <!-- TABLE PENGUMUMAN -->
    <div class="adm-card" style="padding:0; overflow:hidden;">
        <?php if (empty($pengumumanList)): ?>
            <div style="padding: 48px 20px; text-align:center;">
                <div style="font-size: 3rem; color:#94A3B8; margin-bottom:12px;"><i class="ph-bold ph-megaphone-simple-slash"></i></div>
                <h4 style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; margin-bottom:6px;">Belum Ada Pengumuman</h4>
                <p style="color:#64748B; font-size:0.9rem; margin-bottom:20px;">Silakan buat pengumuman baru untuk ditampilkan kepada pengunjung website.</p>
                <button type="button" class="adm-btn adm-btn-primary" onclick="openTambahModal()">
                    <i class="ph-bold ph-plus-circle"></i> Buat Pengumuman Sekarang
                </button>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:2px solid var(--adm-black);">
                            <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:50px;">Pin</th>
                            <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:130px; text-align:center;">Popup Web</th>
                            <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:120px;">Kategori</th>
                            <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569;">Judul &amp; Ringkasan</th>
                            <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:130px;">Tanggal</th>
                            <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:110px;">Lampiran</th>
                            <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:100px; text-align:center;">Status</th>
                            <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:130px; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pengumumanList as $idx => $item): 
                            $cat = strtolower($item['kategori']);
                            $catClass = 'cat-badge-umum';
                            if (strpos($cat, 'spmb') !== false) $catClass = 'cat-badge-spmb';
                            elseif (strpos($cat, 'akademik') !== false) $catClass = 'cat-badge-akademik';
                            elseif (strpos($cat, 'kegiatan') !== false) $catClass = 'cat-badge-kegiatan';
                            elseif (strpos($cat, 'prestasi') !== false) $catClass = 'cat-badge-prestasi';
                            elseif (strpos($cat, 'libur') !== false) $catClass = 'cat-badge-libur';

                            $isThisPopup = ($popupConfig['id'] == $item['id']);
                        ?>
                            <tr style="border-bottom:1px solid #E2E8F0; <?php echo ($item['is_pinned'] == 1 || $isThisPopup) ? 'background:#FFFDF0;' : ''; ?>">
                                <!-- PIN BUTTON -->
                                <td style="padding:14px 18px;">
                                    <form method="POST" action="pengumuman.php" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_pinned">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" title="<?php echo ($item['is_pinned'] == 1) ? 'Lepaskan Pin' : 'Sematkan ke Atas Beranda'; ?>" style="background:<?php echo ($item['is_pinned'] == 1) ? 'var(--adm-red)' : '#F1F5F9'; ?>; color:<?php echo ($item['is_pinned'] == 1) ? '#fff' : '#64748B'; ?>; border:1.5px solid var(--adm-black); border-radius:6px; width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:2px 2px 0px var(--adm-black);">
                                            <i class="ph-bold ph-push-pin<?php echo ($item['is_pinned'] == 1) ? '-simple-fill' : ''; ?>"></i>
                                        </button>
                                    </form>
                                </td>

                                <!-- POPUP WEB BUTTON -->
                                <td style="padding:14px 18px; text-align:center;">
                                    <form method="POST" action="pengumuman.php" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_popup">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <?php if ($isThisPopup): ?>
                                            <button type="submit" class="adm-btn adm-btn-sm" style="background:#FFE600; color:#000; border:1.5px solid #000; padding:4px 8px; font-size:0.75rem; font-weight:800; display:inline-flex; align-items:center; gap:4px;" title="Pengumuman ini sedang aktif sebagai Popup Masuk Web. Klik untuk kembalikan ke otomatis.">
                                                <i class="ph-bold ph-bell-ringing"></i> Popup Aktif
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="adm-btn adm-btn-sm" style="background:#F1F5F9; color:#475569; border:1.5px solid #CBD5E1; padding:4px 8px; font-size:0.72rem; font-weight:700; display:inline-flex; align-items:center; gap:4px;" title="Klik untuk jadikan pengumuman ini sebagai Popup Masuk Web">
                                                <i class="ph-bold ph-bell"></i> Set Popup
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>

                                <!-- KATEGORI -->
                                <td style="padding:14px 18px;">
                                    <span class="cat-badge <?php echo $catClass; ?>">
                                        <?php echo htmlspecialchars($item['kategori']); ?>
                                    </span>
                                </td>

                                <!-- JUDUL & RINGKASAN -->
                                <td style="padding:14px 18px;">
                                    <div style="font-weight:800; font-size:0.95rem; margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                                        <span><?php echo htmlspecialchars($item['judul']); ?></span>
                                        <?php if ($item['is_pinned'] == 1): ?>
                                            <span style="background:#FFE600; color:#000; font-size:0.7rem; font-weight:800; padding:1px 6px; border:1px solid #000; border-radius:4px;">PINNED</span>
                                        <?php endif; ?>
                                        <?php if ($isThisPopup): ?>
                                            <span style="background:#22C55E; color:#fff; font-size:0.7rem; font-weight:800; padding:1px 6px; border:1px solid #000; border-radius:4px;">POPUP</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size:0.83rem; color:#64748B; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                        <?php echo htmlspecialchars(mb_substr(strip_tags($item['isi']), 0, 160)); ?>...
                                    </div>
                                </td>

                                <!-- TANGGAL -->
                                <td style="padding:14px 18px; font-size:0.85rem; font-weight:600; color:#334155; white-space:nowrap;">
                                    <i class="ph-bold ph-calendar-blank" style="color:#0A4D68;"></i>
                                    <?php echo format_tanggal_indonesia($item['tanggal']); ?>
                                </td>

                                <!-- LAMPIRAN / LINK -->
                                <td style="padding:14px 18px; font-size:0.8rem; white-space:nowrap;">
                                    <?php if (!empty($item['file_lampiran'])): ?>
                                        <a href="../../<?php echo htmlspecialchars(ltrim($item['file_lampiran'], '/')); ?>" target="_blank" class="adm-btn adm-btn-sm" style="padding:4px 8px; font-size:0.75rem; background:#E0F2FE; color:#0369A1; border:1.5px solid #000;" title="Unduh Berkas Lampiran">
                                            <i class="ph-bold ph-file-pdf"></i> Lampiran
                                        </a>
                                    <?php elseif (!empty($item['link_tautan']) && $item['link_tautan'] !== '#'): ?>
                                        <a href="../../<?php echo htmlspecialchars(ltrim($item['link_tautan'], '/')); ?>" target="_blank" class="adm-btn adm-btn-sm" style="padding:4px 8px; font-size:0.75rem; background:#F1F5F9; color:#0F172A; border:1.5px solid #000;" title="Buka Tautan">
                                            <i class="ph-bold ph-link-simple"></i> Link
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#94A3B8;">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- TOGGLE STATUS AKTIF -->
                                <td style="padding:14px 18px; text-align:center;">
                                    <form method="POST" action="pengumuman.php" id="form-toggle-<?php echo $item['id']; ?>" style="display:inline-block;">
                                        <input type="hidden" name="action" value="toggle_aktif">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <label class="nb-switch" title="<?php echo ($item['is_aktif'] == 1) ? 'Sedang Tampil di Beranda' : 'Tersembunyi'; ?>">
                                            <input type="checkbox" <?php echo ($item['is_aktif'] == 1) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                            <span class="nb-slider"></span>
                                        </label>
                                    </form>
                                    <div style="font-size:0.72rem; font-weight:700; color:<?php echo ($item['is_aktif'] == 1) ? '#16A34A' : '#94A3B8'; ?>;">
                                        <?php echo ($item['is_aktif'] == 1) ? 'TAMPIL' : 'OFF'; ?>
                                    </div>
                                </td>

                                <!-- AKSI -->
                                <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                    <div style="display:inline-flex; gap:6px;">
                                        <button type="button" class="adm-btn adm-btn-sm adm-btn-yellow" title="Edit Pengumuman" onclick='openEditModal(<?php echo json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </button>
                                        <form method="POST" action="pengumuman.php" onsubmit="return confirmHapusPengumuman(event, this, '<?php echo addslashes($item['judul']); ?>');" style="display:inline;">
                                            <input type="hidden" name="action" value="hapus">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="adm-btn adm-btn-sm adm-btn-danger" title="Hapus Pengumuman">
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
     TAB 2: PENGATURAN POPUP MASUK WEB (OTOMATIS DARI BERANDA)
     ========================================================================= -->
<?php elseif ($activeTab === 'popup'): ?>

    <div class="popup-config-grid">
        <!-- FORM PENGATURAN POPUP -->
        <div class="adm-card popup-card-left">
            <div style="border-bottom:2px solid var(--adm-black); padding-bottom:16px; margin-bottom:24px;">
                <h3 style="font-family:var(--font-heading); font-size:1.35rem; font-weight:800; margin-bottom:6px; color:var(--adm-black);">
                    Pengaturan Popup Masuk Web
                </h3>
                <p style="font-size:0.88rem; color:#64748B; margin:0; line-height:1.5;">
                    Popup otomatis menggunakan data pengumuman beranda yang aktif, sehingga Anda tidak perlu mengetik ulang isi pengumuman.
                </p>
            </div>

            <form method="POST" action="pengumuman.php">
                <input type="hidden" name="action" value="save_popup">

                <!-- SWITCH STATUS POPUP -->
                <div style="background:<?php echo $popupConfig['aktif'] ? '#F0FDF4' : '#FEF2F2'; ?>; border:2px solid var(--adm-black); border-radius:12px; padding:18px 22px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; gap:16px; box-shadow:3px 3px 0px rgba(0,0,0,0.08);">
                    <div>
                        <div style="font-weight:800; font-size:1.02rem; color:var(--adm-black);">
                            Status Popup Masuk Web
                        </div>
                        <div style="font-size:0.86rem; color:#475569; margin-top:4px; line-height:1.45;">
                            <?php echo $popupConfig['aktif'] ? 'Saat ini popup SEDANG AKTIF dan otomatis muncul kepada pengunjung website.' : 'Saat ini popup DINONAKTIFKAN.'; ?>
                        </div>
                    </div>
                    <label class="nb-switch" style="transform:scale(1.15); flex-shrink:0;">
                        <input type="checkbox" name="popup_pengumuman_aktif" value="1" <?php echo $popupConfig['aktif'] ? 'checked' : ''; ?>>
                        <span class="nb-slider"></span>
                    </label>
                </div>

                <!-- PILIHAN SUMBER PENGUMUMAN BERANDA -->
                <div style="margin-bottom:22px;">
                    <label class="spmb-label" style="display:flex; align-items:center; gap:8px; margin-bottom:8px; font-weight:700; font-size:0.92rem;">
                        <i class="ph-bold ph-megaphone-simple" style="color:var(--adm-primary);"></i> Sumber Pengumuman Beranda untuk Popup
                    </label>
                    <select name="popup_pengumuman_id" class="spmb-select" style="font-weight:600; width:100%; padding:12px 14px; font-size:0.9rem;">
                        <option value="auto" <?php echo ($popupConfig['source_type'] !== 'custom') ? 'selected' : ''; ?>>
                            ⚡ Otomatis: Pengumuman Utama Beranda (Pinned / Terbaru) [Rekomendasi]
                        </option>
                        <optgroup label="Atau Pilih Pengumuman Spesifik:">
                            <?php foreach ($pengumumanList as $p): ?>
                                <?php if ($p['is_aktif'] == 1): ?>
                                    <option value="<?php echo $p['id']; ?>" <?php echo ($popupConfig['id'] == $p['id'] && $popupConfig['source_type'] === 'custom') ? 'selected' : ''; ?>>
                                        [<?php echo htmlspecialchars($p['kategori']); ?>] <?php echo htmlspecialchars($p['judul']); ?> (<?php echo format_tanggal_indonesia($p['tanggal']); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                    <div style="font-size:0.82rem; color:#64748B; margin-top:8px; line-height:1.45;">
                        Dengan mode <strong>Otomatis</strong>, setiap kali Anda membuat atau memperbarui pengumuman beranda, popup akan langsung menyesuaikan secara instan tanpa perlu setting ulang!
                    </div>
                </div>

                <!-- FREKUENSI MUNCUL -->
                <div style="margin-bottom:26px;">
                    <label class="spmb-label" style="display:flex; align-items:center; gap:8px; margin-bottom:8px; font-weight:700; font-size:0.92rem;">
                        <i class="ph-bold ph-clock" style="color:var(--adm-primary);"></i> Frekuensi Tampil untuk Pengunjung
                    </label>
                    <select name="popup_pengumuman_mode" class="spmb-select" style="width:100%; padding:12px 14px; font-size:0.9rem;">
                        <option value="sekali_sehari" <?php echo ($popupConfig['mode'] === 'sekali_sehari') ? 'selected' : ''; ?>>
                            Sekali Sehari (Rekomendasi - Tidak mengganggu jika pengunjung sudah menutupnya hari ini)
                        </option>
                        <option value="sekali_sesi" <?php echo ($popupConfig['mode'] === 'sekali_sesi') ? 'selected' : ''; ?>>
                            Sekali per Sesi Browser (Muncul kembali saat browser dibuka ulang)
                        </option>
                        <option value="selalu" <?php echo ($popupConfig['mode'] === 'selalu') ? 'selected' : ''; ?>>
                            Selalu Muncul (Setiap kali membuka halaman Beranda)
                        </option>
                    </select>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; gap:14px; border-top:2px solid #E2E8F0; padding-top:20px; margin-top:10px;">
                    <button type="submit" class="adm-btn adm-btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding:11px 28px;">
                        <i class="ph-bold ph-floppy-disk"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php endif; ?>

<!-- =========================================================================
     MODAL: TAMBAH PENGUMUMAN BARU
     ========================================================================= -->
<div id="modalTambah" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <!-- Top Banner -->
        <div style="background:var(--adm-yellow); border-bottom:3px solid var(--adm-black); padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800;">
                <i class="ph-bold ph-plus-circle"></i> Tambah Pengumuman Baru
            </div>
            <button type="button" onclick="closeTambahModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>

        <form method="POST" action="pengumuman.php" enctype="multipart/form-data" style="padding:24px 26px;">
            <input type="hidden" name="action" value="tambah">

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Judul Pengumuman *</label>
                <input type="text" name="judul" class="spmb-input" required placeholder="Contoh: Jadwal Pelaksanaan Asesmen Semester Ganjil">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Kategori *</label>
                    <select name="kategori" class="spmb-select" required>
                        <option value="SPMB">SPMB / Pendaftaran</option>
                        <option value="Akademik">Akademik &amp; Ujian</option>
                        <option value="Kegiatan">Kegiatan Sekolah</option>
                        <option value="Prestasi">Prestasi &amp; Juara</option>
                        <option value="Libur">Hari Libur / Kalender</option>
                        <option value="Umum" selected>Pengumuman Umum</option>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Tanggal Pengumuman *</label>
                    <input type="date" name="tanggal" class="spmb-input" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Isi Lengkap Pengumuman *</label>
                <textarea name="isi" rows="6" class="spmb-input" required style="resize:vertical;" placeholder="Tuliskan detail pengumuman yang jelas..."></textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Berkas Lampiran (PDF/DOC/Gambar)</label>
                    <input type="file" name="file_lampiran" class="spmb-input" style="padding:6px;">
                </div>
                <div>
                    <label class="spmb-label">Banner Foto (Opsional)</label>
                    <input type="file" name="gambar" accept="image/*" class="spmb-input" style="padding:6px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Tautan / Link Tambahan (Opsional)</label>
                    <input type="text" name="link_tautan" class="spmb-input" placeholder="Contoh: spmb/daftar.php / https://...">
                </div>
                <div>
                    <label class="spmb-label">Teks Tombol Tautan</label>
                    <input type="text" name="teks_tautan" class="spmb-input" value="Lihat Detail" placeholder="Contoh: Unduh Surat Edaran / Daftar Online">
                </div>
            </div>

            <div style="background:#F8FAFC; border:1.5px solid var(--adm-black); border-radius:8px; padding:14px; margin-bottom:20px; display:flex; flex-direction:column; gap:10px;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="is_pinned" value="1" style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-push-pin" style="color:#DC2626;"></i> Sematkan di Posisi Paling Atas Beranda (Pinned)</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer; color:#0A4D68;">
                    <input type="checkbox" name="is_popup" value="1" style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-bell-ringing" style="color:#D97706;"></i> Jadikan Pengumuman Ini Sebagai Popup Masuk Web</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="is_aktif" value="1" checked style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Tampilkan Langsung di Beranda</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeTambahModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-paper-plane-tilt"></i> Terbitkan Pengumuman</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: EDIT PENGUMUMAN
     ========================================================================= -->
<div id="modalEdit" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div style="background:var(--adm-yellow); border-bottom:3px solid var(--adm-black); padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800;">
                <i class="ph-bold ph-pencil-simple"></i> Edit Pengumuman
            </div>
            <button type="button" onclick="closeEditModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>

        <form method="POST" action="pengumuman.php" enctype="multipart/form-data" style="padding:24px 26px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Judul Pengumuman *</label>
                <input type="text" name="judul" id="edit_judul" class="spmb-input" required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Kategori *</label>
                    <select name="kategori" id="edit_kategori" class="spmb-select" required>
                        <option value="SPMB">SPMB / Pendaftaran</option>
                        <option value="Akademik">Akademik &amp; Ujian</option>
                        <option value="Kegiatan">Kegiatan Sekolah</option>
                        <option value="Prestasi">Prestasi &amp; Juara</option>
                        <option value="Libur">Hari Libur / Kalender</option>
                        <option value="Umum">Pengumuman Umum</option>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Tanggal Pengumuman *</label>
                    <input type="date" name="tanggal" id="edit_tanggal" class="spmb-input" required>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Isi Lengkap Pengumuman *</label>
                <textarea name="isi" id="edit_isi" rows="6" class="spmb-input" required style="resize:vertical;"></textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Ganti Berkas Lampiran</label>
                    <div id="edit_lampiran_preview" style="font-size:0.8rem; margin-bottom:4px;"></div>
                    <input type="file" name="file_lampiran" class="spmb-input" style="padding:6px;">
                </div>
                <div>
                    <label class="spmb-label">Ganti Banner Foto</label>
                    <div id="edit_gambar_preview" style="font-size:0.8rem; margin-bottom:4px;"></div>
                    <input type="file" name="gambar" accept="image/*" class="spmb-input" style="padding:6px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Tautan / Link Tambahan</label>
                    <input type="text" name="link_tautan" id="edit_link_tautan" class="spmb-input">
                </div>
                <div>
                    <label class="spmb-label">Teks Tombol Tautan</label>
                    <input type="text" name="teks_tautan" id="edit_teks_tautan" class="spmb-input">
                </div>
            </div>

            <div style="background:#F8FAFC; border:1.5px solid var(--adm-black); border-radius:8px; padding:14px; margin-bottom:20px; display:flex; flex-direction:column; gap:10px;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="is_pinned" id="edit_is_pinned" value="1" style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-push-pin" style="color:#DC2626;"></i> Sematkan di Posisi Paling Atas Beranda (Pinned)</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer; color:#0A4D68;">
                    <input type="checkbox" name="is_popup" id="edit_is_popup" value="1" style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-bell-ringing" style="color:#D97706;"></i> Jadikan Pengumuman Ini Sebagai Popup Masuk Web</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="is_aktif" id="edit_is_aktif" value="1" style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Tampilkan di Beranda</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: PREVIEW LIVE POPUP MASUK WEB
     ========================================================================= -->
<div id="modalPreviewPopup" class="custom-modal-overlay" onclick="if(event.target===this) closePreviewPopup();">
    <div style="max-width:540px; width:100%; position:relative;">
        <div style="background:#FFFDF0; border:4px solid #000; border-radius:16px; box-shadow:12px 12px 0px #000; overflow:hidden;">
            <!-- Modal Header -->
            <div style="background:#0A4D68; color:#fff; padding:14px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:3px solid #000;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="ph-bold ph-megaphone-simple" style="font-size:1.2rem; color:#FFE600;"></i>
                    <span style="font-family:var(--font-heading); font-size:0.82rem; font-weight:800; background:#FFE600; color:#000; padding:3px 10px; border-radius:999px; border:1.5px solid #000; text-transform:uppercase;">
                        <?php echo htmlspecialchars($popupConfig['badge']); ?>
                    </span>
                </div>
                <button type="button" onclick="closePreviewPopup()" style="background:#fff; border:2px solid #000; border-radius:8px; width:32px; height:32px; font-size:1.25rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:2px 2px 0px #000;">&times;</button>
            </div>

            <?php if (!empty($popupConfig['gambar'])): ?>
                <div style="width:100%; max-height:220px; overflow:hidden; border-bottom:3px solid #000;">
                    <img src="../../<?php echo htmlspecialchars(ltrim($popupConfig['gambar'], '/')); ?>" alt="Banner Popup" style="width:100%; height:auto; display:block; object-fit:cover;">
                </div>
            <?php endif; ?>

            <div style="padding:24px 24px 18px;">
                <h3 style="font-family:var(--font-heading); font-size:1.35rem; font-weight:800; line-height:1.25; margin-bottom:12px; color:#000;">
                    <?php echo htmlspecialchars($popupConfig['judul']); ?>
                </h3>
                <div style="font-size:0.92rem; color:#334155; line-height:1.6; margin-bottom:20px;">
                    <?php echo nl2br(htmlspecialchars($popupConfig['isi'])); ?>
                </div>

                <div style="border-top:2px solid #E2E8F0; padding-top:16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.85rem; font-weight:600; color:#64748B; cursor:pointer;">
                        <input type="checkbox" style="width:16px; height:16px;">
                        <span>Jangan tampilkan lagi hari ini</span>
                    </label>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="adm-btn adm-btn-secondary" onclick="closePreviewPopup()" style="padding:8px 16px;">Tutup</button>
                        <?php if (!empty($popupConfig['tombol_link'])): ?>
                            <a href="../../<?php echo htmlspecialchars(ltrim($popupConfig['tombol_link'], '/')); ?>" target="<?php echo htmlspecialchars($popupConfig['target']); ?>" class="adm-btn adm-btn-primary" style="padding:8px 18px; display:inline-flex; align-items:center; gap:6px;">
                                <span><?php echo htmlspecialchars($popupConfig['tombol_teks']); ?></span>
                                <i class="ph-bold ph-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Modal Open & Close Functions
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
    document.getElementById('edit_isi').value = data.isi;
    document.getElementById('edit_link_tautan').value = data.link_tautan || '';
    document.getElementById('edit_teks_tautan').value = data.teks_tautan || 'Lihat Detail';
    document.getElementById('edit_is_pinned').checked = (data.is_pinned == 1);
    document.getElementById('edit_is_popup').checked = (data.is_popup == 1 || data.id == <?php echo json_encode($popupConfig['id'] ?? 0); ?>);
    document.getElementById('edit_is_aktif').checked = (data.is_aktif == 1);

    const lampPreview = document.getElementById('edit_lampiran_preview');
    if (data.file_lampiran) {
        lampPreview.innerHTML = `File saat ini: <a href="../../${data.file_lampiran}" target="_blank" style="font-weight:700;">Lihat Berkas</a> | <label style="color:#DC2626; cursor:pointer;"><input type="checkbox" name="hapus_lampiran" value="1"> Hapus</label>`;
    } else {
        lampPreview.innerHTML = `<span style="color:#94A3B8;">Belum ada lampiran</span>`;
    }

    const imgPreview = document.getElementById('edit_gambar_preview');
    if (data.gambar) {
        imgPreview.innerHTML = `Foto saat ini: <a href="../../${data.gambar}" target="_blank" style="font-weight:700;">Lihat Foto</a> | <label style="color:#DC2626; cursor:pointer;"><input type="checkbox" name="hapus_gambar" value="1"> Hapus</label>`;
    } else {
        imgPreview.innerHTML = `<span style="color:#94A3B8;">Belum ada banner</span>`;
    }

    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

function previewPopupLive() {
    document.getElementById('modalPreviewPopup').style.display = 'flex';
}
function closePreviewPopup() {
    document.getElementById('modalPreviewPopup').style.display = 'none';
}

// Konfirmasi Hapus dengan SweetAlert2
function confirmHapusPengumuman(e, form, judul) {
    if (e) e.preventDefault();
    Swal.fire({
        title: 'Hapus Pengumuman Ini?',
        html: `Apakah Anda yakin ingin menghapus pengumuman:<br><strong>"${judul}"</strong>?<br><span style="color:#DC2626; font-size:0.85rem;">Berkas lampiran terkait juga akan dihapus.</span>`,
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

// Flash Message Alert
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
