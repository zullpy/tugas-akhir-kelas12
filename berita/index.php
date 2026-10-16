<?php
$base_url = '../';
$pageTitle = 'berita';
$subPageTitle = 'berita';

require_once __DIR__ . '/data-berita.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Sekolah | SMKS SUKAPURA</title>
    <meta name="description" content="Berita terkini SMKS Sukapura — Informasi terbaru seputar kegiatan, prestasi, dan pengumuman penting dari SMKS Sukapura Kab. Tasikmalaya.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="style.css?v=<?php echo @filemtime(__DIR__ . '/style.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <!-- HERO -->
    <header class="berita-hero">
        <div class="berita-hero-bg"></div>
        <div class="berita-hero-content">
            <div class="berita-hero-badge"><i class="ph-bold ph-newspaper"></i> SMKS SUKAPURA</div>
            <h1 class="berita-hero-heading">Berita &amp; Informasi</h1>
            <p class="berita-hero-sub">Ikuti perkembangan terkini SMKS Sukapura — prestasi, kegiatan, dan pengumuman penting.</p>
        </div>
    </header>

    <main class="berita-main">

        <!-- JIKA BELUM ADA BERITA -->
        <?php if (empty($berita)): ?>
            <div style="text-align:center; padding:60px 20px; background:#fff; border:3px solid #000; border-radius:14px; box-shadow:6px 6px 0px #000; margin:40px 0;">
                <div style="width:64px; height:64px; background:#FEF9C3; border:2px solid #000; border-radius:12px; margin:0 auto 16px; display:flex; align-items:center; justify-content:center; font-size:2rem; box-shadow:3px 3px 0px #000;">
                    <i class="ph-bold ph-newspaper-clipping"></i>
                </div>
                <h3 style="font-family:'Space Grotesk', sans-serif; font-size:1.4rem; font-weight:800; margin-bottom:8px;">Belum Ada Berita Diterbitkan</h3>
                <p style="color:#64748B; max-width:440px; margin:0 auto;">Saat ini belum ada artikel berita yang dipublikasikan. Silakan kunjungi kembali di lain waktu.</p>
            </div>
        <?php else: ?>

        <!-- BERITA UTAMA (featured) -->
        <?php 
        $featured = $berita[0]; 
        $otherBerita = array_slice($berita, 1);
        ?>
        <section class="berita-featured-section">
            <div class="berita-section-badge"><i class="ph-bold ph-star"></i> BERITA TERKINI</div>
            <div class="berita-featured-card">
                <a href="detail.php?id=<?php echo $featured['id']; ?>" class="berita-featured-img">
                    <img src="<?php echo htmlspecialchars($featured['gambar']); ?>" alt="<?php echo htmlspecialchars($featured['judul']); ?>">
                    <div class="berita-featured-overlay"></div>
                </a>
                <div class="berita-featured-body">
                    <?php
                    $kat = $featured['kategori'];
                    $bg  = $kategori_warna[$kat] ?? '#FFE600';
                    ?>
                    <div class="berita-kategori-badge" style="background-color:<?php echo $bg; ?>"><?php echo htmlspecialchars($kat); ?></div>
                    <h2 class="berita-featured-title">
                        <a href="detail.php?id=<?php echo $featured['id']; ?>" style="text-decoration:none; color:inherit;"><?php echo htmlspecialchars($featured['judul']); ?></a>
                    </h2>
                    <p class="berita-featured-desc"><?php echo htmlspecialchars($featured['ringkasan']); ?></p>
                    <div class="berita-meta">
                        <span><i class="ph-bold ph-calendar-blank"></i> <?php echo htmlspecialchars($featured['tanggal']); ?></span>
                    </div>
                    <a href="detail.php?id=<?php echo $featured['id']; ?>" class="berita-read-btn">
                        <i class="ph-bold ph-arrow-right"></i> Baca Selengkapnya
                    </a>
                </div>
            </div>
        </section>

        <!-- SEMUA BERITA -->
        <?php if (!empty($otherBerita)): ?>
        <section class="berita-list-section">
            <div class="berita-section-badge"><i class="ph-bold ph-list-bullets"></i> SEMUA BERITA</div>
            <div class="berita-grid">
                <?php foreach ($otherBerita as $item): ?>
                <?php
                $kat = $item['kategori'];
                $bg  = $kategori_warna[$kat] ?? '#FFE600';
                ?>
                <article class="berita-card">
                    <a href="detail.php?id=<?php echo $item['id']; ?>" class="berita-card-img">
                        <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>">
                    </a>
                    <div class="berita-card-body">
                        <div class="berita-kategori-badge" style="background-color:<?php echo $bg; ?>"><?php echo htmlspecialchars($kat); ?></div>
                        <h3 class="berita-card-title">
                            <a href="detail.php?id=<?php echo $item['id']; ?>" style="text-decoration:none; color:inherit;"><?php echo htmlspecialchars($item['judul']); ?></a>
                        </h3>
                        <p class="berita-card-desc"><?php echo htmlspecialchars($item['ringkasan']); ?></p>
                        <div class="berita-card-footer">
                            <span class="berita-meta"><i class="ph-bold ph-calendar-blank"></i> <?php echo htmlspecialchars($item['tanggal']); ?></span>
                            <a href="detail.php?id=<?php echo $item['id']; ?>" class="berita-card-link">Baca <i class="ph-bold ph-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        <?php endif; ?>

    </main>

    <?php include '../components/footer.php'; ?>
    <script src="<?php echo $base_url; ?>script.js?v=<?php echo @filemtime(__DIR__ . '/../script.js') ?: time(); ?>"></script>
</body>
</html>
