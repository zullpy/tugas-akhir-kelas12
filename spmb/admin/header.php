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
    <link rel="stylesheet" href="<?php echo $spmbPath; ?>spmb.css">
    <link rel="stylesheet" href="<?php echo $adminPath; ?>admin.css">
    <link rel="shortcut icon" href="<?php echo $rootPath; ?>assets/favicon.ico" type="image/x-icon">
    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <li class="adm-menu-label">Utama</li>
            <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'index.php') ? 'active' : ''; ?>">
                <a href="<?php echo $adminPath; ?>index.php">
                    <i class="ph-bold ph-squares-four"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="adm-menu-item <?php echo (!$isSetting && ($currAdminFile === 'pendaftar.php' || $currAdminFile === 'detail.php')) ? 'active' : ''; ?>">
                <a href="<?php echo $adminPath; ?>pendaftar.php" style="display:flex; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <i class="ph-bold ph-users"></i>
                        <span>Data Pendaftar</span>
                    </div>
                    <?php if ($pendingCount > 0): ?>
                        <span style="background:var(--adm-red); color:#fff; font-size:0.75rem; padding:2px 7px; border-radius:10px; border:1px solid var(--adm-black); font-weight:800;"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'tambah.php') ? 'active' : ''; ?>">
                <a href="<?php echo $adminPath; ?>tambah.php">
                    <i class="ph-bold ph-user-plus"></i>
                    <span>Pendaftaran Manual</span>
                </a>
            </li>

            <li class="adm-menu-label" style="margin-top:10px;">Laporan &amp; Pengaturan</li>
            <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'export.php') ? 'active' : ''; ?>">
                <a href="<?php echo $adminPath; ?>export.php">
                    <i class="ph-bold ph-file-csv"></i>
                    <span>Ekspor Data (Excel/CSV)</span>
                </a>
            </li>
            <li class="adm-menu-item <?php echo ($isSetting || $currAdminFile === 'pengaturan.php') ? 'active' : ''; ?>">
                <a href="<?php echo $settingPath; ?>">
                    <i class="ph-bold ph-gear"></i>
                    <span>Pengaturan</span>
                </a>
            </li>

            <li class="adm-menu-label" style="margin-top:10px;">Konten Website</li>
            <li class="adm-menu-item <?php echo (!$isSetting && $currAdminFile === 'galeri.php') ? 'active' : ''; ?>">
                <a href="<?php echo $adminPath; ?>galeri.php" style="display:flex; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <i class="ph-bold ph-images"></i>
                        <span>Galeri &amp; Beranda</span>
                    </div>
                    <?php 
                    $berandaCount = (int)$pdo->query("SELECT COUNT(*) FROM `galeri_foto` WHERE `tampilkan_beranda` = 1")->fetchColumn();
                    if ($berandaCount > 0): 
                    ?>
                        <span style="background:var(--adm-yellow); color:var(--adm-black); font-size:0.72rem; padding:2px 7px; border-radius:10px; border:1px solid var(--adm-black); font-weight:800;" title="<?php echo $berandaCount; ?> foto tampil di Beranda"><?php echo $berandaCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="adm-menu-label" style="margin-top:10px;">Tautan Luar</li>
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
