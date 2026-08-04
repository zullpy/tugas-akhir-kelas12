<?php
$base_url = '../';
$pageTitle = 'profil';
$subPageTitle = 'struktur-organisasi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Organisasi | SMKS SUKAPURA</title>
    <meta name="description" content="Struktur Organisasi SMKS SUKAPURA Kab. Tasikmalaya - Susunan kepemimpinan dan manajemen sekolah">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <!-- Hero Banner -->
    <header class="profile-hero">
        <img src="../assets/bawah.webp" alt="Banner Struktur Organisasi">
        <div class="background-overlay"></div>
        <div class="hero-title-overlay">
            <div class="hero-badge"><i class="ph-bold ph-tree-structure"></i> PROFIL SEKOLAH</div>
            <h1 class="hero-heading">Struktur Organisasi</h1>
            <p class="hero-sub">SMKS Sukapura Kab. Tasikmalaya</p>
        </div>
    </header>

    <main class="struktur-container">

        <!-- Section header -->
        <div class="profile-header reveal">
            <h2 class="title">BAGAN STRUKTUR ORGANISASI</h2>
        </div>

        <!-- Diagram card -->
        <div class="struktur-card reveal">
            <div class="struktur-card-header">
                <span class="struktur-badge"><i class="ph-bold ph-tree-structure"></i> SMKS SUKAPURA</span>
                <span class="struktur-year">Tahun Pelajaran 2024/2025</span>
            </div>
            <div class="struktur-img-wrapper">
                <img src="../assets/struktur.png" alt="Bagan Struktur Organisasi SMKS SUKAPURA" class="struktur-img">
            </div>
            <div class="struktur-card-footer">
                <a href="../assets/struktur.png" download class="struktur-download-btn" id="btn-download-struktur">
                    <i class="ph-bold ph-download-simple"></i> Unduh Bagan
                </a>
                <a href="../assets/struktur.png" target="_blank" class="struktur-view-btn" id="btn-view-struktur">
                    <i class="ph-bold ph-arrows-out"></i> Lihat Penuh
                </a>
            </div>
        </div>

        <!-- Info Cards Row -->
        <div class="struktur-info-grid">
            <div class="info-card reveal reveal-delay-1">
                <div class="info-icon-box" style="background:#FFE600;">
                    <i class="ph-bold ph-crown"></i>
                </div>
                <div class="info-text">
                    <span class="info-label">Kepala Sekolah</span>
                    <span class="info-value">Pimpinan tertinggi satuan pendidikan</span>
                </div>
            </div>
            <div class="info-card reveal reveal-delay-2">
                <div class="info-icon-box" style="background:#4ADE80;">
                    <i class="ph-bold ph-users-three"></i>
                </div>
                <div class="info-text">
                    <span class="info-label">Wakil Kepala Sekolah</span>
                    <span class="info-value">Bidang Kurikulum, Kesiswaan &amp; Humas</span>
                </div>
            </div>
            <div class="info-card reveal reveal-delay-3">
                <div class="info-icon-box" style="background:#38BDF8;">
                    <i class="ph-bold ph-chalkboard-teacher"></i>
                </div>
                <div class="info-text">
                    <span class="info-label">Tenaga Pendidik</span>
                    <span class="info-value">Guru produktif &amp; normatif adaptif</span>
                </div>
            </div>
            <div class="info-card reveal reveal-delay-4">
                <div class="info-icon-box" style="background:#F472B6;">
                    <i class="ph-bold ph-graduation-cap"></i>
                </div>
                <div class="info-text">
                    <span class="info-label">Peserta Didik</span>
                    <span class="info-value">Siswa-siswi SMKS SUKAPURA</span>
                </div>
            </div>
        </div>

    </main>

    <?php include '../components/footer.php'; ?>
    <script src="<?php echo $base_url; ?>script.js"></script>
</body>
</html>