<!-- components/navbar.php -->
<?php
// Dynamic calculation of $base_url based on script directory depth relative to project root
$root_dir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$script_dir = str_replace('\\', '/', realpath(dirname($_SERVER['SCRIPT_FILENAME'] ?? '')));

if ($root_dir && $script_dir && strpos($script_dir, $root_dir) === 0) {
    $relative_path = trim(substr($script_dir, strlen($root_dir)), '/');
    if ($relative_path === '') {
        $base_url = '';
    } else {
        $depth = count(explode('/', $relative_path));
        $base_url = str_repeat('../', $depth);
    }
} else {
    if (!isset($base_url)) {
        $base_url = '';
    }
}
$current_page_file = basename($_SERVER['SCRIPT_NAME'] ?? '');
$current_script_path = $_SERVER['SCRIPT_NAME'] ?? '';
?>
<style>
/* Google Fonts import: Plus Jakarta Sans & Space Grotesk */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap');

:root {
    --nb-yellow: #FFE600;      /* Logo Vibrant Yellow */
    --nb-navy: #0A4D68;        /* Logo Deep Teal/Navy Blue */
    --nb-navy-dark: #053B50;   /* Logo Darker Teal/Navy */
    --nb-red: #FF4B4B;         /* Logo Accent Red */
    --nb-bg: #FFFDF0;          /* Warm Cream Background */
    --nb-black: #000000;       /* Neubrutalism Black Borders & Shadows */
    --nb-white: #FFFFFF;       /* White */
    --font-heading: 'Space Grotesk', sans-serif;
    --font-body: 'Plus Jakarta Sans', sans-serif;
    --border-thick: 3.5px solid var(--nb-black);
    --border-medium: 2.5px solid var(--nb-black);
    --border-thin: 2px solid var(--nb-black);
    --shadow-sm: 3px 3px 0px var(--nb-black);
    --shadow-md: 5px 5px 0px var(--nb-black);
    --shadow-lg: 7px 7px 0px var(--nb-black);
    --radius-box: 14px;
    --radius-btn: 10px;
}

/* Container for Header & Navbar (Full Width) */
.header-wrapper {
    width: 100%;
    max-width: 100%;
    margin: 0;
    background-color: var(--nb-yellow);
    border-bottom: var(--border-thick);
    box-shadow: 0 4px 0px var(--nb-black);
    position: sticky;
    top: 0;
    z-index: 1000;
}

/* Neubrutalism Inner Navbar Container */
.navbar {
    width: 92%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 12px 0;
    background-color: transparent;
    border: none;
    border-radius: 0;
    box-shadow: none;
    transition: none;
}

.nav-center {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.nav-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nav-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--nb-black);
}

.logo-img-wrapper {
    background: var(--nb-white);
    border: var(--border-medium);
    border-radius: var(--radius-btn);
    box-shadow: 2.5px 2.5px 0px var(--nb-black);
    padding: 4px 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.nav-logo:hover .logo-img-wrapper {
    transform: translate(-2px, -2px);
    box-shadow: 4px 4px 0px var(--nb-black);
}

.logo-img {
    height: 38px;
    width: auto;
    object-fit: contain;
    display: block;
}

.logo-badge {
    background: var(--nb-white);
    border: var(--border-medium);
    border-radius: var(--radius-btn);
    box-shadow: 2.5px 2.5px 0px var(--nb-black);
    padding: 6px 14px;
    font-family: var(--font-heading);
    font-weight: 800;
    font-size: 1.1rem;
    letter-spacing: 0.02em;
    color: var(--nb-navy-dark);
    text-transform: uppercase;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease, color 0.15s ease;
}

.nav-logo:hover .logo-badge {
    transform: translate(-2px, -2px);
    box-shadow: 4px 4px 0px var(--nb-black);
    background-color: var(--nb-white);
    color: var(--nb-red);
}

/* Navigation Links List */
.nav-links {
    display: flex;
    align-items: center;
    gap: 5px;
    list-style: none;
}

.nav-item {
    position: relative;
}

/* Nav Link Buttons */
.nav-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 11px;
    font-family: var(--font-body);
    font-weight: 700;
    font-size: 0.88rem;
    color: var(--nb-black);
    text-decoration: none;
    background-color: transparent;
    border: 2px solid transparent;
    border-radius: var(--radius-btn);
    transition: all 0.15s ease;
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}

.nav-link:hover {
    background-color: var(--nb-white);
    border-color: var(--nb-black);
    box-shadow: var(--shadow-sm);
    transform: translate(-2px, -2px);
    color: var(--nb-black);
}

.nav-link.active {
    background-color: var(--nb-navy);
    color: var(--nb-white);
    border: var(--border-medium);
    box-shadow: var(--shadow-sm);
    transform: translate(-2px, -2px);
}

.nav-link.active:hover {
    background-color: var(--nb-navy-dark);
    color: var(--nb-yellow);
}

.dropdown-icon {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
    display: inline-block;
}

.nav-item.open .dropdown-icon {
    transform: rotate(180deg);
}

/* Sub Menu / Dropdown Box */
.sub-menu {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    background-color: var(--nb-white);
    border: var(--border-thick);
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    list-style: none;
    min-width: 220px;
    padding: 8px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
    z-index: 1100;
}

/* Align right for items on the right side of the navbar so dropdowns never cut off */
@media (min-width: 1081px) {
    .nav-item:last-child .sub-menu,
    .nav-item:nth-last-child(2) .sub-menu {
        left: auto;
        right: 0;
    }

    .nav-item:hover .sub-menu,
    .nav-item.open .sub-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
}

.sub-menu li {
    margin-bottom: 4px;
}

.sub-menu li:last-child {
    margin-bottom: 0;
}

.sub-menu a {
    display: block;
    padding: 10px 14px;
    font-family: var(--font-body);
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--nb-black);
    text-decoration: none;
    border: 2px solid transparent;
    border-radius: 8px;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.sub-menu a:hover,
.sub-menu a.active {
    background-color: var(--nb-yellow);
    border: var(--border-thin);
    box-shadow: 2px 2px 0px var(--nb-black);
    transform: translate(-2px, -2px);
    color: var(--nb-black);
}

/* Toggle Hamburger Button for Mobile */
.nav-toggle {
    display: none;
    background-color: var(--nb-white);
    border: var(--border-medium);
    border-radius: var(--radius-btn);
    box-shadow: 3px 3px 0px var(--nb-black);
    padding: 8px 10px;
    cursor: pointer;
    flex-direction: column;
    gap: 4px;
    justify-content: center;
    align-items: center;
    transition: all 0.15s ease;
}

.nav-toggle:hover {
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0px var(--nb-black);
    background-color: var(--nb-bg);
}

.nav-toggle:active {
    transform: translate(1px, 1px);
    box-shadow: 1px 1px 0px var(--nb-black);
}

.hamburger {
    display: block;
    width: 22px;
    height: 3.5px;
    background-color: var(--nb-black);
    border-radius: 2px;
    transition: transform 0.25s ease, opacity 0.25s ease;
}

.nav-toggle.open .hamburger:nth-child(1) {
    transform: translateY(7.5px) rotate(45deg);
}

.nav-toggle.open .hamburger:nth-child(2) {
    opacity: 0;
}

.nav-toggle.open .hamburger:nth-child(3) {
    transform: translateY(-7.5px) rotate(-45deg);
}

/* Responsive Styles */
@media (max-width: 1080px) {
    .nav-toggle {
        display: flex;
    }

    .nav-center {
        flex-direction: column;
        align-items: stretch;
    }

    .nav-header {
        width: 100%;
    }

    .nav-links {
        display: none;
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
        margin-top: 14px;
        padding-top: 14px;
        padding-bottom: 12px;
        border-top: var(--border-thin);
        width: 100%;
        max-height: calc(85vh - 70px);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .nav-links.show-links {
        display: flex;
    }

    .nav-link {
        width: 100%;
        justify-content: space-between;
        padding: 12px 16px;
        min-height: 44px;
        touch-action: manipulation;
    }

    .sub-menu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        border: var(--border-thin);
        background-color: var(--nb-bg);
        margin-top: 6px;
        margin-left: 8px;
        display: none;
        padding: 6px;
    }

    .sub-menu a {
        padding: 11px 14px;
        min-height: 44px;
        display: flex;
        align-items: center;
        touch-action: manipulation;
    }

    .nav-item.open .sub-menu {
        display: block;
    }
}

@media (max-width: 576px) {
    .navbar {
        width: 94%;
        padding: 10px 0;
    }

    .nav-logo {
        gap: 8px;
    }

    .logo-badge {
        font-size: 0.95rem;
        padding: 5px 10px;
    }

    .logo-img {
        height: 32px;
    }
}

@media (max-width: 400px) {
    .logo-badge {
        font-size: 0.85rem;
        padding: 4px 8px;
        letter-spacing: 0;
    }
    
    .logo-img-wrapper {
        padding: 3px 6px;
    }
    
    .logo-img {
        height: 28px;
    }

    .nav-toggle {
        padding: 6px 8px;
    }
}
</style>

<header class="header-wrapper">
    <nav class="navbar" id="navbar">
        <div class="nav-center">
            <!-- brand / logo -->
            <div class="nav-header">
                <a href="<?php echo $base_url; ?>index.php" class="nav-logo" title="SMKS SUKAPURA">
                    <div class="logo-img-wrapper">
                        <img src="<?php echo $base_url; ?>assets/favicon.ico" alt="Logo SMKS SUKAPURA" class="logo-img" onerror="this.onerror=null; this.src='<?php echo $base_url; ?>assets/favicon.ico';">
                    </div>
                    <span class="logo-badge">SMKS SUKAPURA</span>
                </a>
                <button class="nav-toggle" id="nav-toggle" aria-label="toggle navigation" aria-expanded="false">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </button>
            </div>

            <!-- navigation links -->
            <ul class="nav-links" id="nav-links">
                <li class="nav-item">
                    <a href="<?php echo $base_url; ?>index.php" class="nav-link <?php echo (isset($pageTitle) && $pageTitle === 'beranda') || ($current_page_file === 'index.php' && strpos($current_script_path, '/profil/') === false && strpos($current_script_path, '/data/') === false && strpos($current_script_path, '/program/') === false && strpos($current_script_path, '/galeri/') === false && strpos($current_script_path, '/berita/') === false && strpos($current_script_path, '/spmb/') === false && strpos($current_script_path, '/dpmb/') === false) ? 'active' : ''; ?>">
                        Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url; ?>berita/index.php" class="nav-link <?php echo (isset($pageTitle) && $pageTitle === 'berita') || strpos($current_script_path, '/berita/') !== false ? 'active' : ''; ?>">
                        Berita
                    </a>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo (isset($pageTitle) && $pageTitle === 'profil') || strpos($current_script_path, '/profil/') !== false ? 'active' : ''; ?>">
                        Profil <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo $base_url; ?>profil/index.php" class="<?php echo (isset($subPageTitle) && ($subPageTitle === 'profil' || $subPageTitle === 'profil-singkat')) || (strpos($current_script_path, '/profil/index.php') !== false) ? 'active' : ''; ?>">Profil Singkat</a></li>
                        <li><a href="<?php echo $base_url; ?>profil/visi-misi.php" class="<?php echo ($current_page_file === 'visi-misi.php' || (isset($subPageTitle) && $subPageTitle === 'visi-misi')) ? 'active' : ''; ?>">Visi Misi</a></li>
                        <li><a href="<?php echo $base_url; ?>profil/struktur-organisasi.php" class="<?php echo ($current_page_file === 'struktur-organisasi.php' || (isset($subPageTitle) && $subPageTitle === 'struktur-organisasi')) ? 'active' : ''; ?>">Struktur Organisasi</a></li>
                        <li><a href="<?php echo $base_url; ?>profil/kontak.php" class="<?php echo ($current_page_file === 'kontak.php' || (isset($subPageTitle) && $subPageTitle === 'kontak')) ? 'active' : ''; ?>">Kontak</a></li>
                    </ul>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo (isset($pageTitle) && $pageTitle === 'program unggulan') || strpos($current_script_path, '/program/') !== false ? 'active' : ''; ?>">
                        Program Unggulan <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo $base_url; ?>program/excellent-class.php" class="<?php echo ($current_page_file === 'excellent-class.php' || (isset($subPageTitle) && $subPageTitle === 'excellent-class')) ? 'active' : ''; ?>">Excellent Class</a></li>
                        <li><a href="<?php echo $base_url; ?>program/atlet-class.php" class="<?php echo ($current_page_file === 'atlet-class.php' || (isset($subPageTitle) && $subPageTitle === 'atlet-class')) ? 'active' : ''; ?>">Atlet Class</a></li>
                        <li><a href="<?php echo $base_url; ?>program/industri-class.php" class="<?php echo ($current_page_file === 'industri-class.php' || (isset($subPageTitle) && $subPageTitle === 'industri-class')) ? 'active' : ''; ?>">Industri Class</a></li>
                    </ul>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo (isset($pageTitle) && $pageTitle === 'data') || strpos($current_script_path, '/data/') !== false ? 'active' : ''; ?>">
                        Data <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo $base_url; ?>data/guru.php" class="<?php echo ($current_page_file === 'guru.php' || (isset($subPageTitle) && $subPageTitle === 'guru')) ? 'active' : ''; ?>">Guru</a></li>
                        <li><a href="<?php echo $base_url; ?>data/siswa.php" class="<?php echo ($current_page_file === 'siswa.php' || (isset($subPageTitle) && $subPageTitle === 'siswa')) ? 'active' : ''; ?>">Siswa</a></li>
                        <li><a href="<?php echo $base_url; ?>data/kelas.php" class="<?php echo ($current_page_file === 'kelas.php' || (isset($subPageTitle) && $subPageTitle === 'kelas')) ? 'active' : ''; ?>">Kelas</a></li>
                        <li><a href="<?php echo $base_url; ?>data/ekstrakurikuler.php" class="<?php echo ($current_page_file === 'ekstrakurikuler.php' || (isset($subPageTitle) && $subPageTitle === 'ekstrakurikuler')) ? 'active' : ''; ?>">Ekstrakurikuler</a></li>
                    </ul>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo (isset($pageTitle) && $pageTitle === 'galeri') || strpos($current_script_path, '/galeri/') !== false ? 'active' : ''; ?>">
                        Galeri Sekolah <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo $base_url; ?>galeri/index.php" class="<?php echo ($current_page_file === 'index.php' && strpos($current_script_path, '/galeri/') !== false) || (isset($subPageTitle) && $subPageTitle === 'kegiatan') ? 'active' : ''; ?>">Dokumentasi Kegiatan</a></li>
                        <li><a href="<?php echo $base_url; ?>galeri/prestasi.php" class="<?php echo ($current_page_file === 'prestasi.php' || (isset($subPageTitle) && $subPageTitle === 'prestasi')) ? 'active' : ''; ?>">Dokumentasi Prestasi</a></li>
                    </ul>
                </li>
                <li class="nav-item has-dropdown">
                    <?php 
                    $isSpmbActive = (isset($pageTitle) && ($pageTitle === 'spmb' || $pageTitle === 'dpmb')) || strpos($current_script_path, '/spmb/') !== false || strpos($current_script_path, '/dpmb/') !== false;
                    ?>
                    <a href="<?php echo $base_url; ?>spmb/index.php" class="nav-link <?php echo $isSpmbActive ? 'active' : ''; ?>" style="<?php echo $isSpmbActive ? '' : 'background-color:var(--nb-yellow); color:var(--nb-black); border:var(--border-thin); box-shadow:var(--shadow-sm);'; ?>">
                        <i class="ph-bold ph-sparkle" style="<?php echo $isSpmbActive ? 'color:var(--nb-yellow);' : 'color:var(--nb-red);'; ?>"></i> SPMB <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo $base_url; ?>spmb/index.php" class="<?php echo ($current_page_file === 'index.php' && strpos($current_script_path, '/spmb/') !== false) ? 'active' : ''; ?>">Beranda SPMB</a></li>
                        <li><a href="<?php echo $base_url; ?>spmb/daftar.php" class="<?php echo ($current_page_file === 'daftar.php') ? 'active' : ''; ?>">Formulir Pendaftaran</a></li>
                        <li><a href="<?php echo $base_url; ?>spmb/cek-status.php" class="<?php echo ($current_page_file === 'cek-status.php') ? 'active' : ''; ?>">Cek Status Pendaftaran</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <script src="<?php echo $base_url; ?>script.js?v=<?php echo @filemtime(__DIR__ . '/../script.js') ?: time(); ?>" defer></script>
</header>