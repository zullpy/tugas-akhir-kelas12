<?php
// spmb/admin/ekskul.php
// Kelola Data Ekstrakurikuler SMKS Sukapura (Neubrutalism Style)

require_once __DIR__ . '/../config.php';
check_admin_login(true, 'login.php');

$pdo = get_db_connection();

$kategoriMeta = [
    'olahraga'   => ['label' => 'Olahraga & Bela Diri', 'class' => 'cat-olahraga', 'icon' => 'ph-soccer-ball', 'bg' => 'icon-olahraga'],
    'teknologi'  => ['label' => 'Teknologi & Sains', 'class' => 'cat-teknologi', 'icon' => 'ph-code-block', 'bg' => 'icon-teknologi'],
    'kebangsaan' => ['label' => 'Kebangsaan & Kepanduan', 'class' => 'cat-kebangsaan', 'icon' => 'ph-compass', 'bg' => 'icon-kebangsaan'],
    'seni'       => ['label' => 'Seni & Budaya', 'class' => 'cat-seni', 'icon' => 'ph-palette', 'bg' => 'icon-seni'],
    'bahasa'     => ['label' => 'Bahasa & Komunikasi', 'class' => 'cat-bahasa', 'icon' => 'ph-translate', 'bg' => 'icon-bahasa'],
    'keagamaan'  => ['label' => 'Keagamaan & Rohis', 'class' => 'cat-keagamaan', 'icon' => 'ph-hands-praying', 'bg' => 'icon-keagamaan'],
];

// HANDLE FORM ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TOGGLE AKTIF
    if ($action === 'toggle_aktif') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newStatus = toggle_ekskul_aktif($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'is_aktif' => $newStatus]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Status Diperbarui',
                'text'  => 'Status aktif ekstrakurikuler berhasil diubah.'
            ];
        }
        header("Location: ekskul.php");
        exit;
    }

    // 2. TAMBAH EKSKUL
    if ($action === 'tambah') {
        $maxKode = (int)$pdo->query("SELECT MAX(CAST(`kode_ekskul` AS UNSIGNED)) FROM `data_ekskul`")->fetchColumn();
        $maxUrutan = (int)$pdo->query("SELECT MAX(`urutan`) FROM `data_ekskul`")->fetchColumn();
        $autoKode = sprintf('%02d', max($maxKode, $maxUrutan) + 1);

        $kodeEkskul    = trim($_POST['kode_ekskul'] ?? '');
        if (empty($kodeEkskul)) {
            $kodeEkskul = $autoKode;
        }
        $nama          = trim($_POST['nama'] ?? '');
        $kategori      = strtolower(trim($_POST['kategori'] ?? 'olahraga'));
        $icon          = trim($_POST['icon'] ?? ($kategoriMeta[$kategori]['icon'] ?? 'ph-trophy'));
        $pembina       = trim($_POST['pembina'] ?? '');
        $ketua         = trim($_POST['ketua'] ?? '');
        $jadwal        = trim($_POST['jadwal'] ?? '');
        $lokasi        = trim($_POST['lokasi'] ?? '');
        $jumlahAnggota = (int)($_POST['jumlah_anggota'] ?? 0);
        $prestasi      = trim($_POST['prestasi'] ?? '');
        $deskripsi     = trim($_POST['deskripsi'] ?? '');
        $isAktif       = isset($_POST['is_aktif']) ? 1 : 0;

        if (empty($nama) || empty($pembina)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Form Belum Lengkap',
                'text'  => 'Nama ekstrakurikuler dan pembina wajib diisi.'
            ];
            header("Location: ekskul.php");
            exit;
        }

        add_ekskul([
            'kode_ekskul'    => $kodeEkskul,
            'nama'           => $nama,
            'kategori'       => $kategori,
            'kategori_label' => $kategoriMeta[$kategori]['label'] ?? ucfirst($kategori),
            'cat_class'      => $kategoriMeta[$kategori]['class'] ?? 'cat-olahraga',
            'icon'           => $icon,
            'icon_bg'        => $kategoriMeta[$kategori]['bg'] ?? 'icon-olahraga',
            'pembina'        => $pembina,
            'ketua'          => $ketua,
            'jadwal'         => $jadwal,
            'lokasi'         => $lokasi,
            'jumlah_anggota' => $jumlahAnggota,
            'prestasi'       => $prestasi,
            'deskripsi'      => $deskripsi,
            'is_aktif'       => $isAktif
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Ekstrakurikuler Ditambahkan!',
            'text'  => 'Data ekskul baru berhasil disimpan dan siap tampil di web.'
        ];
        header("Location: ekskul.php");
        exit;
    }

    // 3. EDIT EKSKUL
    if ($action === 'edit') {
        $id            = (int)($_POST['id'] ?? 0);
        $kodeEkskul    = trim($_POST['kode_ekskul'] ?? '');
        if (empty($kodeEkskul) && $id > 0) {
            $existing = get_ekskul_by_id($id);
            $kodeEkskul = $existing['kode_ekskul'] ?? sprintf('%02d', $id);
        }
        $nama          = trim($_POST['nama'] ?? '');
        $kategori      = strtolower(trim($_POST['kategori'] ?? 'olahraga'));
        $icon          = trim($_POST['icon'] ?? ($kategoriMeta[$kategori]['icon'] ?? 'ph-trophy'));
        $pembina       = trim($_POST['pembina'] ?? '');
        $ketua         = trim($_POST['ketua'] ?? '');
        $jadwal        = trim($_POST['jadwal'] ?? '');
        $lokasi        = trim($_POST['lokasi'] ?? '');
        $jumlahAnggota = (int)($_POST['jumlah_anggota'] ?? 0);
        $prestasi      = trim($_POST['prestasi'] ?? '');
        $deskripsi     = trim($_POST['deskripsi'] ?? '');
        $isAktif       = isset($_POST['is_aktif']) ? 1 : 0;

        if ($id <= 0 || empty($nama) || empty($pembina)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Gagal Mengubah',
                'text'  => 'Data tidak valid atau form belum lengkap.'
            ];
            header("Location: ekskul.php");
            exit;
        }

        update_ekskul($id, [
            'kode_ekskul'    => $kodeEkskul,
            'nama'           => $nama,
            'kategori'       => $kategori,
            'kategori_label' => $kategoriMeta[$kategori]['label'] ?? ucfirst($kategori),
            'cat_class'      => $kategoriMeta[$kategori]['class'] ?? 'cat-olahraga',
            'icon'           => $icon,
            'icon_bg'        => $kategoriMeta[$kategori]['bg'] ?? 'icon-olahraga',
            'pembina'        => $pembina,
            'ketua'          => $ketua,
            'jadwal'         => $jadwal,
            'lokasi'         => $lokasi,
            'jumlah_anggota' => $jumlahAnggota,
            'prestasi'       => $prestasi,
            'deskripsi'      => $deskripsi,
            'is_aktif'       => $isAktif
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Data Ekskul Diperbarui!',
            'text'  => 'Perubahan data ekstrakurikuler berhasil disimpan.'
        ];
        header("Location: ekskul.php");
        exit;
    }

    // 4. HAPUS EKSKUL
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_ekskul($id);
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Ekskul Dihapus',
                'text'  => 'Data ekstrakurikuler berhasil dihapus dan nomor ID diperbarui otomatis.'
            ];
        }
        header("Location: ekskul.php");
        exit;
    }

    // 5. RAPIKAN / URUTKAN ULANG NOMOR EKSKUL
    if ($action === 'reorder') {
        reorder_ekskul();
        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Nomor ID Dirapikan!',
            'text'  => 'Semua nomor ID ekstrakurikuler telah diurutkan ulang secara otomatis.'
        ];
        header("Location: ekskul.php");
        exit;
    }
}

// FILTER & QUERY
$filterKategori = trim($_GET['kategori'] ?? 'semua');
$searchQuery    = trim($_GET['q'] ?? '');

$daftarEkskul = get_all_ekskul(false, $filterKategori, $searchQuery);

// STATS
$statTotal   = (int)$pdo->query("SELECT COUNT(*) FROM `data_ekskul`")->fetchColumn();
$statAnggota = (int)$pdo->query("SELECT SUM(`jumlah_anggota`) FROM `data_ekskul`")->fetchColumn();
$statOlahraga = (int)$pdo->query("SELECT COUNT(*) FROM `data_ekskul` WHERE `kategori` = 'olahraga'")->fetchColumn();
$statSeni     = (int)$pdo->query("SELECT COUNT(*) FROM `data_ekskul` WHERE `kategori` IN ('seni', 'bahasa', 'teknologi')")->fetchColumn();

// ID Otomatis untuk data baru
$maxKodeNum   = (int)$pdo->query("SELECT MAX(CAST(`kode_ekskul` AS UNSIGNED)) FROM `data_ekskul`")->fetchColumn();
$maxUrutanNum = (int)$pdo->query("SELECT MAX(`urutan`) FROM `data_ekskul`")->fetchColumn();
$nextAutoKode = sprintf('%02d', max($maxKodeNum, $maxUrutanNum) + 1);

$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

$adminPageTitle   = 'Kelola Ekstrakurikuler';
$adminPageHeading = 'Ekstrakurikuler Sekolah';
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
    max-width: 650px;
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

.ekskul-icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    border: 2px solid #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    box-shadow: 2px 2px 0px #000;
}
</style>

<!-- TOP ACTION HEADER -->
<div class="adm-section-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--adm-black); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
            <i class="ph-bold ph-trophy" style="color: var(--adm-navy);"></i> Kelola Ekstrakurikuler
        </h2>
        <p style="color: #64748B; font-size: 0.92rem;">Kelola kegiatan ekstrakurikuler siswa.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="adm-btn adm-btn-yellow" onclick="openTambahModal()" style="font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-plus-circle" style="font-size: 1.25rem;"></i> Tambah Ekskul Baru
        </button>
        <form method="POST" action="ekskul.php" style="display:inline;">
            <input type="hidden" name="action" value="reorder">
            <button type="submit" class="adm-btn adm-btn-white" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;" title="Urutkan & Rapikan Nomor ID Semua Ekskul">
                <i class="ph-bold ph-arrows-down-up"></i> Rapikan Nomor
            </button>
        </form>
        <a href="../../data/ekstrakurikuler.php" target="_blank" class="adm-btn adm-btn-white" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;" title="Lihat Halaman Ekskul di Web">
            <i class="ph-bold ph-arrow-square-out"></i> Halaman Publik
        </a>
    </div>
</div>

<!-- STATS ROW -->
<div class="adm-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: var(--adm-navy-dark);"><?php echo $statTotal; ?></div>
            <div class="adm-stat-title">Total Ekstrakurikuler</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #E0F2FE;">
            <i class="ph-bold ph-trophy" style="color: var(--adm-navy);"></i>
        </div>
    </div>
    <div class="adm-stat-card" style="border: 2.5px solid var(--adm-black); background: #FFFDF0;">
        <div>
            <div class="adm-stat-val" style="color: #B45309;"><?php echo number_format($statAnggota, 0, ',', '.'); ?></div>
            <div class="adm-stat-title" style="font-weight: 800; color: #000;">Total Anggota Siswa</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: var(--adm-yellow);">
            <i class="ph-bold ph-users-three" style="color: var(--adm-black);"></i>
        </div>
    </div>
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #16A34A;"><?php echo $statOlahraga; ?></div>
            <div class="adm-stat-title">Olahraga &amp; Bela Diri</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #DCFCE7; color: #16A34A;">
            <i class="ph-bold ph-soccer-ball"></i>
        </div>
    </div>
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #9333EA;"><?php echo $statSeni; ?></div>
            <div class="adm-stat-title">Seni, Sains &amp; Bahasa</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #F3E8FF; color: #9333EA;">
            <i class="ph-bold ph-palette"></i>
        </div>
    </div>
</div>

<!-- FILTER & SEARCH BAR -->
<div class="adm-card" style="margin-bottom: 24px; padding: 18px 22px;">
    <form method="GET" action="ekskul.php" id="filterEkskulForm" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 800; font-size: 0.82rem; color: #64748B; text-transform: uppercase;">
                <i class="ph-bold ph-funnel"></i> Kategori:
            </span>
            <a href="ekskul.php?q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'semua' || empty($filterKategori)) ? 'adm-btn-navy' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Semua (<?php echo $statTotal; ?>)</a>
            <a href="ekskul.php?kategori=olahraga&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'olahraga') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Olahraga (<?php echo $statOlahraga; ?>)</a>
            <a href="ekskul.php?kategori=teknologi&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'teknologi') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Teknologi</a>
            <a href="ekskul.php?kategori=kebangsaan&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'kebangsaan') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Kebangsaan</a>
            <a href="ekskul.php?kategori=seni&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'seni') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Seni</a>
            <a href="ekskul.php?kategori=bahasa&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'bahasa') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Bahasa</a>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($filterKategori); ?>">
            <div class="adm-input-icon-group" style="width: 250px;">
                <i class="ph-bold ph-magnifying-glass input-icon-left"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Cari nama ekskul, pembina..." class="adm-input" style="padding-left: 38px; height: 40px; font-size: 0.85rem;">
            </div>
            <button type="submit" class="adm-btn adm-btn-navy" style="height: 40px; padding: 0 16px;">
                <i class="ph-bold ph-magnifying-glass"></i> Cari
            </button>
            <?php if (!empty($searchQuery) || ($filterKategori !== 'semua' && !empty($filterKategori))): ?>
                <a href="ekskul.php" class="adm-btn adm-btn-white" style="height: 40px; padding: 0 12px;" title="Reset Filter"><i class="ph-bold ph-arrow-counter-clockwise"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABLE CARD -->
<div class="adm-card" style="margin-bottom: 30px;">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-list-dashes"></i> Daftar Kegiatan Ekstrakurikuler
            <span style="font-size: 0.8rem; background: var(--adm-yellow); color: #000; padding: 2px 8px; border-radius: 10px; border: 1px solid #000; font-weight: 800;"><?php echo count($daftarEkskul); ?></span>
        </h3>
        <div style="font-size: 0.82rem; color: #64748B;">
            Total Terdata: <strong><?php echo count($daftarEkskul); ?></strong> Kegiatan
        </div>
    </div>
    <?php if (empty($daftarEkskul)): ?>
        <div style="padding: 48px 20px; text-align:center;">
            <div style="font-size: 3rem; color:#94A3B8; margin-bottom:12px;"><i class="ph-bold ph-trophy"></i></div>
            <h4 style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; margin-bottom:6px;">Tidak Ada Ekstrakurikuler</h4>
            <p style="color:#64748B; font-size:0.9rem; margin-bottom:20px;">Belum ada ekskul yang cocok dengan kriteria filter saat ini.</p>
            <button type="button" class="adm-btn adm-btn-primary" onclick="openTambahModal()"><i class="ph-bold ph-plus"></i> Tambah Ekskul Sekarang</button>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid var(--adm-black);">
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:60px;">No</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569;">Nama Ekstrakurikuler</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:150px;">Kategori</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569;">Pembina &amp; Ketua</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:220px;">Jadwal &amp; Lokasi</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:100px; text-align:center;">Anggota</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:100px; text-align:center;">Tampil</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:120px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarEkskul as $e): 
                        $iconClass = strpos($e['icon'], 'ph-') === false ? 'ph-trophy' : $e['icon'];
                    ?>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:14px 18px; font-weight:800; color:#64748B;">
                                #<?php echo htmlspecialchars($e['kode_ekskul']); ?>
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="ekskul-icon-wrap" style="background:var(--adm-yellow-light);">
                                        <i class="ph-bold <?php echo htmlspecialchars($iconClass); ?>"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:800; font-size:0.98rem; color:#0F172A;"><?php echo htmlspecialchars($e['nama']); ?></div>
                                        <?php if (!empty($e['prestasi'])): ?>
                                            <div style="font-size:0.75rem; color:#D97706; display:flex; align-items:center; gap:4px; margin-top:2px; font-weight:700;">
                                                <i class="ph-bold ph-medal"></i> <?php echo htmlspecialchars($e['prestasi']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:14px 18px;">
                                <span style="display:inline-block; padding:3px 8px; border-radius:6px; font-size:0.75rem; font-weight:800; border:1px solid #000; background:#FFFDF0;">
                                    <?php echo htmlspecialchars($e['kategori_label'] ?? ucfirst($e['kategori'])); ?>
                                </span>
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="font-weight:800; font-size:0.88rem; color:#0F172A;">
                                    <i class="ph-bold ph-chalkboard-teacher" style="color:#0A4D68;"></i> <?php echo htmlspecialchars($e['pembina']); ?>
                                </div>
                                <?php if (!empty($e['ketua'])): ?>
                                    <div style="font-size:0.78rem; color:#64748B; margin-top:2px;">
                                        <i class="ph-bold ph-user"></i> Ketua: <?php echo htmlspecialchars($e['ketua']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 18px; font-size:0.82rem; color:#334155;">
                                <div><i class="ph-bold ph-clock" style="color:#D97706;"></i> <?php echo htmlspecialchars($e['jadwal']); ?></div>
                                <div style="color:#64748B; margin-top:2px;"><i class="ph-bold ph-map-pin"></i> <?php echo htmlspecialchars($e['lokasi']); ?></div>
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <span style="display:inline-flex; align-items:center; gap:4px; background:#DCFCE7; color:#166534; font-weight:800; font-size:0.82rem; padding:3px 10px; border-radius:12px; border:1px solid #86EFAC;">
                                    <i class="ph-bold ph-users"></i> <?php echo (int)$e['jumlah_anggota']; ?>
                                </span>
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <form method="POST" action="ekskul.php" style="display:inline-block;">
                                    <input type="hidden" name="action" value="toggle_aktif">
                                    <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                    <label class="nb-switch" title="<?php echo $e['is_aktif'] ? 'Aktif tampil di web' : 'Disembunyikan'; ?>">
                                        <input type="checkbox" <?php echo $e['is_aktif'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="nb-slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; gap:6px;">
                                    <button type="button" class="adm-btn adm-btn-sm adm-btn-yellow" onclick='openEditModal(<?php echo json_encode($e, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' title="Edit Ekskul">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>
                                    <form method="POST" action="ekskul.php" onsubmit="return confirmHapus(event, this, '<?php echo addslashes($e['nama']); ?>');" style="display:inline;">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                        <button type="submit" class="adm-btn adm-btn-sm adm-btn-danger" title="Hapus Ekskul">
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
     MODAL: TAMBAH EKSKUL
     ========================================================================= -->
<div id="modalTambah" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-plus-circle"></i> Tambah Ekstrakurikuler Baru
            </h3>
            <button type="button" onclick="closeTambahModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>
        <form method="POST" action="ekskul.php" style="padding: 24px;">
            <input type="hidden" name="action" value="tambah">
            <div style="display:grid; grid-template-columns: 110px 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label" style="white-space:nowrap;">No. ID</label>
                    <input type="text" name="kode_ekskul" id="tambah_kode_ekskul" value="<?php echo htmlspecialchars($nextAutoKode); ?>" class="spmb-input" readonly style="text-align:center; font-weight:800; background:#F1F5F9; color:#475569; cursor:not-allowed;" title="No. ID dibuat otomatis oleh sistem">
                </div>
                <div>
                    <label class="spmb-label">Nama Ekstrakurikuler *</label>
                    <input type="text" name="nama" placeholder="Contoh: Futsal Sukapura" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Kategori *</label>
                    <select name="kategori" class="spmb-select" required>
                        <option value="olahraga">Olahraga &amp; Bela Diri</option>
                        <option value="teknologi">Teknologi &amp; Sains</option>
                        <option value="kebangsaan">Kebangsaan &amp; Kepanduan</option>
                        <option value="seni">Seni &amp; Budaya</option>
                        <option value="bahasa">Bahasa &amp; Komunikasi</option>
                        <option value="keagamaan">Keagamaan &amp; Rohis</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Nama Pembina *</label>
                    <input type="text" name="pembina" placeholder="Contoh: Rendi Supriadi" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Ketua Siswa</label>
                    <input type="text" name="ketua" placeholder="Contoh: M. Rizky Pratama (XII TJKT A)" class="spmb-input">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 100px; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Jadwal Latihan</label>
                    <input type="text" name="jadwal" placeholder="Contoh: Selasa & Jumat, 15.30 - 17.30" class="spmb-input">
                </div>
                <div>
                    <label class="spmb-label">Lokasi Kegiatan</label>
                    <input type="text" name="lokasi" placeholder="Contoh: Lapangan Olahraga Utama" class="spmb-input">
                </div>
                <div>
                    <label class="spmb-label">Anggota</label>
                    <input type="number" name="jumlah_anggota" value="30" min="0" class="spmb-input" style="text-align:center;">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Ikon Phosphor (opsional)</label>
                <input type="text" name="icon" placeholder="Contoh: ph-soccer-ball / ph-code-block / ph-compass" class="spmb-input">
            </div>

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Prestasi / Penghargaan Unggulan</label>
                <input type="text" name="prestasi" placeholder="Contoh: Juara 1 Turnamen Futsal Pelajar Kab. Tasikmalaya" class="spmb-input">
            </div>

            <div style="margin-bottom:20px;">
                <label class="spmb-label">Deskripsi Singkat Kegiatan</label>
                <textarea name="deskripsi" rows="3" placeholder="Jelaskan tujuan dan aktivitas ekskul..." class="spmb-input" style="resize:vertical;"></textarea>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="is_aktif" value="1" checked style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Tampilkan di Halaman Web Sekolah</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:2px solid #E2E8F0; padding-top:16px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeTambahModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-check"></i> Simpan Ekstrakurikuler</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: EDIT EKSKUL
     ========================================================================= -->
<div id="modalEdit" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-pencil-simple"></i> Edit Data Ekstrakurikuler
            </h3>
            <button type="button" onclick="closeEditModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>
        <form method="POST" action="ekskul.php" style="padding: 24px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div style="display:grid; grid-template-columns: 110px 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label" style="white-space:nowrap;">No. ID</label>
                    <input type="text" name="kode_ekskul" id="edit_kode_ekskul" class="spmb-input" readonly style="text-align:center; font-weight:800; background:#F1F5F9; color:#475569; cursor:not-allowed;" title="No. ID otomatis dan tidak dapat diubah">
                </div>
                <div>
                    <label class="spmb-label">Nama Ekstrakurikuler *</label>
                    <input type="text" name="nama" id="edit_nama" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Kategori *</label>
                    <select name="kategori" id="edit_kategori" class="spmb-select" required>
                        <option value="olahraga">Olahraga &amp; Bela Diri</option>
                        <option value="teknologi">Teknologi &amp; Sains</option>
                        <option value="kebangsaan">Kebangsaan &amp; Kepanduan</option>
                        <option value="seni">Seni &amp; Budaya</option>
                        <option value="bahasa">Bahasa &amp; Komunikasi</option>
                        <option value="keagamaan">Keagamaan &amp; Rohis</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Nama Pembina *</label>
                    <input type="text" name="pembina" id="edit_pembina" class="spmb-input" required>
                </div>
                <div>
                    <label class="spmb-label">Ketua Siswa</label>
                    <input type="text" name="ketua" id="edit_ketua" class="spmb-input">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr 100px; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Jadwal Latihan</label>
                    <input type="text" name="jadwal" id="edit_jadwal" class="spmb-input">
                </div>
                <div>
                    <label class="spmb-label">Lokasi Kegiatan</label>
                    <input type="text" name="lokasi" id="edit_lokasi" class="spmb-input">
                </div>
                <div>
                    <label class="spmb-label">Anggota</label>
                    <input type="number" name="jumlah_anggota" id="edit_jumlah_anggota" min="0" class="spmb-input" style="text-align:center;">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Ikon Phosphor</label>
                <input type="text" name="icon" id="edit_icon" class="spmb-input">
            </div>

            <div style="margin-bottom:16px;">
                <label class="spmb-label">Prestasi / Penghargaan Unggulan</label>
                <input type="text" name="prestasi" id="edit_prestasi" class="spmb-input">
            </div>

            <div style="margin-bottom:20px;">
                <label class="spmb-label">Deskripsi Singkat Kegiatan</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="spmb-input" style="resize:vertical;"></textarea>
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
function openTambahModal() {
    var idInput = document.getElementById('tambah_kode_ekskul');
    if (idInput) {
        idInput.value = '<?php echo $nextAutoKode; ?>';
    }
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeTambahModal() {
    document.getElementById('modalTambah').style.display = 'none';
}
function openEditModal(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_kode_ekskul').value = item.kode_ekskul || item.id || '';
    document.getElementById('edit_nama').value = item.nama || '';
    document.getElementById('edit_kategori').value = item.kategori || 'olahraga';
    document.getElementById('edit_icon').value = item.icon || '';
    document.getElementById('edit_pembina').value = item.pembina || '';
    document.getElementById('edit_ketua').value = item.ketua || '';
    document.getElementById('edit_jadwal').value = item.jadwal || '';
    document.getElementById('edit_lokasi').value = item.lokasi || '';
    document.getElementById('edit_jumlah_anggota').value = item.jumlah_anggota || 0;
    document.getElementById('edit_prestasi').value = item.prestasi || '';
    document.getElementById('edit_deskripsi').value = item.deskripsi || '';
    document.getElementById('edit_is_aktif').checked = (parseInt(item.is_aktif) === 1);
    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

function confirmHapus(e, form, nama) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus Ekstrakurikuler?',
        text: 'Apakah Anda yakin ingin menghapus ekskul "' + nama + '"? Tindakan ini tidak dapat dibatalkan.',
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
