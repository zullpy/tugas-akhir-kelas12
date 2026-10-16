<?php
// spmb/admin/header.php
require_once __DIR__ . '/../config.php';
check_admin_login();

$currAdminFile = basename($_SERVER['PHP_SELF']);
$pdo = get_db_connection();
$settings = get_all_settings();

// Path helper jika diakses dari /setting/ atau /spmb/admin/
$isSetting = (strpos($_SERVER['PHP_SELF'], '/setting/') !== false);
$adminPath = $isSetting ? '../spmb/admin/' : '';
$spmbPath  = $isSetting ? '../spmb/' : '../';
$rootPath  = $isSetting ? '../' : '../../';
$settingPath = $isSetting ? './' : '../../setting/';

// Hitung pendaftar yang belum diverifikasi
$pendingCount = $pdo->query("SELECT COUNT(*) FROM `spmb_pendaftar` WHERE `status` = 'Menunggu Verifikasi'")->fetchColumn();

// Hitung data untuk badge sidebar
$cntG = 0; $cntS = 0; $cntK = 0; $cntE = 0;
try {
    $cntG = (int)$pdo->query("SELECT COUNT(*) FROM `data_guru` WHERE `is_aktif` = 1")->fetchColumn();
    $cntS = (int)$pdo->query("SELECT COUNT(*) FROM `data_siswa` WHERE `is_aktif` = 1")->fetchColumn();
    $cntK = (int)$pdo->query("SELECT COUNT(*) FROM `data_kelas` WHERE `is_aktif` = 1")->fetchColumn();
    $cntE = (int)$pdo->query("SELECT COUNT(*) FROM `data_ekskul` WHERE `is_aktif` = 1")->fetchColumn();
} catch (Exception $e) {}
$totalDataSekolahCount = $cntG + $cntS + $cntK + $cntE;

$berandaCount = 0; $activeAnnounceCount = 0; $activeNewsCount = 0;
try {
    $berandaCount = (int)$pdo->query("SELECT COUNT(*) FROM `galeri_foto` WHERE `tampilkan_beranda` = 1")->fetchColumn();
    $activeAnnounceCount = (int)$pdo->query("SELECT COUNT(*) FROM `pengumuman` WHERE `is_aktif` = 1")->fetchColumn();
    $activeNewsCount = (int)$pdo->query("SELECT COUNT(*) FROM `berita` WHERE `is_aktif` = 1")->fetchColumn();
} catch (Exception $e) {}
$totalKontenCount = $berandaCount + $activeAnnounceCount + $activeNewsCount;

// Status menu aktif untuk auto-expand grup terkait
$isSpmbActive        = ((!$isSetting && in_array($currAdminFile, ['pendaftar.php', 'detail.php', 'tambah.php'])) || ($isSetting && in_array($currAdminFile, ['index.php', 'pengaturan.php'])));
$isDataSekolahActive = (!$isSetting && in_array($currAdminFile, ['guru.php', 'siswa.php', 'kelas.php', 'ekskul.php']));
$isKontenActive      = (!$isSetting && in_array($currAdminFile, ['galeri.php', 'pengumuman.php', 'berita.php']));
$isPengaturanActive  = ($isSetting && in_array($currAdminFile, ['fonnte.php', 'cloudinary.php', 'keamanan.php']));
$isLaporanActive     = (!$isSetting && in_array($currAdminFile, ['export.php']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $adminPageTitle ?? 'Dashboard Admin'; ?> | Panitia SPMB SMKS SUKAPURA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="<?php echo $spmbPath; ?>spmb.css?v=<?php echo @filemtime(__DIR__ . '/../spmb.css') ?: time(); ?>">
    <link rel="stylesheet" href="<?php echo $adminPath; ?>admin.css?v=<?php echo @filemtime(__DIR__ . '/admin.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $rootPath; ?>assets/favicon.ico" type="image/x-icon">
    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
    /* ─── Collapsible Menu Group Neubrutalism ─── */
    .adm-menu-group {
        list-style: none;
        margin-top: 3px;
    }
    .adm-menu-group-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        font-family: var(--font-heading);
        font-size: 0.88rem;
        font-weight: 800;
        color: #334155;
        background: transparent;
        border: 1.5px solid transparent;
        border-radius: var(--radius-btn);
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: left;
    }
    .adm-menu-group-btn:hover {
        background: #FEF08A;
        color: var(--adm-black);
        border-color: var(--adm-black);
        box-shadow: var(--shadow-sm);
        transform: translate(-1px, -1px);
    }
    .adm-menu-group.is-open > .adm-menu-group-btn,
    .adm-menu-group.has-active > .adm-menu-group-btn {
        background: #FFFDF0;
        color: var(--adm-black);
        border-color: var(--adm-black);
        box-shadow: 2px 2px 0px var(--adm-black);
    }
    .adm-menu-group.has-active:not(.is-open) > .adm-menu-group-btn {
        background: var(--adm-yellow-light);
        border-color: var(--adm-black);
    }
    .adm-menu-group-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .adm-menu-group-left i {
        font-size: 1.2rem;
        color: var(--adm-navy);
    }
    .adm-menu-group-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .adm-group-counter {
        background: var(--adm-yellow);
        color: var(--adm-black);
        font-size: 0.72rem;
        padding: 1px 7px;
        border-radius: 10px;
        border: 1px solid var(--adm-black);
        font-weight: 800;
    }
    .adm-sub-counter {
        background: var(--adm-yellow);
        color: var(--adm-black);
        font-size: 0.7rem;
        padding: 1px 6px;
        border-radius: 8px;
        border: 1px solid var(--adm-black);
        font-weight: 800;
    }
    .adm-group-caret {
        font-size: 0.95rem;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        color: #64748B;
    }
    .adm-menu-group.is-open .adm-group-caret {
        transform: rotate(180deg);
        color: var(--adm-black);
    }
    .adm-submenu-list {
        list-style: none;
        display: none;
        flex-direction: column;
        gap: 5px;
        padding: 6px 0 6px 8px;
        margin: 6px 0 6px 14px;
        border-left: 2px dashed #000;
        animation: submenuSlideDown 0.2s ease-out forwards;
    }
    @keyframes submenuSlideDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .adm-menu-group.is-open > .adm-submenu-list {
        display: flex;
    }
    .adm-submenu-list .adm-menu-item a {
        padding: 8px 12px;
        font-size: 0.85rem;
        font-weight: 700;
    }
    </style>

    <script>
    function toggleMenuGroup(groupId) {
        const group = document.getElementById(groupId);
        if (!group) return;
        const isOpen = group.classList.contains('is-open');
        if (isOpen) {
            group.classList.remove('is-open');
            group.querySelector('.adm-menu-group-btn')?.setAttribute('aria-expanded', 'false');
        } else {
            group.classList.add('is-open');
            group.querySelector('.adm-menu-group-btn')?.setAttribute('aria-expanded', 'true');
        }
    }
    </script>
</head>
<body>

<div class="adm-wrapper">
    <!-- SIDEBAR -->
    <aside class="adm-sidebar">
        <a href="<?php echo $adminPath; ?>index.php" class="adm-brand">
            <div class="adm-brand-logo">
                <img src="<?php echo $rootPath; ?>assets/favicon.ico" alt="Logo">
            </div>
            <div>
                <div class="adm-brand-title">SPMB SUKAPURA</div>
                <div class="adm-brand-subtitle">Panel Administrator</div>
            </div>
        </a>

        <ul class="adm-menu">
            <!-- SEKSI 1: UTAMA (Selalu Terbuka) -->
            <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'index.php') ? 'active' : ''; ?>">
                <a href="<?php echo $adminPath; ?>index.php">
                    <i class="ph-bold ph-squares-four"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- SEKSI 2: SPMB (Collapsible Accordion) -->
            <li class="adm-menu-group <?php echo $isSpmbActive ? 'is-open has-active' : ''; ?>" id="group-spmb">
                <button type="button" class="adm-menu-group-btn" onclick="toggleMenuGroup('group-spmb')" aria-expanded="<?php echo $isSpmbActive ? 'true' : 'false'; ?>">
                    <div class="adm-menu-group-left">
                        <i class="ph-bold ph-identification-card"></i>
                        <span>SPMB</span>
                    </div>
                    <div class="adm-menu-group-right">
                        <?php if ($pendingCount > 0): ?>
                            <span style="background:var(--adm-red); color:#fff; font-size:0.72rem; padding:1px 7px; border-radius:10px; border:1px solid var(--adm-black); font-weight:800;"><?php echo $pendingCount; ?></span>
                        <?php endif; ?>
                        <i class="ph-bold ph-caret-down adm-group-caret"></i>
                    </div>
                </button>
                <ul class="adm-submenu-list">
                    <li class="adm-menu-item <?php echo (!$isSetting && ($currAdminFile === 'pendaftar.php' || $currAdminFile === 'detail.php')) ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>pendaftar.php" style="display:flex; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="ph-bold ph-users"></i>
                                <span>Data Pendaftar</span>
                            </div>
                            <?php if ($pendingCount > 0): ?>
                                <span style="background:var(--adm-red); color:#fff; font-size:0.7rem; padding:1px 6px; border-radius:8px; border:1px solid var(--adm-black); font-weight:800;"><?php echo $pendingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'tambah.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>tambah.php">
                            <i class="ph-bold ph-user-plus"></i>
                            <span>Pendaftaran Manual</span>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo ($isSetting && ($currAdminFile === 'index.php' || $currAdminFile === 'pengaturan.php')) ? 'active' : ''; ?>">
                        <a href="<?php echo $settingPath; ?>index.php">
                            <i class="ph-bold ph-sliders-horizontal"></i>
                            <span>Pengaturan SPMB</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- SEKSI 3: DATA SEKOLAH (Collapsible Accordion) -->
            <li class="adm-menu-group <?php echo $isDataSekolahActive ? 'is-open has-active' : ''; ?>" id="group-data-sekolah">
                <button type="button" class="adm-menu-group-btn" onclick="toggleMenuGroup('group-data-sekolah')" aria-expanded="<?php echo $isDataSekolahActive ? 'true' : 'false'; ?>">
                    <div class="adm-menu-group-left">
                        <i class="ph-bold ph-student"></i>
                        <span>Data Sekolah</span>
                    </div>
                    <div class="adm-menu-group-right">
                        <?php if ($totalDataSekolahCount > 0): ?>
                            <span class="adm-group-counter"><?php echo $totalDataSekolahCount; ?></span>
                        <?php endif; ?>
                        <i class="ph-bold ph-caret-down adm-group-caret"></i>
                    </div>
                </button>
                <ul class="adm-submenu-list">
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'guru.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>guru.php" style="display:flex; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="ph-bold ph-chalkboard-teacher"></i>
                                <span>Guru &amp; Staf</span>
                            </div>
                            <?php if ($cntG > 0): ?>
                                <span class="adm-sub-counter"><?php echo $cntG; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'siswa.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>siswa.php" style="display:flex; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="ph-bold ph-student"></i>
                                <span>Data Siswa</span>
                            </div>
                            <?php if ($cntS > 0): ?>
                                <span class="adm-sub-counter"><?php echo $cntS; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'kelas.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>kelas.php" style="display:flex; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="ph-bold ph-chalkboard"></i>
                                <span>Kelas &amp; Rombel</span>
                            </div>
                            <?php if ($cntK > 0): ?>
                                <span class="adm-sub-counter"><?php echo $cntK; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'ekskul.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>ekskul.php" style="display:flex; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="ph-bold ph-trophy"></i>
                                <span>Ekstrakurikuler</span>
                            </div>
                            <?php if ($cntE > 0): ?>
                                <span class="adm-sub-counter"><?php echo $cntE; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- SEKSI 3: KONTEN WEBSITE (Collapsible Accordion) -->
            <li class="adm-menu-group <?php echo $isKontenActive ? 'is-open has-active' : ''; ?>" id="group-konten-web">
                <button type="button" class="adm-menu-group-btn" onclick="toggleMenuGroup('group-konten-web')" aria-expanded="<?php echo $isKontenActive ? 'true' : 'false'; ?>">
                    <div class="adm-menu-group-left">
                        <i class="ph-bold ph-browsers"></i>
                        <span>Konten Website</span>
                    </div>
                    <div class="adm-menu-group-right">
                        <?php if ($totalKontenCount > 0): ?>
                            <span class="adm-group-counter"><?php echo $totalKontenCount; ?></span>
                        <?php endif; ?>
                        <i class="ph-bold ph-caret-down adm-group-caret"></i>
                    </div>
                </button>
                <ul class="adm-submenu-list">
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'galeri.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>galeri.php" style="display:flex; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="ph-bold ph-images"></i>
                                <span>Galeri &amp; Beranda</span>
                            </div>
                            <?php if ($berandaCount > 0): ?>
                                <span class="adm-sub-counter"><?php echo $berandaCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'pengumuman.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>pengumuman.php" style="display:flex; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="ph-bold ph-megaphone-simple"></i>
                                <span>Pengumuman</span>
                            </div>
                            <?php if ($activeAnnounceCount > 0): ?>
                                <span class="adm-sub-counter"><?php echo $activeAnnounceCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'berita.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>berita.php" style="display:flex; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="ph-bold ph-newspaper"></i>
                                <span>Berita Sekolah</span>
                            </div>
                            <?php if ($activeNewsCount > 0): ?>
                                <span class="adm-sub-counter"><?php echo $activeNewsCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- SEKSI 4: PENGATURAN SISTEM (Collapsible Accordion) -->
            <li class="adm-menu-group <?php echo $isPengaturanActive ? 'is-open has-active' : ''; ?>" id="group-pengaturan-sistem">
                <button type="button" class="adm-menu-group-btn" onclick="toggleMenuGroup('group-pengaturan-sistem')" aria-expanded="<?php echo $isPengaturanActive ? 'true' : 'false'; ?>">
                    <div class="adm-menu-group-left">
                        <i class="ph-bold ph-gear-six"></i>
                        <span>Pengaturan Sistem</span>
                    </div>
                    <div class="adm-menu-group-right">
                        <i class="ph-bold ph-caret-down adm-group-caret"></i>
                    </div>
                </button>
                <ul class="adm-submenu-list">
                    <li class="adm-menu-item <?php echo ($isSetting && $currAdminFile === 'fonnte.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $settingPath; ?>fonnte.php">
                            <i class="ph-bold ph-whatsapp-logo" style="color:#22C55E;"></i>
                            <span>WhatsApp Fonnte</span>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo ($isSetting && $currAdminFile === 'cloudinary.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $settingPath; ?>cloudinary.php">
                            <i class="ph-bold ph-cloud" style="color:#0284C7;"></i>
                            <span>Cloud Storage</span>
                        </a>
                    </li>
                    <li class="adm-menu-item <?php echo ($isSetting && $currAdminFile === 'keamanan.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $settingPath; ?>keamanan.php">
                            <i class="ph-bold ph-shield-check" style="color:#9333EA;"></i>
                            <span>Keamanan Akun</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- SEKSI 5: LAPORAN (Collapsible Accordion) -->
            <li class="adm-menu-group <?php echo $isLaporanActive ? 'is-open has-active' : ''; ?>" id="group-laporan">
                <button type="button" class="adm-menu-group-btn" onclick="toggleMenuGroup('group-laporan')" aria-expanded="<?php echo $isLaporanActive ? 'true' : 'false'; ?>">
                    <div class="adm-menu-group-left">
                        <i class="ph-bold ph-file-text"></i>
                        <span>Laporan</span>
                    </div>
                    <div class="adm-menu-group-right">
                        <i class="ph-bold ph-caret-down adm-group-caret"></i>
                    </div>
                </button>
                <ul class="adm-submenu-list">
                    <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'export.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $adminPath; ?>export.php">
                            <i class="ph-bold ph-file-csv"></i>
                            <span>Ekspor Data (CSV)</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="adm-menu-label" style="margin-top:12px;">Tautan Luar</li>
            <li class="adm-menu-item">
                <a href="<?php echo $spmbPath; ?>index.php" target="_blank">
                    <i class="ph-bold ph-arrow-square-out"></i>
                    <span>Lihat Web SPMB</span>
                </a>
            </li>
            <li class="adm-menu-item">
                <a href="<?php echo $rootPath; ?>index.php" target="_blank">
                    <i class="ph-bold ph-globe"></i>
                    <span>Beranda Sekolah</span>
                </a>
            </li>
        </ul>

        <div class="adm-sidebar-footer">
            <div class="adm-user-profile">
                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                    <div style="width:34px; height:34px; background:var(--adm-navy); color:#fff; border-radius:50%; border:var(--border-thin); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.9rem; flex-shrink:0;">
                        <?php echo strtoupper(substr($_SESSION['spmb_admin_nama'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <div style="font-weight:800; font-size:0.85rem;"><?php echo htmlspecialchars($_SESSION['spmb_admin_nama'] ?? 'Admin'); ?></div>
                        <div style="font-size:0.75rem; color:#555;">@<?php echo htmlspecialchars($_SESSION['spmb_admin_user'] ?? 'admin'); ?></div>
                    </div>
                </div>
                <a href="<?php echo $adminPath; ?>logout.php" title="Keluar" onclick="konfirmasiLogout(event, this.href);" style="color:var(--adm-red); font-size:1.3rem; padding:4px; display:flex; align-items:center;">
                    <i class="ph-bold ph-sign-out"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="adm-main">
        <!-- TOPBAR -->
        <header class="adm-topbar">
            <div class="adm-topbar-title">
                <?php echo $adminPageHeading ?? 'Dashboard'; ?>
            </div>
            <div class="adm-topbar-actions">
                <span class="spmb-badge spmb-badge-success" style="font-size:0.8rem;">
                    TA <?php echo htmlspecialchars($settings['tahun_ajaran'] ?? '2026/2027'); ?>
                </span>
                <span class="spmb-badge" style="background:#fff; font-size:0.8rem;">
                    <?php echo htmlspecialchars($settings['gelombang'] ?? 'Gelombang 1'); ?>
                </span>
            </div>
        </header>

        <main class="adm-content">
