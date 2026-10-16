<?php
// spmb/admin/guru.php
// Kelola Guru & Tenaga Kependidikan SMKS Sukapura (Neubrutalism Style)

require_once __DIR__ . '/../config.php';
check_admin_login(true, 'login.php');

$pdo = get_db_connection();

// HANDLE FORM ACTIONS (TAMBAH / EDIT / HAPUS / TOGGLE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TOGGLE AKTIF (AJAX / Normal)
    if ($action === 'toggle_aktif') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newStatus = toggle_guru_aktif($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'is_aktif' => $newStatus]);
                exit;
            }
            $_SESSION['flash_msg'] = [
                'type' => 'success',
                'title' => 'Status Diperbarui',
                'text'  => 'Status aktif guru berhasil diubah.'
            ];
        }
        header("Location: guru.php");
        exit;
    }

    // 2. TAMBAH GURU
    if ($action === 'tambah') {
        $nama     = trim($_POST['nama'] ?? '');
        $noUrut   = trim($_POST['no_urut'] ?? '');
        $kategori = trim($_POST['kategori'] ?? 'guru');
        $peran    = trim($_POST['peran'] ?? 'Guru Pengajar');
        $mapelRaw = trim($_POST['mapel'] ?? '');
        $isAktif  = isset($_POST['is_aktif']) ? 1 : 0;

        if (empty($nama)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Nama Wajib Diisi',
                'text'  => 'Mohon lengkapi nama guru/staff beserta gelarnya.'
            ];
            header("Location: guru.php");
            exit;
        }

        $mapelArr = array_filter(array_map('trim', explode(',', $mapelRaw)));
        if (empty($mapelArr)) {
            $mapelArr = [$peran];
        }

        add_guru([
            'no_urut'  => $noUrut,
            'nama'     => $nama,
            'peran'    => $peran,
            'mapel'    => $mapelArr,
            'kategori' => $kategori,
            'is_aktif' => $isAktif
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Guru / Staff Ditambahkan!',
            'text'  => 'Data guru baru telah tersimpan dan siap tampil di website.'
        ];
        header("Location: guru.php");
        exit;
    }

    // 3. EDIT GURU
    if ($action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $nama     = trim($_POST['nama'] ?? '');
        $noUrut   = trim($_POST['no_urut'] ?? '');
        $kategori = trim($_POST['kategori'] ?? 'guru');
        $peran    = trim($_POST['peran'] ?? 'Guru Pengajar');
        $mapelRaw = trim($_POST['mapel'] ?? '');
        $isAktif  = isset($_POST['is_aktif']) ? 1 : 0;

        if ($id <= 0 || empty($nama)) {
            $_SESSION['flash_msg'] = [
                'type' => 'error',
                'title' => 'Gagal Mengubah',
                'text'  => 'Data tidak valid atau nama kosong.'
            ];
            header("Location: guru.php");
            exit;
        }

        $mapelArr = array_filter(array_map('trim', explode(',', $mapelRaw)));
        if (empty($mapelArr)) {
            $mapelArr = [$peran];
        }

        update_guru($id, [
            'no_urut'  => $noUrut,
            'nama'     => $nama,
            'peran'    => $peran,
            'mapel'    => $mapelArr,
            'kategori' => $kategori,
            'is_aktif' => $isAktif
        ]);

        $_SESSION['flash_msg'] = [
            'type'  => 'success',
            'title' => 'Data Diperbarui!',
            'text'  => 'Perubahan informasi guru berhasil disimpan.'
        ];
        header("Location: guru.php");
        exit;
    }

    // 4. HAPUS GURU
    if ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_guru($id);
            $_SESSION['flash_msg'] = [
                'type'  => 'success',
                'title' => 'Data Dihapus',
                'text'  => 'Data guru telah berhasil dihapus dari sistem.'
            ];
        }
        header("Location: guru.php");
        exit;
    }
}

// FILTER & QUERY
$filterKategori = trim($_GET['kategori'] ?? 'semua');
$searchQuery    = trim($_GET['q'] ?? '');

$daftarGuru = get_all_guru(false, $filterKategori, $searchQuery);

// STATS
$statTotal   = (int)$pdo->query("SELECT COUNT(*) FROM `data_guru`")->fetchColumn();
$statGuru    = (int)$pdo->query("SELECT COUNT(*) FROM `data_guru` WHERE `kategori` = 'guru'")->fetchColumn();
$statTU      = (int)$pdo->query("SELECT COUNT(*) FROM `data_guru` WHERE `kategori` IN ('tu', 'staf')")->fetchColumn();
$statPembina = (int)$pdo->query("SELECT COUNT(*) FROM `data_guru` WHERE `kategori` = 'pembina'")->fetchColumn();

$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

$adminPageTitle   = 'Kelola Guru & Staff';
$adminPageHeading = 'Data Guru & Tenaga Didik';
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
    max-width: 600px;
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

/* Badges */
.role-badge-tag {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 800;
    border: 1.5px solid #000;
}
.role-badge-guru { background: #E0F2FE; color: #0284C7; }
.role-badge-tu { background: #FEF3C7; color: #D97706; }
.role-badge-staf { background: #F1F5F9; color: #475569; }
.role-badge-pembina { background: #DCFCE7; color: #16A34A; }

.subject-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #F8FAFC;
    color: #1E293B;
    border: 1px solid #CBD5E1;
    border-radius: 4px;
    padding: 2px 7px;
    font-size: 0.75rem;
    margin: 2px;
}
</style>

<!-- TOP ACTION HEADER -->
<div class="adm-section-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--adm-black); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
            <i class="ph-bold ph-chalkboard-teacher" style="color: var(--adm-navy);"></i> Kelola Guru &amp; Staff
        </h2>
        <p style="color: #64748B; font-size: 0.92rem;">Kelola daftar guru pengajar, staf tata usaha, dan tenaga kependidikan SMKS Sukapura.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="adm-btn adm-btn-yellow" onclick="openTambahModal()" style="font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-plus-circle" style="font-size: 1.25rem;"></i> Tambah Guru / Staff
        </button>
        <a href="../../data/guru.php" target="_blank" class="adm-btn adm-btn-white" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;" title="Lihat Halaman Guru di Web">
            <i class="ph-bold ph-arrow-square-out"></i> Halaman Publik
        </a>
    </div>
</div>

<!-- STATS ROW -->
<div class="adm-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: var(--adm-navy-dark);"><?php echo $statTotal; ?></div>
            <div class="adm-stat-title">Total Guru &amp; Staff</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #E0F2FE;">
            <i class="ph-bold ph-users-three" style="color: var(--adm-navy);"></i>
        </div>
    </div>
    <div class="adm-stat-card" style="border: 2.5px solid var(--adm-black); background: #FFFDF0;">
        <div>
            <div class="adm-stat-val" style="color: #B45309;"><?php echo $statGuru; ?></div>
            <div class="adm-stat-title" style="font-weight: 800; color: #000;">Guru Pengajar</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: var(--adm-yellow);">
            <i class="ph-bold ph-chalkboard-teacher" style="color: var(--adm-black);"></i>
        </div>
    </div>
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #0284C7;"><?php echo $statTU; ?></div>
            <div class="adm-stat-title">Tata Usaha &amp; Caraka</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #E0F2FE; color: #0284C7;">
            <i class="ph-bold ph-identification-card"></i>
        </div>
    </div>
    <div class="adm-stat-card">
        <div>
            <div class="adm-stat-val" style="color: #16A34A;"><?php echo $statPembina; ?></div>
            <div class="adm-stat-title">Pembina Kegiatan</div>
        </div>
        <div class="adm-stat-icon-wrap" style="background: #DCFCE7; color: #16A34A;">
            <i class="ph-bold ph-medal"></i>
        </div>
    </div>
</div>

<!-- FILTER & SEARCH BAR -->
<div class="adm-card" style="margin-bottom: 24px; padding: 18px 22px;">
    <form method="GET" action="guru.php" id="filterGuruForm" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-weight: 800; font-size: 0.82rem; color: #64748B; text-transform: uppercase;">
                <i class="ph-bold ph-funnel"></i> Kategori:
            </span>
            <a href="guru.php?q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'semua' || empty($filterKategori)) ? 'adm-btn-navy' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Semua (<?php echo $statTotal; ?>)</a>
            <a href="guru.php?kategori=guru&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'guru') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Guru (<?php echo $statGuru; ?>)</a>
            <a href="guru.php?kategori=tu&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'tu') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Tata Usaha</a>
            <a href="guru.php?kategori=staf&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'staf') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Satpam &amp; Caraka</a>
            <a href="guru.php?kategori=pembina&q=<?php echo urlencode($searchQuery); ?>" class="adm-btn <?php echo ($filterKategori === 'pembina') ? 'adm-btn-yellow' : 'adm-btn-white'; ?>" style="padding: 6px 12px; font-size: 0.82rem;">Pembina (<?php echo $statPembina; ?>)</a>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($filterKategori); ?>">
            <div class="adm-input-icon-group" style="width: 260px;">
                <i class="ph-bold ph-magnifying-glass input-icon-left"></i>
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Cari nama atau mapel..." class="adm-input" style="padding-left: 38px; height: 40px; font-size: 0.85rem;">
            </div>
            <button type="submit" class="adm-btn adm-btn-navy" style="height: 40px; padding: 0 16px;">
                <i class="ph-bold ph-magnifying-glass"></i> Cari
            </button>
            <?php if (!empty($searchQuery) || ($filterKategori !== 'semua' && !empty($filterKategori))): ?>
                <a href="guru.php" class="adm-btn adm-btn-white" style="height: 40px; padding: 0 12px;" title="Reset Filter"><i class="ph-bold ph-arrow-counter-clockwise"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABLE CARD -->
<div class="adm-card" style="margin-bottom: 30px;">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-list-dashes"></i> Daftar Tenaga Pendidik
            <span style="font-size: 0.8rem; background: var(--adm-yellow); color: #000; padding: 2px 8px; border-radius: 10px; border: 1px solid #000; font-weight: 800;"><?php echo count($daftarGuru); ?></span>
        </h3>
        <div style="font-size: 0.82rem; color: #64748B;">
            Perubahan langsung tersinkronisasi ke halaman informasi guru sekolah.
        </div>
    </div>
    <?php if (empty($daftarGuru)): ?>
        <div style="padding: 48px 20px; text-align:center;">
            <div style="font-size: 3rem; color:#94A3B8; margin-bottom:12px;"><i class="ph-bold ph-chalkboard-teacher"></i></div>
            <h4 style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; margin-bottom:6px;">Tidak Ada Data Guru</h4>
            <p style="color:#64748B; font-size:0.9rem; margin-bottom:20px;">Belum ada data guru yang cocok dengan pencarian atau filter saat ini.</p>
            <button type="button" class="adm-btn adm-btn-primary" onclick="openTambahModal()"><i class="ph-bold ph-plus"></i> Tambah Data Sekarang</button>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:2px solid var(--adm-black);">
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:60px;">No</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569;">Nama &amp; Gelar</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:180px;">Peran / Jabatan</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569;">Mata Pelajaran / Tugas</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:110px; text-align:center;">Status</th>
                        <th style="padding:14px 18px; font-size:0.8rem; font-weight:800; text-transform:uppercase; color:#475569; width:130px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarGuru as $g): ?>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:14px 18px; font-weight:800; color:#64748B;">
                                #<?php echo htmlspecialchars($g['no_urut']); ?>
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:38px; height:38px; border-radius:50%; background:#F1F5F9; border:1.5px solid #000; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <i class="ph-bold ph-user" style="color:var(--adm-navy);"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:800; font-size:0.95rem; color:#0F172A;"><?php echo htmlspecialchars($g['nama']); ?></div>
                                        <span class="role-badge-tag role-badge-<?php echo htmlspecialchars($g['kategori']); ?>">
                                            <?php echo strtoupper(htmlspecialchars($g['kategori'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:14px 18px; font-size:0.88rem; font-weight:700; color:#334155;">
                                <?php echo htmlspecialchars($g['peran']); ?>
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                    <?php foreach ($g['mapel'] as $m): ?>
                                        <span class="subject-badge"><i class="ph-bold ph-check"></i> <?php echo htmlspecialchars($m); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <form method="POST" action="guru.php" style="display:inline-block;">
                                    <input type="hidden" name="action" value="toggle_aktif">
                                    <input type="hidden" name="id" value="<?php echo $g['id']; ?>">
                                    <label class="nb-switch" title="<?php echo $g['is_aktif'] ? 'Aktif tampil di website' : 'Disembunyikan'; ?>">
                                        <input type="checkbox" <?php echo $g['is_aktif'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="nb-slider"></span>
                                    </label>
                                </form>
                                <div style="font-size:0.72rem; font-weight:800; color:<?php echo $g['is_aktif'] ? '#16A34A' : '#94A3B8'; ?>;">
                                    <?php echo $g['is_aktif'] ? 'AKTIF' : 'OFF'; ?>
                                </div>
                            </td>
                            <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; gap:6px;">
                                    <button type="button" class="adm-btn adm-btn-sm adm-btn-yellow" onclick='openEditModal(<?php echo json_encode($g, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' title="Edit Guru">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>
                                    <form method="POST" action="guru.php" onsubmit="return confirmHapus(event, this, '<?php echo addslashes($g['nama']); ?>');" style="display:inline;">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?php echo $g['id']; ?>">
                                        <button type="submit" class="adm-btn adm-btn-sm adm-btn-danger" title="Hapus Guru">
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
     MODAL: TAMBAH GURU / STAFF
     ========================================================================= -->
<div id="modalTambah" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-plus-circle"></i> Tambah Guru &amp; Staff Baru
            </h3>
            <button type="button" onclick="closeTambahModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>
        <form method="POST" action="guru.php" style="padding: 24px;">
            <input type="hidden" name="action" value="tambah">
            <div style="display:grid; grid-template-columns: 100px 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">No. Urut</label>
                    <input type="text" name="no_urut" placeholder="01" class="spmb-input" style="text-align:center; font-weight:800;">
                </div>
                <div>
                    <label class="spmb-label">Nama Lengkap &amp; Gelar *</label>
                    <input type="text" name="nama" placeholder="Contoh: Dedi Ah Kurniadi, S.Pd., M.Pd." class="spmb-input" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Kategori *</label>
                    <select name="kategori" class="spmb-select" required>
                        <option value="guru">Guru Pengajar</option>
                        <option value="tu">Staff Tata Usaha</option>
                        <option value="staf">Satpam / Caraka</option>
                        <option value="pembina">Pembina Kegiatan</option>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Peran / Jabatan *</label>
                    <input type="text" name="peran" placeholder="Contoh: Guru Pengajar / Satpam" class="spmb-input" required>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label class="spmb-label">Mata Pelajaran / Tugas *</label>
                <input type="text" name="mapel" placeholder="Pisahkan koma jika lebih dari satu (Contoh: Matematika, IPAS)" class="spmb-input" required>
                <div style="font-size:0.8rem; color:#64748B; margin-top:4px;">Gunakan koma (,) untuk memisahkan mapel jika mengajar lebih dari 1 mata pelajaran.</div>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="is_aktif" value="1" checked style="width:18px; height:18px;">
                    <span><i class="ph-bold ph-check-circle" style="color:#16A34A;"></i> Tampilkan di Halaman Web Sekolah</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:2px solid #E2E8F0; padding-top:16px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="closeTambahModal()">Batal</button>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="ph-bold ph-check"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: EDIT GURU / STAFF
     ========================================================================= -->
<div id="modalEdit" class="custom-modal-overlay">
    <div class="custom-modal-card">
        <div class="modal-header-neubrutal">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; margin: 0; display:flex; align-items:center; gap:8px;">
                <i class="ph-bold ph-pencil-simple"></i> Edit Data Guru / Staff
            </h3>
            <button type="button" onclick="closeEditModal()" style="background:#fff; border:2px solid #000; border-radius:6px; width:30px; height:30px; font-size:1.1rem; font-weight:800; cursor:pointer; box-shadow:2px 2px 0px #000;">&times;</button>
        </div>
        <form method="POST" action="guru.php" style="padding: 24px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div style="display:grid; grid-template-columns: 100px 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">No. Urut</label>
                    <input type="text" name="no_urut" id="edit_no_urut" class="spmb-input" style="text-align:center; font-weight:800;">
                </div>
                <div>
                    <label class="spmb-label">Nama Lengkap &amp; Gelar *</label>
                    <input type="text" name="nama" id="edit_nama" class="spmb-input" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px;">
                <div>
                    <label class="spmb-label">Kategori *</label>
                    <select name="kategori" id="edit_kategori" class="spmb-select" required>
                        <option value="guru">Guru Pengajar</option>
                        <option value="tu">Staff Tata Usaha</option>
                        <option value="staf">Satpam / Caraka</option>
                        <option value="pembina">Pembina Kegiatan</option>
                    </select>
                </div>
                <div>
                    <label class="spmb-label">Peran / Jabatan *</label>
                    <input type="text" name="peran" id="edit_peran" class="spmb-input" required>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label class="spmb-label">Mata Pelajaran / Tugas *</label>
                <input type="text" name="mapel" id="edit_mapel" class="spmb-input" required>
                <div style="font-size:0.8rem; color:#64748B; margin-top:4px;">Gunakan koma (,) untuk memisahkan mapel jika lebih dari satu.</div>
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
    document.getElementById('modalTambah').style.display = 'flex';
}
function closeTambahModal() {
    document.getElementById('modalTambah').style.display = 'none';
}
function openEditModal(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_no_urut').value = item.no_urut || item.no || '';
    document.getElementById('edit_nama').value = item.nama || '';
    document.getElementById('edit_kategori').value = item.kategori || 'guru';
    document.getElementById('edit_peran').value = item.peran || '';
    document.getElementById('edit_mapel').value = Array.isArray(item.mapel) ? item.mapel.join(', ') : (item.mapel || '');
    document.getElementById('edit_is_aktif').checked = (parseInt(item.is_aktif) === 1);
    document.getElementById('modalEdit').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('modalEdit').style.display = 'none';
}

function confirmHapus(e, form, nama) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus Guru / Staff?',
        text: 'Apakah Anda yakin ingin menghapus data "' + nama + '"? Tindakan ini tidak dapat dibatalkan.',
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
