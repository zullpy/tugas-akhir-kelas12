<?php
// spmb/admin/kelas.php
// Kelola Data Kelas & Rombel SMKS Sukapura (Neubrutalism Style)

require_once __DIR__ . '/../config.php';
check_admin_login(true, 'login.php');

$pdo = get_db_connection();

// Map jurusan full name helper
$jurusanFullMap = [
    'AK'      => 'Akuntansi dan Keuangan Lembaga',
    'MP'      => 'Manajemen Perkantoran dan Layanan Bisnis',
    'BD'      => 'Bisnis Digital',
    'TJKT'    => 'Teknik Jaringan Komputer dan Telekomunikasi',
    'PPLG'    => 'Pengembangan Perangkat Lunak dan Gim',
    'TSM'     => 'Teknik Sepeda Motor',
    'DKV'     => 'Desain Komunikasi Visual',
    'DPB'     => 'Desain dan Produksi Busana',
    'Kuliner' => 'Kuliner / Tata Boga',
];

// HANDLE FORM ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TOGGLE AKTIF
    if ($action === 'toggle_aktif') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newStatus = toggle_kelas_aktif($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'is_aktif' => $newStatus]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Status Diperbarui',
                'text'  => 'Status aktif kelas berhasil diubah.'
            ];
        }
        header("Location: kelas.php");
        exit;
    }

    // 2. TAMBAH KELAS
    if ($action === 'tambah') {
        $noUrut        = trim($_POST['no_urut'] ?? '');
        $namaKelas     = trim($_POST['kelas'] ?? '');
        $tingkat       = trim($_POST['tingkat'] ?? 'X');
        $jurusan       = trim($_POST['jurusan'] ?? 'PPLG');
        $jurusanFull   = trim($_POST['jurusan_full'] ?? ($jurusanFullMap[$jurusan] ?? $jurusan));
        $namaWalikelas = trim($_POST['nama_walikelas'] ?? '');
        $kodeWalikelas = trim($_POST['kode_walikelas'] ?? '');
        $mapelWali     = trim($_POST['mapel_walikelas'] ?? '');
        $siswaL        = max(0, (int)($_POST['siswa_l'] ?? 0));
        $siswaP        = max(0, (int)($_POST['siswa_p'] ?? 0));
        $jumlahSiswa   = ($siswaL + $siswaP > 0) ? ($siswaL + $siswaP) : (int)($_POST['jumlah_siswa'] ?? 36);
        $ruang         = trim($_POST['ruang'] ?? '');
        $isAktif       = isset($_POST['is_aktif']) ? 1 : 0;

        if (empty($namaKelas) || empty($namaWalikelas)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Form Belum Lengkap',
                'text'  => 'Nama rombel kelas dan nama wali kelas wajib diisi.'
            ];
            header("Location: kelas.php");
            exit;
        }

        add_kelas([
            'no_urut'         => $noUrut,
            'kelas'           => $namaKelas,
            'tingkat'         => $tingkat,
            'jurusan'         => $jurusan,
            'jurusan_full'    => $jurusanFull,
            'kode_walikelas'  => $kodeWalikelas,
            'nama_walikelas'  => $namaWalikelas,
            'mapel_walikelas' => $mapelWali,
            'siswa_l'         => $siswaL,
            'siswa_p'         => $siswaP,
            'jumlah_siswa'    => $jumlahSiswa,
            'ruang'           => $ruang,
            'is_aktif'        => $isAktif
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Rombel Kelas Ditambahkan!',
            'text'  => 'Data kelas baru berhasil disimpan.'
        ];
        header("Location: kelas.php");
        exit;
    }

    // 3. EDIT KELAS
    if ($action === 'edit') {
        $id            = (int)($_POST['id'] ?? 0);
        $noUrut        = trim($_POST['no_urut'] ?? '');
        $namaKelas     = trim($_POST['kelas'] ?? '');
        $tingkat       = trim($_POST['tingkat'] ?? 'X');
        $jurusan       = trim($_POST['jurusan'] ?? 'PPLG');
        $jurusanFull   = trim($_POST['jurusan_full'] ?? ($jurusanFullMap[$jurusan] ?? $jurusan));
        $namaWalikelas = trim($_POST['nama_walikelas'] ?? '');
        $kodeWalikelas = trim($_POST['kode_walikelas'] ?? '');
        $mapelWali     = trim($_POST['mapel_walikelas'] ?? '');
        $siswaL        = max(0, (int)($_POST['siswa_l'] ?? 0));
        $siswaP        = max(0, (int)($_POST['siswa_p'] ?? 0));
        $jumlahSiswa   = ($siswaL + $siswaP > 0) ? ($siswaL + $siswaP) : (int)($_POST['jumlah_siswa'] ?? 36);
        $ruang         = trim($_POST['ruang'] ?? '');
        $isAktif       = isset($_POST['is_aktif']) ? 1 : 0;

        if ($id <= 0 || empty($namaKelas) || empty($namaWalikelas)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Gagal Mengubah',
                'text'  => 'Data tidak valid atau form belum lengkap.'
            ];
            header("Location: kelas.php");
            exit;
        }

        update_kelas($id, [
            'no_urut'         => $noUrut,
            'kelas'           => $namaKelas,
            'tingkat'         => $tingkat,
            'jurusan'         => $jurusan,
            'jurusan_full'    => $jurusanFull,
            'kode_walikelas'  => $kodeWalikelas,
            'nama_walikelas'  => $namaWalikelas,
            'mapel_walikelas' => $mapelWali,
            'siswa_l'         => $siswaL,
            'siswa_p'         => $siswaP,
            'jumlah_siswa'    => $jumlahSiswa,
            'ruang'           => $ruang,
            'is_aktif'        => $isAktif
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Data Kelas Diperbarui!',
            'text'  => 'Perubahan data rombel kelas berhasil disimpan.'
        ];
        header("Location: kelas.php");
        exit;
    }

    // 4. HAPUS KELAS
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_kelas($id);
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Kelas Dihapus',
                'text'  => 'Data rombel kelas berhasil dihapus dari sistem.'
            ];
        }
        header("Location: kelas.php");
        exit;
    }
}

// FILTER & QUERY
$filterTingkat = trim($_GET['tingkat'] ?? 'semua');
$filterJurusan = trim($_GET['jurusan'] ?? 'semua');
$searchQuery   = trim($_GET['q'] ?? '');

$daftarKelas = get_all_kelas(false, $filterTingkat, $filterJurusan, $searchQuery);

// STATS
$statTotalRombel = (int)$pdo->query("SELECT COUNT(*) FROM `data_kelas`")->fetchColumn();
$statTotalSiswa  = (int)$pdo->query("SELECT SUM(`jumlah_siswa`) FROM `data_kelas`")->fetchColumn();
$statTotalL      = (int)$pdo->query("SELECT SUM(`siswa_l`) FROM `data_kelas`")->fetchColumn();
$statTotalP      = (int)$pdo->query("SELECT SUM(`siswa_p`) FROM `data_kelas`")->fetchColumn();
$statKelasXII    = (int)$pdo->query("SELECT COUNT(*) FROM `data_kelas` WHERE `tingkat` = 'XII'")->fetchColumn();
$statKelasXI     = (int)$pdo->query("SELECT COUNT(*) FROM `data_kelas` WHERE `tingkat` = 'XI'")->fetchColumn();
$statKelasX      = (int)$pdo->query("SELECT COUNT(*) FROM `data_kelas` WHERE `tingkat` = 'X'")->fetchColumn();

$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

$adminPageTitle   = 'Kelola Data Kelas';
$adminPageHeading = 'Data Kelas & Rombongan Belajar';
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
</style>

<!-- TOP ACTION HEADER -->
<div class="adm-section-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--adm-black); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
            <i class="ph-bold ph-chalkboard" style="color: var(--adm-navy);"></i> Kelola Data Kelas &amp; Rombel
        </h2>
        <p style="color: #64748B; font-size: 0.92rem;">Kelola rombongan belajar, nama wali kelas, kapasitas siswa, dan ruang kelas SMKS Sukapura.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="adm-btn adm-btn-yellow" onclick="openTambahModal()" style="font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-plus-circle" style="font-size: 1.25rem;"></i> Tambah Kelas Baru
        </button>
        <a href="../../data/kelas.php" target="_blank" class="adm-btn adm-btn-white" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;" title="Lihat Halaman Kelas di Web">
            <i class="ph-bold ph-arrow-square-out"></i> Halaman Publik
        </a>
    </div>
</div>

<!-- STATS ROW -->
<div class="adm-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: var(--adm-navy-dark);"><?php echo $statTotalRombel; ?></div>
            <div class="adm-stat-title">Total Rombel Kelas</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #E0F2FE;">
            <i class="ph-bold ph-chalkboard" style="color: var(--adm-navy);"></i>
        </div>
    </div>
    <div class="adm-stat-card" style="border: 2.5px solid var(--adm-black); background: #FFFDF0;">
        <div>
            <div class="adm-stat-val" style="color: #B45309;"><?php echo number_format($statTotalSiswa, 0, ',', '.'); ?></div>
            <div class="adm-stat-title" style="font-weight: 800; color: #000;">Total Jumlah Siswa</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: var(--adm-yellow);">
            <i class="ph-bold ph-users-three" style="color: var(--adm-black);"></i>
        </div>
    </div>
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #0284C7;"><?php echo number_format($statTotalL, 0, ',', '.'); ?></div>
            <div class="adm-stat-title">Siswa Laki-laki (L)</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #E0F2FE; color: #0284C7;">
            <i class="ph-bold ph-gender-male"></i>
        </div>
    </div>
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #DB2777;"><?php echo number_format($statTotalP, 0, ',', '.'); ?></div>
            <div class="adm-stat-title">Siswa Perempuan (P)</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #FCE7F3; color: #DB2777;">
            <i class="ph-bold ph-gender-female"></i>
        </div>
    </div>
</div>

<!-- FILTER & SEARCH BAR -->
<div class="adm-card" style="margin-bottom: 24px; padding: 18px 22px;">
    <form method="GET" action="kelas.php" id="filterKelasForm" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 800; font-size: 0.82rem; color: #64748B; text-transform: uppercase;">
                <i class="ph-bold ph-funnel"></i> Tingkat:
            </span>
            <a href="kelas.php?jurusan=<?php echo urlencode($filterJurusan); ?>&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterTingkat === 'semua' || empty($filterTingkat)) ? 'adm-btn-navy' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Semua (<?php echo $statTotalRombel; ?>)</a>
            <a href="kelas.php?tingkat=X&jurusan=<?php echo urlencode($filterJurusan); ?>&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterTingkat === 'X') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Kelas X (<?php echo $statKelasX; ?>)</a>
            <a href="kelas.php?tingkat=XI&jurusan=<?php echo urlencode($filterJurusan); ?>&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterTingkat === 'XI') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Kelas XI (<?php echo $statKelasXI; ?>)</a>
            <a href="kelas.php?tingkat=XII&jurusan=<?php echo urlencode($filterJurusan); ?>&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterTingkat === 'XII') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Kelas XII (<?php echo $statKelasXII; ?>)</a>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <input type="hidden" name="tingkat" value="<?php echo htmlspecialchars($filterTingkat); ?>">
            <div style="min-width: 150px;">
                <select name="jurusan" onchange="document.getElementById('filterKelasForm').submit()" class="adm-select" style="height: 40px; font-size: 0.85rem; font-weight:700;">
                    <option value="semua">-- Semua Jurusan --</option>
                    <?php foreach ($jurusanFullMap as $kKode => $kFull): ?>
                        <option value="<?php echo $kKode; ?>" <?php echo ($filterJurusan === $kKode) ? 'selected' : ''; ?>><?php echo $kKode; ?> - <?php echo $kFull; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="adm-input-icon-group" style="width: 230px;">
                <i class="ph-bold ph-magnifying-glass input-icon-left"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Cari kelas, wali kelas, ruang..." class="adm-input" style="padding-left: 38px; height: 40px; font-size: 0.85rem;">
            </div>
            <button type="submit" class="adm-btn adm-btn-navy" style="height: 40px; padding: 0 16px;">
                <i class="ph-bold ph-magnifying-glass"></i> Cari
            </button>
            <?php if (!empty($searchQuery) || ($filterTingkat !== 'semua' && !empty($filterTingkat)) || ($filterJurusan !== 'semua' && !empty($filterJurusan))): ?>
                <a href="kelas.php" class="adm-btn adm-btn-white" style="height: 40px; padding: 0 12px;" title="Reset Filter"><i class="ph-bold ph-arrow-counter-clockwise"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABLE CARD -->
<div class="adm-card" style="margin-bottom: 30px;">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-list-dashes"></i> Daftar Rombongan Belajar
            <span style="font-size: 0.8rem; background: var(--adm-yellow); color: #000; padding: 2px 8px; border-radius: 10px; border: 1px solid #000; font-weight: 800;"><?php echo count($daftarKelas); ?></span>
        </h3>
        <div style="font-size: 0.82rem; color: #64748B;">
            Total Kelas Terdata: <strong><?php echo count($daftarKelas); ?></strong> Rombel
        </div>
    </div>
    <?php if (empty($daftarKelas)): ?>
        <div style="padding: 48px 20px; text-align:center;">
            <div style="font-size: 3rem; color:#94A3B8; margin-bottom:12px;"><i class="ph-bold ph-chalkboard"></i></div>
            <h4 style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; margin-bottom:6px;">Tidak Ada Data Kelas</h4>
            <p style="color:#64748B; font-size:0.9rem; margin-bottom:20px;">Belum ada rombel kelas yang sesuai filter saat ini.</p>
            <button type="button" class="adm-btn adm-btn-primary" onclick="openTambahModal()"><i class="ph-bold ph-plus"></i> Tambah Kelas Sekarang</button>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid var(--adm-black);">
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:60px;">No</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:140px;">Nama Kelas</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:160px;">Jurusan</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569;">Wali Kelas &amp; Mapel</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:140px; text-align:center;">Jml Siswa</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:180px;">Ruang Kelas</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:100px; text-align:center;">Tampil</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:120px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarKelas as $k): ?>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:14px 18px; font-weight:800; color:#64748B;">
                                #<?php echo htmlspecialchars($k['no_urut']); ?>
                            </td>
                            <td style="padding:14px 18px;">
                                <strong style="font-family:var(--font-heading); font-size:1.05rem; color:#0F172A;"><?php echo htmlspecialchars($k['kelas']); ?></strong><br>
                                <span style="font-size:0.75rem; background:#E2E8F0; padding:1px 6px; border-radius:4px; font-weight:800;">Tingkat <?php echo htmlspecialchars($k['tingkat']); ?></span>
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="font-weight:800; font-size:0.85rem; color:#0A4D68;"><?php echo htmlspecialchars($k['jurusan']); ?></div>
                                <div style="font-size:0.75rem; color:#64748B; line-height:1.3;"><?php echo htmlspecialchars($k['jurusan_full']); ?></div>
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="font-weight:800; font-size:0.92rem; color:#0F172A; display:flex; align-items:center; gap:6px;">
                                    <i class="ph-bold ph-user-circle" style="color:#0284C7;"></i> <?php echo htmlspecialchars($k['nama_walikelas']); ?>
                                </div>
                                <?php if (!empty($k['mapel_walikelas'])): ?>
                                    <div style="font-size:0.78rem; color:#64748B; margin-top:2px;">
                                        <i class="ph-bold ph-book-open"></i> <?php echo htmlspecialchars($k['mapel_walikelas']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <div style="display:inline-flex; flex-direction:column; align-items:center; gap:5px;">
                                    <span style="display:inline-flex; align-items:center; gap:4px; background:#DCFCE7; color:#166534; font-weight:800; font-size:0.84rem; padding:3px 10px; border-radius:12px; border:1px solid #86EFAC;" title="Total Siswa: <?php echo (int)$k['jumlah_siswa']; ?>">
                                        <i class="ph-bold ph-users"></i> <?php echo (int)$k['jumlah_siswa']; ?> Siswa
                                    </span>
                                    <div style="display:flex; gap:5px; font-size:0.75rem; font-weight:800;">
                                        <span style="background:#E0F2FE; color:#0369A1; padding:2px 7px; border-radius:6px; border:1px solid #BAE6FD;" title="Siswa Laki-laki (L)">
                                            L: <?php echo (int)($k['siswa_l'] ?? 0); ?>
                                        </span>
                                        <span style="background:#FCE7F3; color:#BE185D; padding:2px 7px; border-radius:6px; border:1px solid #FBCFE8;" title="Siswa Perempuan (P)">
                                            P: <?php echo (int)($k['siswa_p'] ?? 0); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:14px 18px; font-size:0.82rem; color:#475569; font-weight:600;">
                                <i class="ph-bold ph-door"></i> <?php echo htmlspecialchars($k['ruang']); ?>
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <form method="POST" action="kelas.php" style="display:inline-block;">
                                    <input type="hidden" name="action" value="toggle_aktif">
                                    <input type="hidden" name="id" value="<?php echo $k['id']; ?>">
                                    <label class="nb-switch" title="<?php echo $k['is_aktif'] ? 'Aktif tampil di web' : 'Disembunyikan'; ?>">
                                        <input type="checkbox" <?php echo $k['is_aktif'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="nb-slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; gap:6px;">
                                    <button type="button" class="adm-btn adm-btn-sm adm-btn-yellow" onclick='openEditModal(<?php echo json_encode($k, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' title="Edit Kelas">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>
                                    <form method="POST" action="kelas.php" onsubmit="return confirmHapus(event, this, '<?php echo addslashes($k['kelas']); ?>');" style="display:inline;">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?php echo $k['id']; ?>">
                                        <button type="submit" class="adm-btn adm-btn-sm adm-btn-danger" title="Hapus Kelas">
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
     MODAL: TAMBAH KELAS
     ========================================================================= -->
<div id="modalTambah" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-plus-circle"></i> Tambah Rombel Kelas Baru
            </h3>
            <button type="button" onclick="closeTambahModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>
        <form method="POST" action="kelas.php" style="padding: 24px;">
            <input type="hidden" name="action" value="tambah">
            <div style="display:grid; grid-template-columns: 100px 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">No. Urut</label>
                    <input type="text" name="no_urut" placeholder="01" class="spmb-input" style="text-align:center; font-weight:800;">
                </div>
                <div>
                    <label class="spmb-label">Nama Kelas *</label>
                    <input type="text" name="kelas" placeholder="Contoh: X AK" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Tingkat *</label>
                    <select name="tingkat" class="spmb-select" required>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Kode Jurusan *</label>
                    <select name="jurusan" class="spmb-select" required onchange="autoFillJurusan(this.value, 'tambah_jurusan_full')">
                        <?php foreach ($jurusanFullMap as $kKode => $kFull): ?>
                            <option value="<?php echo $kKode; ?>"><?php echo $kKode; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Nama Lengkap Jurusan *</label>
                    <input type="text" name="jurusan_full" id="tambah_jurusan_full" value="Akuntansi dan Keuangan Lembaga" class="spmb-input" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Nama Wali Kelas *</label>
                    <input type="text" name="nama_walikelas" placeholder="Contoh: Krisnawati, S.Pd." class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Kode / No. Guru</label>
                    <input type="text" name="kode_walikelas" placeholder="44" class="spmb-input">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Mata Pelajaran Wali Kelas</label>
                <input type="text" name="mapel_walikelas" placeholder="Contoh: Seni Budaya / DKV" class="spmb-input">
            </div>

            <!-- Input Rincian Siswa: Laki-laki (L) + Perempuan (P) = Total Otomatis -->
            <div style="background:#F8FAFC; border:2px solid var(--adm-black); border-radius:10px; padding:14px; margin-bottom:16px; box-shadow:3px 3px 0px var(--adm-black);">
                <div style="font-weight:800; font-size:0.85rem; color:#0F172A; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <i class="ph-bold ph-users-three" style="color:#0284C7; font-size:1.15rem;"></i> Rincian Jumlah Siswa Rombel
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1.1fr; gap:12px;">
                    <div>
                        <label class="spmb-label" style="display:flex; align-items:center; gap:4px; font-size:0.8rem;">
                            <i class="ph-bold ph-gender-male" style="color:#0284C7;"></i> Laki-laki (L) *
                        </label>
                        <input type="number" name="siswa_l" id="tambah_siswa_l" value="18" min="0" max="60" class="spmb-input" style="text-align:center; font-weight:800; background:#fff;" required oninput="calcTotalSiswa('tambah')">
                    </div>
                    <div>
                        <label class="spmb-label" style="display:flex; align-items:center; gap:4px; font-size:0.8rem;">
                            <i class="ph-bold ph-gender-female" style="color:#EC4899;"></i> Perempuan (P) *
                        </label>
                        <input type="number" name="siswa_p" id="tambah_siswa_p" value="18" min="0" max="60" class="spmb-input" style="text-align:center; font-weight:800; background:#fff;" required oninput="calcTotalSiswa('tambah')">
                    </div>
                    <div>
                        <label class="spmb-label" style="display:flex; align-items:center; gap:4px; font-size:0.8rem;">
                            <i class="ph-bold ph-calculator" style="color:#16A34A;"></i> Total Siswa
                        </label>
                        <input type="number" name="jumlah_siswa" id="tambah_jumlah_siswa" value="36" class="spmb-input" readonly style="text-align:center; font-weight:800; background:#DCFCE7; color:#166534; cursor:not-allowed;" title="Total otomatis = Laki-laki + Perempuan">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label class="spmb-label">Ruang / Gedung Kelas</label>
                <input type="text" name="ruang" placeholder="Contoh: R. 101 (Gedung A)" class="spmb-input">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="is_aktif" value="1" checked style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Tampilkan di Halaman Web Sekolah</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:2px solid #E2E8F0; padding-top:16px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeTambahModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-check"></i> Simpan Rombel Kelas</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: EDIT KELAS
     ========================================================================= -->
<div id="modalEdit" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-pencil-simple"></i> Edit Rombel Kelas
            </h3>
            <button type="button" onclick="closeEditModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>
        <form method="POST" action="kelas.php" style="padding: 24px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div style="display:grid; grid-template-columns: 100px 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">No. Urut</label>
                    <input type="text" name="no_urut" id="edit_no_urut" class="spmb-input" style="text-align:center; font-weight:800;">
                </div>
                <div>
                    <label class="spmb-label">Nama Kelas *</label>
                    <input type="text" name="kelas" id="edit_kelas" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Tingkat *</label>
                    <select name="tingkat" id="edit_tingkat" class="spmb-select" required>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Kode Jurusan *</label>
                    <select name="jurusan" id="edit_jurusan" class="spmb-select" required onchange="autoFillJurusan(this.value, 'edit_jurusan_full')">
                        <?php foreach ($jurusanFullMap as $kKode => $kFull): ?>
                            <option value="<?php echo $kKode; ?>"><?php echo $kKode; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Nama Lengkap Jurusan *</label>
                    <input type="text" name="jurusan_full" id="edit_jurusan_full" class="spmb-input" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Nama Wali Kelas *</label>
                    <input type="text" name="nama_walikelas" id="edit_nama_walikelas" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Kode / No. Guru</label>
                    <input type="text" name="kode_walikelas" id="edit_kode_walikelas" class="spmb-input">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Mata Pelajaran Wali Kelas</label>
                <input type="text" name="mapel_walikelas" id="edit_mapel_walikelas" class="spmb-input">
            </div>

            <!-- Input Rincian Siswa: Laki-laki (L) + Perempuan (P) = Total Otomatis -->
            <div style="background:#F8FAFC; border:2px solid var(--adm-black); border-radius:10px; padding:14px; margin-bottom:16px; box-shadow:3px 3px 0px var(--adm-black);">
                <div style="font-weight:800; font-size:0.85rem; color:#0F172A; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <i class="ph-bold ph-users-three" style="color:#0284C7; font-size:1.15rem;"></i> Rincian Jumlah Siswa Rombel
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1.1fr; gap:12px;">
                    <div>
                        <label class="spmb-label" style="display:flex; align-items:center; gap:4px; font-size:0.8rem;">
                            <i class="ph-bold ph-gender-male" style="color:#0284C7;"></i> Laki-laki (L) *
                        </label>
                        <input type="number" name="siswa_l" id="edit_siswa_l" value="0" min="0" max="60" class="spmb-input" style="text-align:center; font-weight:800; background:#fff;" required oninput="calcTotalSiswa('edit')">
                    </div>
                    <div>
                        <label class="spmb-label" style="display:flex; align-items:center; gap:4px; font-size:0.8rem;">
                            <i class="ph-bold ph-gender-female" style="color:#EC4899;"></i> Perempuan (P) *
                        </label>
                        <input type="number" name="siswa_p" id="edit_siswa_p" value="0" min="0" max="60" class="spmb-input" style="text-align:center; font-weight:800; background:#fff;" required oninput="calcTotalSiswa('edit')">
                    </div>
                    <div>
                        <label class="spmb-label" style="display:flex; align-items:center; gap:4px; font-size:0.8rem;">
                            <i class="ph-bold ph-calculator" style="color:#16A34A;"></i> Total Siswa
                        </label>
                        <input type="number" name="jumlah_siswa" id="edit_jumlah_siswa" value="0" class="spmb-input" readonly style="text-align:center; font-weight:800; background:#DCFCE7; color:#166534; cursor:not-allowed;" title="Total otomatis = Laki-laki + Perempuan">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label class="spmb-label">Ruang / Gedung Kelas</label>
                <input type="text" name="ruang" id="edit_ruang" class="spmb-input">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="is_aktif" id="edit_is_aktif" value="1" style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Tampilkan di Halaman Web Sekolah</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:2px solid #E2E8F0; padding-top:16px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-check"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
const fullMap = <?php echo json_encode($jurusanFullMap); ?>;

function autoFillJurusan(kode, targetInputId) {
    if (fullMap[kode]) {
        document.getElementById(targetInputId).value = fullMap[kode];
    }
}

function openTambahModal() {
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeTambahModal() {
    document.getElementById('modalTambah').style.display = 'none';
}
function openEditModal(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_no_urut').value = item.no_urut || item.no || '';
    document.getElementById('edit_kelas').value = item.kelas || '';
    document.getElementById('edit_tingkat').value = item.tingkat || 'X';
    document.getElementById('edit_jurusan').value = item.jurusan || 'PPLG';
    document.getElementById('edit_jurusan_full').value = item.jurusan_full || (fullMap[item.jurusan] || '');
    document.getElementById('edit_nama_walikelas').value = item.nama_walikelas || '';
    document.getElementById('edit_kode_walikelas').value = item.kode_walikelas || '';
    document.getElementById('edit_mapel_walikelas').value = item.mapel_walikelas || '';
    document.getElementById('edit_siswa_l').value = (item.siswa_l !== undefined && item.siswa_l !== null) ? item.siswa_l : 0;
    document.getElementById('edit_siswa_p').value = (item.siswa_p !== undefined && item.siswa_p !== null) ? item.siswa_p : 0;
    document.getElementById('edit_jumlah_siswa').value = item.jumlah_siswa || (parseInt(item.siswa_l || 0) + parseInt(item.siswa_p || 0));
    document.getElementById('edit_ruang').value = item.ruang || '';
    document.getElementById('edit_is_aktif').checked = (parseInt(item.is_aktif) === 1);
    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

function calcTotalSiswa(prefix) {
    const lInput = document.getElementById(prefix + '_siswa_l');
    const pInput = document.getElementById(prefix + '_siswa_p');
    const totInput = document.getElementById(prefix + '_jumlah_siswa');
    if (!lInput || !pInput || !totInput) return;
    const l = parseInt(lInput.value) || 0;
    const p = parseInt(pInput.value) || 0;
    totInput.value = l + p;
}

function confirmHapus(e, form, nama) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus Rombel Kelas?',
        text: 'Apakah Anda yakin ingin menghapus kelas "' + nama + '"? Tindakan ini tidak dapat dibatalkan.',
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
