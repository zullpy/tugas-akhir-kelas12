<?php
// spmb/admin/siswa.php
// Kelola Data Siswa SMKS Sukapura (Neubrutalism Style)

require_once __DIR__ . '/../config.php';
check_admin_login(true, 'login.php');

$pdo = get_db_connection();

// HANDLE FORM ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TOGGLE AKTIF (AJAX / Normal)
    if ($action === 'toggle_aktif') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newStatus = toggle_siswa_aktif($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'is_aktif' => $newStatus]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Status Diperbarui',
                'text'  => 'Status aktif siswa berhasil diubah.'
            ];
        }
        header("Location: siswa.php");
        exit;
    }

    // 2. TAMBAH SISWA
    if ($action === 'tambah') {
        $noUrut  = trim($_POST['no_urut'] ?? '');
        if (empty($noUrut)) {
            $maxUrutan = (int)$pdo->query("SELECT MAX(`urutan`) FROM `data_siswa`")->fetchColumn();
            $maxNo = (int)$pdo->query("SELECT MAX(CAST(`no_urut` AS UNSIGNED)) FROM `data_siswa`")->fetchColumn();
            $noUrut = sprintf('%02d', max($maxUrutan, $maxNo) + 1);
        }
        $nis     = trim($_POST['nis'] ?? '');
        $nisn    = trim($_POST['nisn'] ?? '');
        $nama    = trim($_POST['nama'] ?? '');
        $jk      = trim($_POST['jk'] ?? 'L');
        $tingkat = trim($_POST['tingkat'] ?? 'X');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $kelas   = trim($_POST['kelas'] ?? '');
        $status  = trim($_POST['status'] ?? 'Aktif');
        $isAktif = isset($_POST['is_aktif']) ? 1 : 0;

        if (empty($nama) || empty($kelas)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Form Belum Lengkap',
                'text'  => 'Nama siswa dan kelas wajib diisi.'
            ];
            header("Location: siswa.php");
            exit;
        }

        add_siswa([
            'no_urut'  => $noUrut,
            'nis'      => $nis,
            'nisn'     => $nisn,
            'nama'     => $nama,
            'jk'       => $jk,
            'tingkat'  => $tingkat,
            'jurusan'  => $jurusan,
            'kelas'    => $kelas,
            'status'   => $status,
            'is_aktif' => $isAktif
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Siswa Berhasil Ditambahkan!',
            'text'  => 'Data siswa baru telah tersimpan dan tampil di website.'
        ];
        header("Location: siswa.php");
        exit;
    }

    // 3. EDIT SISWA
    if ($action === 'edit') {
        $id      = (int)($_POST['id'] ?? 0);
        $noUrut  = trim($_POST['no_urut'] ?? '');
        $nis     = trim($_POST['nis'] ?? '');
        $nisn    = trim($_POST['nisn'] ?? '');
        $nama    = trim($_POST['nama'] ?? '');
        $jk      = trim($_POST['jk'] ?? 'L');
        $tingkat = trim($_POST['tingkat'] ?? 'X');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $kelas   = trim($_POST['kelas'] ?? '');
        $status  = trim($_POST['status'] ?? 'Aktif');
        $isAktif = isset($_POST['is_aktif']) ? 1 : 0;

        if ($id <= 0 || empty($nama) || empty($kelas)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Gagal Mengubah',
                'text'  => 'Data tidak valid atau form belum lengkap.'
            ];
            header("Location: siswa.php");
            exit;
        }

        update_siswa($id, [
            'no_urut'  => $noUrut,
            'nis'      => $nis,
            'nisn'     => $nisn,
            'nama'     => $nama,
            'jk'       => $jk,
            'tingkat'  => $tingkat,
            'jurusan'  => $jurusan,
            'kelas'    => $kelas,
            'status'   => $status,
            'is_aktif' => $isAktif
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Data Siswa Diperbarui!',
            'text'  => 'Perubahan data siswa berhasil disimpan.'
        ];
        header("Location: siswa.php");
        exit;
    }

    // 4. HAPUS SISWA
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_siswa($id);
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Siswa Dihapus',
                'text'  => 'Data siswa berhasil dihapus dan nomor urut diperbarui otomatis.'
            ];
        }
        header("Location: siswa.php");
        exit;
    }

    // 5. RAPIKAN / URUTKAN ULANG NOMOR SISWA
    if ($action === 'reorder') {
        reorder_siswa();
        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Nomor Urut Dirapikan!',
            'text'  => 'Semua nomor urut siswa telah diurutkan ulang secara otomatis.'
        ];
        header("Location: siswa.php");
        exit;
    }
}

// Ambil data kelas aktif dari data_kelas
$allDaftarKelas = $pdo->query("SELECT `id`, `kelas`, `tingkat`, `jurusan`, `jurusan_full` FROM `data_kelas` WHERE `is_aktif` = 1 ORDER BY `urutan` ASC, `tingkat` ASC, `kelas` ASC")->fetchAll(PDO::FETCH_ASSOC);

// Map kelas untuk autocomplete & auto-fill tingkat dan jurusan
$kelasDataMap = [];
foreach ($allDaftarKelas as $dk) {
    $kelasDataMap[$dk['kelas']] = [
        'tingkat'      => $dk['tingkat'],
        'jurusan'      => $dk['jurusan'],
        'jurusan_full' => $dk['jurusan_full']
    ];
}

// Opsi Jurusan dari data_kelas
$jurusanOpts = $pdo->query("SELECT DISTINCT `jurusan` FROM `data_kelas` WHERE `jurusan` != '' ORDER BY `jurusan` ASC")->fetchAll(PDO::FETCH_COLUMN);
if (empty($jurusanOpts)) {
    $jurusanOpts = ['PPLG', 'TJKT', 'TSM', 'DKV', 'AK', 'MP', 'BD', 'DPB', 'Kuliner'];
}

// FILTER & QUERY
$filterTingkat = trim($_GET['tingkat'] ?? 'semua');
$filterJurusan = trim($_GET['jurusan'] ?? 'semua');
$filterKelas   = trim($_GET['kelas'] ?? 'semua');
$searchQuery   = trim($_GET['q'] ?? '');

// Auto-rekonsiliasi jika filter kelas dipilih agar tingkat & jurusan tidak saling bertentangan
if ($filterKelas !== 'semua' && !empty($filterKelas) && isset($kelasDataMap[$filterKelas])) {
    $targetTingkat = $kelasDataMap[$filterKelas]['tingkat'];
    $targetJurusan = $kelasDataMap[$filterKelas]['jurusan'];
    if ($filterTingkat !== 'semua' && $filterTingkat !== $targetTingkat) {
        $filterTingkat = $targetTingkat;
    }
    if ($filterJurusan !== 'semua' && $filterJurusan !== $targetJurusan) {
        $filterJurusan = $targetJurusan;
    }
} elseif ($filterJurusan !== 'semua' && $filterKelas !== 'semua' && isset($kelasDataMap[$filterKelas])) {
    if ($kelasDataMap[$filterKelas]['jurusan'] !== $filterJurusan) {
        $filterKelas = 'semua';
    }
}

$daftarSiswa = get_all_siswa(false, $filterTingkat, $filterJurusan, $searchQuery, $filterKelas);

// STATS
$statTotal = (int)$pdo->query("SELECT COUNT(*) FROM `data_siswa`")->fetchColumn();
$statXII   = (int)$pdo->query("SELECT COUNT(*) FROM `data_siswa` WHERE `tingkat` = 'XII'")->fetchColumn();
$statXI    = (int)$pdo->query("SELECT COUNT(*) FROM `data_siswa` WHERE `tingkat` = 'XI'")->fetchColumn();
$statX     = (int)$pdo->query("SELECT COUNT(*) FROM `data_siswa` WHERE `tingkat` = 'X'")->fetchColumn();
$statL     = (int)$pdo->query("SELECT COUNT(*) FROM `data_siswa` WHERE `jk` = 'L'")->fetchColumn();
$statP     = (int)$pdo->query("SELECT COUNT(*) FROM `data_siswa` WHERE `jk` = 'P'")->fetchColumn();

// No. Urut otomatis untuk tambah siswa baru
$maxUrutanS = (int)$pdo->query("SELECT MAX(`urutan`) FROM `data_siswa`")->fetchColumn();
$maxNoS     = (int)$pdo->query("SELECT MAX(CAST(`no_urut` AS UNSIGNED)) FROM `data_siswa`")->fetchColumn();
$nextAutoNoSiswa = sprintf('%02d', max($maxUrutanS, $maxNoS) + 1);

$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

$adminPageTitle   = 'Kelola Data Siswa';
$adminPageHeading = 'Data Siswa Sekolah';
require_once __DIR__ . '/header.php';
?>

<style>
/* Modal Neubrutalism */
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
    max-width: 620px;
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
    align-items: center;
    justify-content: space-between;
}
.nb-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}
.nb-switch input { opacity: 0; width: 0; height: 0; }
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
.nb-switch input:checked + .nb-slider { background-color: var(--adm-yellow); }
.nb-switch input:checked + .nb-slider:before { transform: translateX(20px); }

.gender-badge-l {
    background: #E0F2FE;
    color: #0284C7;
    border: 1px solid #7DD3FC;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 0.72rem;
    font-weight: 800;
}
.gender-badge-p {
    background: #FCE7F3;
    color: #DB2777;
    border: 1px solid #F9A8D4;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 0.72rem;
    font-weight: 800;
}
</style>

<!-- TOP ACTION HEADER -->
<div class="adm-section-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--adm-black); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
            <i class="ph-bold ph-student" style="color: var(--adm-navy);"></i> Kelola Data Siswa
        </h2>
        <p style="color: #64748B; font-size: 0.92rem;">Kelola data siswa aktif, NISN, kelas, dan jurusan.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="adm-btn adm-btn-yellow" onclick="openTambahModal()" style="font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-plus-circle" style="font-size: 1.25rem;"></i> Tambah Siswa Baru
        </button>
        <form method="POST" action="siswa.php" style="display:inline;">
            <input type="hidden" name="action" value="reorder">
            <button type="submit" class="adm-btn adm-btn-white" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;" title="Urutkan & Rapikan Nomor Urut Semua Siswa">
                <i class="ph-bold ph-arrows-down-up"></i> Rapikan Nomor
            </button>
        </form>
        <a href="../../data/siswa.php" target="_blank" class="adm-btn adm-btn-white" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;" title="Lihat Halaman Siswa di Web">
            <i class="ph-bold ph-arrow-square-out"></i> Halaman Publik
        </a>
    </div>
</div>

<!-- STATS ROW -->
<div class="adm-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: var(--adm-navy-dark);"><?php echo $statTotal; ?></div>
            <div class="adm-stat-title">Total Siswa Aktif</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #E0F2FE;">
            <i class="ph-bold ph-student" style="color: var(--adm-navy);"></i>
        </div>
    </div>
    <div class="adm-stat-card" style="border: 2.5px solid var(--adm-black); background: #FFFDF0;">
        <div>
            <div class="adm-stat-val" style="color: #B45309;"><?php echo $statXII; ?></div>
            <div class="adm-stat-title" style="font-weight: 800; color: #000;">Kelas XII (Tingkat 3)</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: var(--adm-yellow);">
            <i class="ph-bold ph-graduation-cap" style="color: var(--adm-black);"></i>
        </div>
    </div>
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #2563EB;"><?php echo $statXI; ?></div>
            <div class="adm-stat-title">Kelas XI (Tingkat 2)</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #DBEAFE; color: #2563EB;">
            <i class="ph-bold ph-books"></i>
        </div>
    </div>
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #16A34A;"><?php echo $statX; ?></div>
            <div class="adm-stat-title">Kelas X (Tingkat 1)</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #DCFCE7; color: #16A34A;">
            <i class="ph-bold ph-book-open"></i>
        </div>
    </div>
</div>

<!-- FILTER & SEARCH BAR -->
<div class="adm-card" style="margin-bottom: 24px; padding: 18px 22px;">
    <form method="GET" action="siswa.php" id="filterSiswaForm" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 800; font-size: 0.82rem; color: #64748B; text-transform: uppercase;">
                <i class="ph-bold ph-funnel"></i> Tingkat:
            </span>
            <a href="siswa.php?tingkat=semua&jurusan=<?php echo urlencode($filterJurusan); ?>&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterTingkat === 'semua' || empty($filterTingkat)) ? 'adm-btn-navy' : 'adm-btn-white'; ?>" style="padding: 7px 14px; font-size: 0.82rem; font-weight:700;">Semua</a>
            <a href="siswa.php?tingkat=XII&jurusan=<?php echo urlencode($filterJurusan); ?>&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterTingkat === 'XII') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 7px 14px; font-size: 0.82rem; font-weight:700;">Kelas XII (<?php echo $statXII; ?>)</a>
            <a href="siswa.php?tingkat=XI&jurusan=<?php echo urlencode($filterJurusan); ?>&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterTingkat === 'XI') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 7px 14px; font-size: 0.82rem; font-weight:700;">Kelas XI (<?php echo $statXI; ?>)</a>
            <a href="siswa.php?tingkat=X&jurusan=<?php echo urlencode($filterJurusan); ?>&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterTingkat === 'X') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 7px 14px; font-size: 0.82rem; font-weight:700;">Kelas X (<?php echo $statX; ?>)</a>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="tingkat" id="filterTingkatInput" value="<?php echo htmlspecialchars($filterTingkat); ?>">
            <div style="position: relative;">
                <select name="jurusan" id="filterJurusanSelect" onchange="onJurusanFilterChange(this)" class="adm-select" style="height: 42px; padding: 0 36px 0 14px; font-size: 0.86rem; font-weight:700; line-height: 42px; min-width: 165px; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000; <?php echo ($filterJurusan !== 'semua' && !empty($filterJurusan)) ? 'background-color:#FEF3C7; font-weight:800;' : 'background-color:#fff;'; ?>">
                    <option value="semua">Semua Jurusan</option>
                    <?php foreach ($jurusanOpts as $j): ?>
                        <option value="<?php echo $j; ?>" <?php echo ($filterJurusan === $j) ? 'selected' : ''; ?>><?php echo $j; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="position: relative;">
                <select name="kelas" id="filterKelasSelect" onchange="onKelasFilterChange(this)" class="adm-select" style="height: 42px; padding: 0 36px 0 14px; font-size: 0.86rem; font-weight:700; line-height: 42px; min-width: 175px; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000; <?php echo ($filterKelas !== 'semua' && !empty($filterKelas)) ? 'background-color:#FEF3C7; font-weight:800;' : 'background-color:#fff;'; ?>">
                    <option value="semua">Semua Kelas</option>
                    <?php
                    $filteredDropdownKelas = [];
                    foreach ($allDaftarKelas as $dk) {
                        if ($filterTingkat !== 'semua' && !empty($filterTingkat) && $dk['tingkat'] !== $filterTingkat) {
                            continue;
                        }
                        if ($filterJurusan !== 'semua' && !empty($filterJurusan) && $dk['jurusan'] !== $filterJurusan) {
                            continue;
                        }
                        $filteredDropdownKelas[] = $dk;
                    }
                    ?>
                    <?php if ($filterTingkat !== 'semua' && !empty($filterTingkat)): ?>
                        <?php foreach ($filteredDropdownKelas as $dk): ?>
                            <option value="<?php echo htmlspecialchars($dk['kelas']); ?>" <?php echo ($filterKelas === $dk['kelas']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dk['kelas']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php
                        $byTingkat = ['XII' => [], 'XI' => [], 'X' => []];
                        foreach ($filteredDropdownKelas as $dk) {
                            $byTingkat[$dk['tingkat']][] = $dk;
                        }
                        foreach ($byTingkat as $tName => $kList):
                            if (empty($kList)) continue;
                        ?>
                            <optgroup label="Kelas <?php echo $tName; ?>">
                                <?php foreach ($kList as $dk): ?>
                                    <option value="<?php echo htmlspecialchars($dk['kelas']); ?>" <?php echo ($filterKelas === $dk['kelas']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dk['kelas']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="adm-input-icon-group" style="width: 220px;">
                <i class="ph-bold ph-magnifying-glass input-icon-left"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Cari nama, NISN, kelas..." class="adm-input" style="padding-left: 38px; height: 42px; font-size: 0.86rem; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000;">
            </div>
            <button type="submit" class="adm-btn adm-btn-navy" style="height: 42px; padding: 0 16px; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000;">
                <i class="ph-bold ph-magnifying-glass"></i> Cari
            </button>
            <?php if (!empty($searchQuery) || ($filterTingkat !== 'semua' && !empty($filterTingkat)) || ($filterJurusan !== 'semua' && !empty($filterJurusan)) || ($filterKelas !== 'semua' && !empty($filterKelas))): ?>
                <a href="siswa.php" class="adm-btn adm-btn-white" style="height: 42px; padding: 0 14px; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000; display: inline-flex; align-items: center;" title="Reset Filter"><i class="ph-bold ph-arrow-counter-clockwise"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABLE CARD -->
<div class="adm-card" style="margin-bottom: 30px;">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-list-dashes"></i> Daftar Siswa Terdata
            <span style="font-size: 0.8rem; background: var(--adm-yellow); color: #000; padding: 2px 8px; border-radius: 10px; border: 1px solid #000; font-weight: 800;"><?php echo count($daftarSiswa); ?></span>
        </h3>
        <div style="font-size: 0.82rem; color: #64748B;">
            Total Terfilter: <strong><?php echo count($daftarSiswa); ?></strong> Siswa
        </div>
    </div>
    <?php if (empty($daftarSiswa)): ?>
        <div style="padding: 48px 20px; text-align:center;">
            <div style="font-size: 3rem; color:#94A3B8; margin-bottom:12px;"><i class="ph-bold ph-student"></i></div>
            <h4 style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; margin-bottom:6px;">Tidak Ada Data Siswa</h4>
            <p style="color:#64748B; font-size:0.9rem; margin-bottom:20px;">Belum ada siswa yang cocok dengan kriteria filter saat ini.</p>
            <button type="button" class="adm-btn adm-btn-primary" onclick="openTambahModal()"><i class="ph-bold ph-plus"></i> Tambah Siswa Sekarang</button>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid var(--adm-black);">
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:60px;">No</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:140px;">NIS / NISN</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569;">Nama Siswa</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:130px;">Tingkat &amp; Jurusan</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:140px;">Kelas / Rombel</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:100px; text-align:center;">Status</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:110px; text-align:center;">Tampil</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:120px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarSiswa as $s): ?>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:14px 18px; font-weight:800; color:#64748B;">
                                #<?php echo htmlspecialchars($s['no_urut']); ?>
                            </td>
                            <td style="padding:14px 18px; font-size:0.85rem;">
                                <strong style="font-family:monospace;"><?php echo htmlspecialchars($s['nisn']); ?></strong><br>
                                <span style="font-size:0.75rem; color:#64748B;">NIS: <?php echo htmlspecialchars($s['nis']); ?></span>
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-weight:800; font-size:0.95rem; color:#0F172A;"><?php echo htmlspecialchars($s['nama']); ?></span>
                                    <span class="<?php echo ($s['jk'] === 'L') ? 'gender-badge-l' : 'gender-badge-p'; ?>">
                                        <?php echo ($s['jk'] === 'L') ? 'LAKI-LAKI' : 'PEREMPUAN'; ?>
                                    </span>
                                </div>
                            </td>
                            <td style="padding:14px 18px;">
                                <span style="display:inline-block; padding:2px 8px; border-radius:6px; font-size:0.75rem; font-weight:800; background:#E2E8F0; border:1px solid #000;">
                                    <?php echo htmlspecialchars($s['tingkat']); ?> - <?php echo htmlspecialchars($s['jurusan']); ?>
                                </span>
                            </td>
                            <td style="padding:14px 18px; font-weight:700; font-size:0.9rem; color:#0A4D68;">
                                <i class="ph-bold ph-chalkboard"></i> <?php echo htmlspecialchars($s['kelas']); ?>
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <span style="background:#DCFCE7; color:#166534; font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:10px; border:1px solid #22C55E;">
                                    <?php echo htmlspecialchars($s['status']); ?>
                                </span>
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <form method="POST" action="siswa.php" style="display:inline-block;">
                                    <input type="hidden" name="action" value="toggle_aktif">
                                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                    <label class="nb-switch" title="<?php echo $s['is_aktif'] ? 'Aktif tampil di web' : 'Disembunyikan'; ?>">
                                        <input type="checkbox" <?php echo $s['is_aktif'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="nb-slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; gap:6px;">
                                    <button type="button" class="adm-btn adm-btn-sm adm-btn-yellow" onclick='openEditModal(<?php echo json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' title="Edit Siswa">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>
                                    <form method="POST" action="siswa.php" onsubmit="return confirmHapus(event, this, '<?php echo addslashes($s['nama']); ?>');" style="display:inline;">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                        <button type="submit" class="adm-btn adm-btn-sm adm-btn-danger" title="Hapus Siswa">
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
     MODAL: TAMBAH SISWA
     ========================================================================= -->
<div id="modalTambah" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-plus-circle"></i> Tambah Data Siswa Baru
            </h3>
            <button type="button" onclick="closeTambahModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>
        <form method="POST" action="siswa.php" style="padding: 24px;">
            <input type="hidden" name="action" value="tambah">
            <div style="display:grid; grid-template-columns: 100px 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label" style="white-space:nowrap;">No. Urut</label>
                    <input type="text" name="no_urut" id="tambah_no_urut" value="<?php echo htmlspecialchars($nextAutoNoSiswa); ?>" class="spmb-input" readonly style="text-align:center; font-weight:800; background:#F1F5F9; color:#475569; cursor:not-allowed;" title="No. Urut otomatis">
                </div>
                <div>
                    <label class="spmb-label">NISN *</label>
                    <input type="text" name="nisn" placeholder="0058291021" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">NIS</label>
                    <input type="text" name="nis" placeholder="39.10.001" class="spmb-input">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Nama Lengkap Siswa *</label>
                    <input type="text" name="nama" placeholder="Contoh: Muhamad Zulfahmi" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Jenis Kelamin *</label>
                    <select name="jk" class="spmb-select" required>
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1.4fr 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Kelas / Rombel *</label>
                    <input type="text" name="kelas" id="tambah_kelas" list="list_data_kelas" placeholder="Cari &amp; pilih kelas..." class="spmb-input" required autocomplete="off" oninput="onKelasChanged('tambah')" onchange="onKelasChanged('tambah')">
                    <datalist id="list_data_kelas">
                        <?php foreach ($allDaftarKelas as $dk): ?>
                            <option value="<?php echo htmlspecialchars($dk['kelas']); ?>">
                                <?php echo htmlspecialchars($dk['kelas']); ?> - <?php echo htmlspecialchars($dk['jurusan_full'] ?? $dk['jurusan']); ?> (<?php echo htmlspecialchars($dk['tingkat']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="spmb-label">Tingkat *</label>
                    <select name="tingkat" id="tambah_tingkat" class="spmb-select" required>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Jurusan *</label>
                    <select name="jurusan" id="tambah_jurusan" class="spmb-select" required>
                        <?php foreach ($jurusanOpts as $j): ?>
                            <option value="<?php echo $j; ?>"><?php echo $j; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:20px;">
                <div>
                    <label class="spmb-label">Status Siswa</label>
                    <select name="status" class="spmb-select">
                        <option value="Aktif">Aktif</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Pindah">Pindah</option>
                    </select>
                </div>
                <div style="display:flex; align-items:center; margin-top:24px;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                        <input type="checkbox" name="is_aktif" value="1" checked style="width:18px; height:18px;">
                        <span><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Tampilkan di Web</span>
                    </label>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:2px solid #E2E8F0; padding-top:16px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeTambahModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-check"></i> Simpan Data Siswa</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: EDIT SISWA
     ========================================================================= -->
<div id="modalEdit" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-pencil-simple"></i> Edit Data Siswa
            </h3>
            <button type="button" onclick="closeEditModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>
        <form method="POST" action="siswa.php" style="padding: 24px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div style="display:grid; grid-template-columns: 100px 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label" style="white-space:nowrap;">No. Urut</label>
                    <input type="text" name="no_urut" id="edit_no_urut" class="spmb-input" readonly style="text-align:center; font-weight:800; background:#F1F5F9; color:#475569; cursor:not-allowed;" title="No. Urut">
                </div>
                <div>
                    <label class="spmb-label">NISN *</label>
                    <input type="text" name="nisn" id="edit_nisn" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">NIS</label>
                    <input type="text" name="nis" id="edit_nis" class="spmb-input">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Nama Lengkap Siswa *</label>
                    <input type="text" name="nama" id="edit_nama" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Jenis Kelamin *</label>
                    <select name="jk" id="edit_jk" class="spmb-select" required>
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1.4fr 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Kelas / Rombel *</label>
                    <input type="text" name="kelas" id="edit_kelas" list="list_data_kelas" placeholder="Cari &amp; pilih kelas..." class="spmb-input" required autocomplete="off" oninput="onKelasChanged('edit')" onchange="onKelasChanged('edit')">
                </div>
                <div>
                    <label class="spmb-label">Tingkat *</label>
                    <select name="tingkat" id="edit_tingkat" class="spmb-select" required>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Jurusan *</label>
                    <select name="jurusan" id="edit_jurusan" class="spmb-select" required>
                        <?php foreach ($jurusanOpts as $j): ?>
                            <option value="<?php echo $j; ?>"><?php echo $j; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:20px;">
                <div>
                    <label class="spmb-label">Status Siswa</label>
                    <select name="status" id="edit_status" class="spmb-select">
                        <option value="Aktif">Aktif</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Pindah">Pindah</option>
                    </select>
                </div>
                <div style="display:flex; align-items:center; margin-top:24px;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                        <input type="checkbox" name="is_aktif" id="edit_is_aktif" value="1" style="width:18px; height:18px;">
                        <span><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Tampilkan di Web</span>
                    </label>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:2px solid #E2E8F0; padding-top:16px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-check"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
const kelasDataMap = <?php echo json_encode($kelasDataMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

function onKelasChanged(prefix) {
    const input = document.getElementById(prefix + '_kelas');
    if (!input) return;
    const val = input.value.trim();
    if (kelasDataMap[val]) {
        const item = kelasDataMap[val];
        const tingkatSelect = document.getElementById(prefix + '_tingkat');
        const jurusanSelect = document.getElementById(prefix + '_jurusan');
        if (tingkatSelect && item.tingkat) {
            tingkatSelect.value = item.tingkat;
        }
        if (jurusanSelect && item.jurusan) {
            jurusanSelect.value = item.jurusan;
        }
    }
}

function onKelasFilterChange(selectElem) {
    const val = selectElem.value;
    if (val !== 'semua' && val !== '') {
        if (kelasDataMap[val]) {
            const tInput = document.getElementById('filterTingkatInput');
            if (tInput) {
                tInput.value = kelasDataMap[val].tingkat;
            }
        }
    }
    document.getElementById('filterSiswaForm').submit();
}

function onJurusanFilterChange(selectElem) {
    const kelasSelect = document.getElementById('filterKelasSelect');
    if (kelasSelect) {
        kelasSelect.value = 'semua';
    }
    document.getElementById('filterSiswaForm').submit();
}

function openTambahModal() {
    var no = document.getElementById('tambah_no_urut');
    if (no) no.value = '<?php echo $nextAutoNoSiswa; ?>';
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeTambahModal() {
    document.getElementById('modalTambah').style.display = 'none';
}
function openEditModal(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_no_urut').value = item.no_urut || item.no || '';
    document.getElementById('edit_nisn').value = item.nisn || '';
    document.getElementById('edit_nis').value = item.nis || '';
    document.getElementById('edit_nama').value = item.nama || '';
    document.getElementById('edit_jk').value = item.jk || 'L';
    document.getElementById('edit_tingkat').value = item.tingkat || 'X';
    document.getElementById('edit_jurusan').value = item.jurusan || 'PPLG';
    document.getElementById('edit_kelas').value = item.kelas || '';
    document.getElementById('edit_status').value = item.status || 'Aktif';
    document.getElementById('edit_is_aktif').checked = (parseInt(item.is_aktif) === 1);
    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

function confirmHapus(e, form, nama) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus Data Siswa?',
        text: 'Apakah Anda yakin ingin menghapus data siswa "' + nama + '"? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Ya, Hapus Data',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

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
