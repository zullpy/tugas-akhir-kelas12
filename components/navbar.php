<!-- components/navbar.php -->
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
    margin: 0;
    background-color: var(--nb-yellow);
    border-bottom: var(--border-thick);
    box-shadow: 0 4px 0px var(--nb-black);
    position: relative;
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
    gap: 8px;
    list-style: none;
}

.nav-item {
    position: relative;
}

/* Nav Link Buttons */
.nav-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    font-family: var(--font-body);
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--nb-black);
    text-decoration: none;
    background-color: transparent;
    border: 2.5px solid transparent;
    border-radius: var(--radius-btn);
    transition: all 0.15s ease;
    cursor: pointer;
    user-select: none;
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

@media (min-width: 993px) {
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
}

.sub-menu a:hover {
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
@media (max-width: 992px) {
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
        margin-top: 16px;
        padding-top: 16px;
        border-top: var(--border-thin);
        width: 100%;
    }

    .nav-links.show-links {
        display: flex;
    }

    .nav-link {
        width: 100%;
        justify-content: space-between;
        padding: 12px 16px;
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
        margin-left: 12px;
        display: none;
    }

    .nav-item.open .sub-menu {
        display: block;
    }
}
</style>

<header class="header-wrapper">
    <nav class="navbar" id="navbar">
        <div class="nav-center">
            <!-- brand / logo -->
            <div class="nav-header">
                <a href="index.php" class="nav-logo" title="SMKS SUKAPURA">
                    <div class="logo-img-wrapper">
                        <img src="assets/favicon.ico" alt="Logo SMKS SUKAPURA" class="logo-img" onerror="this.onerror=null; this.src='assets/favicon.ico';">
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
                    <a href="index.php" class="nav-link <?php echo isset($pageTitle) && $pageTitle === 'beranda' ? 'active' : ''; ?>">
                        Beranda
                    </a>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo isset($pageTitle) && $pageTitle === 'profil' ? 'active' : ''; ?>">
                        Profil <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="profil/visi-misi.php">Visi Misi</a></li>
                        <li><a href="profil/struktur-organisasi.php">Struktur Organisasi</a></li>
                    </ul>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo isset($pageTitle) && $pageTitle === 'program unggulan' ? 'active' : ''; ?>">
                        Program Unggulan <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="program/excellent-class.php">Excellent Class</a></li>
                        <li><a href="program/atlet-class.php">Atlet Class</a></li>
                    </ul>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link <?php echo isset($pageTitle) && $pageTitle === 'data' ? 'active' : ''; ?>">
                        Data <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="data/guru.php">Guru</a></li>
                        <li><a href="data/siswa.php">Siswa</a></li>
                        <li><a href="data/kelas.php">Kelas</a></li>
                        <li><a href="data/ekstrakurikuler.php">Ekstrakurikuler</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="galeri.php" class="nav-link <?php echo isset($pageTitle) && $pageTitle === 'galeri' ? 'active' : ''; ?>">
                        Galeri Sekolah
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>