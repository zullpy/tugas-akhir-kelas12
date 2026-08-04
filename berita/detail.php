<?php
$base_url = '../';
$pageTitle = 'berita';
$subPageTitle = 'berita';

require_once __DIR__ . '/data-berita.php';

// Ambil ID dari param GET, default ID 1
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Cari data berita berdasarkan ID
$article = null;
foreach ($berita as $item) {
    if ($item['id'] === $id) {
        $article = $item;
        break;
    }
}

// Jika berita tidak ditemukan, pakai berita pertama sebagai fallback
if (!$article) {
    $article = $berita[0];
}

$kat = $article['kategori'];
$bg  = $kategori_warna[$kat] ?? '#FFE600';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['judul']); ?> | SMKS SUKAPURA</title>
    <meta name="description" content="<?php echo htmlspecialchars($article['ringkasan']); ?>">
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

    <main class="berita-main" style="padding-top: 40px;">

        <!-- BREADCRUMB & BACK BUTTON -->
        <div class="berita-back-wrap">
            <a href="index.php" class="berita-back-btn">
                <i class="ph-bold ph-arrow-left"></i> Kembali ke Berita
            </a>
        </div>

        <!-- DETAIL ARTICLE -->
        <article class="berita-detail-card">
            <!-- Header artikel -->
            <header class="berita-detail-header">
                <div class="berita-kategori-badge" style="background-color:<?php echo $bg; ?>"><?php echo htmlspecialchars($article['kategori']); ?></div>
                <h1 class="berita-detail-title"><?php echo htmlspecialchars($article['judul']); ?></h1>
                
                <div class="berita-detail-meta">
                    <span><i class="ph-bold ph-calendar-blank"></i> <?php echo htmlspecialchars($article['tanggal']); ?></span>
                    <span><i class="ph-bold ph-user-circle"></i> <?php echo htmlspecialchars($article['penulis']); ?></span>
                </div>
            </header>

            <!-- Main Image -->
            <div class="berita-detail-img-wrap">
                <img src="<?php echo htmlspecialchars($article['gambar']); ?>" alt="<?php echo htmlspecialchars($article['judul']); ?>" class="berita-detail-img">
            </div>

            <!-- Content Paragraphs -->
            <div class="berita-detail-content">
                <?php foreach ($article['konten'] as $paragraph): ?>
                    <p><?php echo htmlspecialchars($paragraph); ?></p>
                <?php endforeach; ?>
            </div>

            <!-- Footer / Share & Back -->
            <footer class="berita-detail-footer">
                <a href="index.php" class="berita-back-btn">
                    <i class="ph-bold ph-arrow-left"></i> Lihat Berita Lainnya
                </a>
            </footer>
        </article>

        <!-- RELATED NEWS SECTION -->
        <section class="berita-list-section" style="margin-top: 60px;">
            <div class="berita-section-badge"><i class="ph-bold ph-newspaper"></i> BERITA TERKAIT LAINNYA</div>
            <div class="berita-grid">
                <?php 
                $count = 0;
                foreach ($berita as $rel): 
                    if ($rel['id'] === $article['id']) continue;
                    if ($count >= 3) break;
                    $count++;
                    $rKat = $rel['kategori'];
                    $rBg  = $kategori_warna[$rKat] ?? '#FFE600';
                ?>
                <article class="berita-card">
                    <a href="detail.php?id=<?php echo $rel['id']; ?>" class="berita-card-img">
                        <img src="<?php echo htmlspecialchars($rel['gambar']); ?>" alt="<?php echo htmlspecialchars($rel['judul']); ?>">
                    </a>
                    <div class="berita-card-body">
                        <div class="berita-kategori-badge" style="background-color:<?php echo $rBg; ?>"><?php echo htmlspecialchars($rKat); ?></div>
                        <h3 class="berita-card-title">
                            <a href="detail.php?id=<?php echo $rel['id']; ?>" style="text-decoration:none; color:inherit;"><?php echo htmlspecialchars($rel['judul']); ?></a>
                        </h3>
                        <p class="berita-card-desc"><?php echo htmlspecialchars($rel['ringkasan']); ?></p>
                        <div class="berita-card-footer">
                            <span class="berita-meta"><i class="ph-bold ph-calendar-blank"></i> <?php echo htmlspecialchars($rel['tanggal']); ?></span>
                            <a href="detail.php?id=<?php echo $rel['id']; ?>" class="berita-card-link">Baca <i class="ph-bold ph-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <?php include '../components/footer.php'; ?>
    <script src="<?php echo $base_url; ?>script.js"></script>
</body>
</html>
