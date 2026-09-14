<?php
$base_url = '../';
$pageTitle = 'galeri';
$subPageTitle = 'kegiatan';

// Load sistem database jika tersedia
$dbPhotos = [];
if (file_exists(__DIR__ . '/../spmb/config.php')) {
    require_once __DIR__ . '/../spmb/config.php';
    if (function_exists('get_galeri_photos')) {
        $allGaleri = get_galeri_photos();
        foreach ($allGaleri as $g) {
            $cat = $g['kategori'];
            $path = $g['file_path'];
            if (!preg_match('/^https?:\/\//', $path)) {
                $path = '../' . ltrim($path, '/');
            }
            $dbPhotos[$cat][] = [
                'path'  => $path,
                'title' => $g['judul'],
                'desc'  => $g['deskripsi'] ?? ''
            ];
        }
    }
}

$categories = [
    'lomba' => [
        'badge' => 'KEGIATAN LOMBA',
        'title' => 'Dokumentasi Lomba 17 Agustus',
        'subtitle' => 'Keseruan dan partisipasi siswa dalam berbagai cabang perlombaan pada 17 agustus'
    ],
    'istigosah' => [
        'badge' => 'KEGIATAN KEAGAMAAN',
        'title' => 'Dokumentasi Istigosah',
        'subtitle' => 'Kegiatan doa bersama dan istigosah siswa & guru SMKS Sukapura'
    ],
    'porsekas' => [
        'badge' => 'PEKAN OLAHRAGA & SENI',
        'title' => 'Kegiatan Porsekas',
        'subtitle' => 'Pekan Olahraga dan Seni antar kelas SMKS Sukapura'
    ],
    'sertijab' => [
        'badge' => 'ORGANISASI & OSIS',
        'title' => 'Serah Terima Jabatan (Sertijab)',
        'subtitle' => 'Dokumentasi pelantikan dan sertijab pengurus OSIS & Ekstrakurikuler'
    ],
    'tka' => [
        'badge' => 'AKADEMIK',
        'title' => 'Tes Kemampuan Akademik (TKA) dan Ujian Sekolah',
        'subtitle' => 'Dokumentasi pelaksanaan Tes Kemampuan Akademik serta ujian-ujian lainnya di SMKS Sukapura'
    ],
    'upacara' => [
        'badge' => 'UPACARA BENDERA',
        'title' => 'Upacara Bendera',
        'subtitle' => 'Dokumentasi upacara rutin dan peringatan hari besar nasional'
    ],
    'expo' => [
        'badge' => 'PAMERAN & EXPO',
        'title' => 'Expo & Pameran Karya',
        'subtitle' => 'Pameran karya inovasi dan pameran hasil karya siswa SMKS Sukapura'
    ],
    'prestasi' => [
        'badge' => 'PRESTASI SISWA',
        'title' => 'Dokumentasi Prestasi',
        'subtitle' => 'Momen penganugerahan dan apresiasi juara siswa-siswi SMKS Sukapura'
    ]
];

// Gabungkan kategori tambahan dari database jika ada
foreach ($dbPhotos as $catKey => $items) {
    if (!isset($categories[$catKey])) {
        $categories[$catKey] = [
            'badge' => 'KEGIATAN SEKOLAH',
            'title' => 'Dokumentasi ' . ucfirst($catKey),
            'subtitle' => 'Kumpulan dokumentasi kegiatan ' . htmlspecialchars($catKey)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi Kegiatan | SMKS SUKAPURA</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"
    />
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"
    />
    <link rel="stylesheet" href="style.css?v=<?php echo @filemtime(__DIR__ . '/style.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <!-- Hero Banner -->
    <header class="profile-hero">
        <img src="../assets/bawah.webp" alt="Banner Dokumentasi Kegiatan">
        <div class="background-overlay"></div>
        <div class="hero-title-overlay">
            <div class="hero-badge"><i class="ph-bold ph-image"></i> GALERI SEKOLAH</div>
            <h1 class="hero-heading">Dokumentasi Kegiatan Sekolah</h1>
            <p class="hero-sub">Kumpulan Foto Kegiatan & Events SMKS Sukapura</p>
        </div>
    </header>

    <main class="gallery-section">
        <?php foreach ($categories as $catKey => $catInfo): ?>
            <?php
            $photos = [];
            if (!empty($dbPhotos[$catKey])) {
                $photos = $dbPhotos[$catKey];
            } else {
                $dirPath = "../assets/galeri/" . $catKey;
                if (is_dir($dirPath)) {
                    $files = scandir($dirPath);
                    natsort($files);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                                $photos[] = [
                                    'path'  => "../assets/galeri/" . $catKey . "/" . $file,
                                    'title' => $catInfo['title'],
                                    'desc'  => ''
                                ];
                            }
                        }
                    }
                }
            }
            ?>
            <?php if (!empty($photos)): ?>
                <section class="gallery-category-section" id="section-<?php echo $catKey; ?>">
                    <div class="category-header">
                        <div class="category-title-group">
                            <span class="category-badge"><i class="ph-bold ph-images"></i> <?php echo $catInfo['badge']; ?></span>
                            <h2 class="category-title"><?php echo $catInfo['title']; ?></h2>
                            <p class="category-subtitle"><?php echo $catInfo['subtitle']; ?></p>
                        </div>
                        <?php if (count($photos) > 4): ?>
                            <button class="btn-see-more" type="button">
                                <span>Lihat Selengkapnya</span> <i class="ph-bold ph-caret-down"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="gallery-grid">
                        <?php foreach ($photos as $index => $pItem): ?>
                            <div class="gallery-card <?php echo $index >= 4 ? 'hidden-photo' : ''; ?>" data-category-title="<?php echo htmlspecialchars($pItem['title'] ?: $catInfo['title']); ?>">
                                <div class="gallery-img-container">
                                    <img src="<?php echo htmlspecialchars($pItem['path']); ?>" alt="<?php echo htmlspecialchars($pItem['title'] ?: $catInfo['title']); ?>" loading="lazy">
                                    <div class="gallery-overlay">
                                        <div class="gallery-overlay-icon">
                                            <i class="ph-bold ph-arrows-out-simple"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    </main>

    <!-- Lightbox Modal - Pure Full Preview with Category Header -->
    <div class="lightbox-modal" id="lightbox-modal">
        <div class="lightbox-content">
            <div class="lightbox-header">
                <span class="lightbox-category-title" id="lightbox-category-title">
                    <i class="ph-bold ph-image"></i> Dokumentasi Kegiatan
                </span>
                <button class="lightbox-close" id="lightbox-close" aria-label="Tutup">&times;</button>
            </div>
            <div class="lightbox-body">
                <button class="lightbox-nav lightbox-prev" id="lightbox-prev" aria-label="Sebelumnya">&#8249;</button>
                <img src="" alt="Full Preview Foto" id="lightbox-img">
                <button class="lightbox-nav lightbox-next" id="lightbox-next" aria-label="Berikutnya">&#8250;</button>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
    <script src="<?php echo $base_url; ?>script.js?v=<?php echo @filemtime(__DIR__ . '/../script.js') ?: time(); ?>"></script>
    <script src="script.js?v=<?php echo @filemtime(__DIR__ . '/script.js') ?: time(); ?>"></script>
</body>
</html>