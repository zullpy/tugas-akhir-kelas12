<?php
$pageTitle = "beranda";

// Ambil info SPMB terkini dari sistem database SPMB
$spmbSettings = [];
if (file_exists(__DIR__ . '/spmb/config.php')) {
    require_once __DIR__ . '/spmb/config.php';
    if (function_exists('get_all_settings')) {
        try {
            $spmbSettings = get_all_settings();
        } catch (Exception $e) {
            // fallback
        }
    }
}

$statusInfo = function_exists('get_spmb_status_info') ? get_spmb_status_info($spmbSettings) : [
    'isOpen' => true,
    'label' => 'Sedang Dibuka',
    'badgeColor' => '#16A34A',
    'note' => ''
];
$isSpmbBuka = $statusInfo['isOpen'];
$spmbTahun = $spmbSettings['tahun_ajaran'] ?? '2027-2028';
$spmbGelombang = $spmbSettings['gelombang'] ?? 'Gelombang 1';
$spmbPeriode = $spmbSettings['periode_gelombang'] ?? '';
$spmbJudul = !empty($spmbSettings['pengumuman_header']) ? $spmbSettings['pengumuman_header'] : 'Pendaftaran Peserta Didik Baru (SPMB)';

// Ambil pengumuman aktif untuk ditampilkan di bawah sambutan kepala sekolah
$pengumumanList = [];
if (function_exists('get_active_pengumuman')) {
    $pengumumanList = get_active_pengumuman(6);
}

// Ambil konfigurasi popup pengumuman masuk web
$popupConfig = function_exists('get_popup_pengumuman_config') ? get_popup_pengumuman_config() : [
    'aktif' => false
];

// Ambil foto galeri yang dipilih admin untuk tampil di Beranda
$berandaPhotos = [];
if (function_exists('get_galeri_photos')) {
    $berandaPhotos = get_galeri_photos(null, true, 8);
    // Fallback jika belum ada yang ditandai beranda
    if (empty($berandaPhotos)) {
        $berandaPhotos = get_galeri_photos(null, false, 6);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | SMKS SUKAPURA</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
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
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body>

<?php include 'components/navbar.php'; ?>

<div class="hero-section">
    <div class="carousel">
        <div class="carousel-inner">
            <div class="carousel-slide active">
                <img src="assets/depan.webp" alt="SMKS Sukapura" class="img-building-front">
                <div class="carousel-caption">
                    <span class="caption-tag">BELAJAR & BERKARYA</span>
                    <h2>Tempat Tumbuh Bakat dan Prestasi</h2>
                    <p>Kami menghadirkan pendidikan berkualitas yang mengembangkan akademik, keterampilan, dan nilai karakter untuk masa depan yang lebih cerah.</p>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="assets/bawah.webp" alt="Fasilitas SMKS Sukapura" class="img-building-courtyard">
                <div class="carousel-caption">
                    <span class="caption-tag">FASILITAS UNGGULAN</span>
                    <h2>Fasilitas Praktik Modern</h2>
                    <p>Mendukung pembelajaran interaktif berbasis standar industri terbaru untuk semua kompetensi keahlian.</p>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="assets/welcome.webp" alt="Kemitraan SMKS Sukapura" class="img-banner">
                <div class="carousel-caption">
                    <span class="caption-tag">KARIR & MASA DEPAN</span>
                    <h2>Kemitraan Industri Luas</h2>
                    <p>Menjamin peluang magang dan penyerapan kerja lulusan terbaik di berbagai perusahaan terkemuka.</p>
                </div>
            </div>
        </div>


        <!-- Indicators -->
        <div class="carousel-indicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
        </div>
    </div>

    <!-- Floating Hero Stats Bar -->
    <div class="hero-stats-wrapper">
        <div class="hero-stats-container">
            <div class="stat-item">
                <div class="stat-value">A</div>
                <div class="stat-label">Akreditasi</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">1300+</div>
                <div class="stat-label">Siswa</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">80+</div>
                <div class="stat-label">Guru & Staf</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">10+</div>
                <div class="stat-label">Ekstrakurikuler</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">10</div>
                <div class="stat-label">Jurusan</div>
            </div>
        </div>
    </div>
</div>

<main style="width: 92%; max-width: 1200px; margin: 75px auto 50px; min-height: 400px;">
    <!-- SPMB BANNER ANNOUNCEMENT (DYNAMIC FROM ADMIN) -->
    <div class="spmb-home-banner">
        <div class="spmb-banner-left">
            <div class="spmb-banner-icon">
                <i class="ph-bold ph-megaphone"></i>
            </div>
            <div class="spmb-banner-content">
                <div class="spmb-banner-header">
                    <span class="spmb-badge-status" style="background: <?php echo $statusInfo['badgeColor']; ?>;">
                        <?php echo htmlspecialchars($statusInfo['label']); ?>
                    </span>
                </div>
                <h3 class="spmb-banner-title">
                    <?php echo htmlspecialchars($spmbJudul); ?>
                </h3>
                <?php if (!empty($spmbPeriode)): ?>
                    <div class="spmb-banner-periode">
                        <i class="ph-bold ph-calendar"></i> Periode: <?php echo htmlspecialchars($spmbPeriode); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="spmb-banner-actions">
            <?php if ($isSpmbBuka): ?>
                <a href="spmb/daftar.php" class="spmb-btn spmb-btn-primary">
                    <i class="ph-bold ph-pencil-simple"></i> Daftar Sekarang
                </a>
                <a href="spmb/index.php" class="spmb-btn spmb-btn-secondary">
                    Info Lengkap <i class="ph-bold ph-arrow-right"></i>
                </a>
            <?php else: ?>
                <a href="spmb/index.php" class="spmb-btn spmb-btn-primary">
                    <i class="ph-bold ph-info"></i> Info &amp; Alur SPMB
                </a>
                <a href="spmb/cek-status.php" class="spmb-btn spmb-btn-secondary">
                    <i class="ph-bold ph-magnifying-glass"></i> Cek Status
                </a>
            <?php endif; ?>
        </div>
    </div>

    <section class="sambutan-section">
        <div class="section-header reveal">
            <h2 class="section-title">Sambutan Kepala Sekolah</h2>
            <p class="section-subtitle">SMK Sukapura Kab. Tasikmalaya</p>
        </div>

        <div class="sambutan-grid">
            <div class="sambutan-left reveal-left">
                <div class="kepala-card">
                    <div class="kepala-img-wrapper">
                        <img src="assets/kepala.png" alt="Dedi Ah Kurniadi, S.Pd., M.Pd." class="kepala-img">
                    </div>
                    <div class="kepala-info">
                        <h3 class="kepala-name">Dedi Ah Kurniadi, S.Pd., M.Pd.</h3>
                        <span class="kepala-title">KEPALA SEKOLAH</span>
                    </div>
                </div>
            </div>

            <div class="sambutan-right reveal-right">
                <div class="sambutan-content">
                    <div class="quote-icon">&ldquo;</div>
                    <div class="greeting-header">
                        <h3 class="greeting-opening">Assalamu'alaikum Warahmatullahi Wabarakatuh,</h3>
                        <p class="greeting-sub">Salam sejahtera untuk kita semua,</p>
                    </div>
                    <div class="sambutan-body">
                        <p>Puji syukur kita panjatkan ke hadirat Allah SWT atas limpahan rahmat dan karunia-Nya sehingga website sekolah ini dapat hadir sebagai sarana informasi, komunikasi, dan inspirasi bagi seluruh warga sekolah serta masyarakat luas.</p>
                        <p>Website ini kami hadirkan sebagai wujud komitmen untuk mendukung <strong>pendidikan yang adaptif</strong> terhadap perkembangan teknologi. Melalui platform ini, kami berharap dapat memberikan akses informasi yang cepat, akurat, dan transparan terkait kegiatan, prestasi, dan layanan sekolah.</p>
                        <p>Kami juga mengundang seluruh siswa, guru, orang tua, dan masyarakat untuk berpartisipasi aktif dalam mengisi dan memanfaatkan website ini demi kemajuan pendidikan. Semoga melalui kerja sama dan sinergi, kita dapat mencetak <strong>generasi yang Unggul, Berkarakter, dan Berprestasi</strong>.</p>
                        <p>Terima kasih atas dukungan dan kepercayaan yang telah diberikan kepada sekolah kami. Mari bersama-sama membangun pendidikan yang lebih baik.</p>
                    </div>
                    <p class="greeting-closing">Waalaikumsalam Warahmatullahi Wabarakatuh.</p>
                    <div class="sambutan-footer-note">
                        <span class="note-location"><i class="ph-bold ph-map-pin"></i> Kab. Tasikmalaya, Jawa Barat</span>
                        <span class="note-tag">SMK SUKAPURA: BISA • BEDA • JUARA • LUAR BIASA!</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION PENGUMUMAN RESMI SEKOLAH (DIBAWAH SAMBUTAN KEPALA SEKOLAH)
         ========================================================================= -->
    <section class="pengumuman-section" id="pengumuman">
        <div class="section-header reveal">
            <div class="section-header-flex">
                <div>
                    <span class="section-badge badge-red">
                        <i class="ph-bold ph-megaphone-simple"></i> INFORMASI RESMI
                    </span>
                    <h2 class="section-title">Pengumuman Sekolah</h2>
                    <p class="section-subtitle">Pemberitahuan terkini, agenda penting, dan edaran resmi SMKS Sukapura</p>
                </div>
            </div>
        </div>

        <?php if (empty($pengumumanList)): ?>
            <div class="pengumuman-empty reveal">
                <div class="pengumuman-empty-icon"><i class="ph-bold ph-bell-slash"></i></div>
                <h3 class="pengumuman-empty-title">Belum Ada Pengumuman Baru</h3>
                <p class="pengumuman-empty-desc">Saat ini belum ada pengumuman resmi yang dipublikasikan. Silakan periksa kembali di lain waktu.</p>
            </div>
        <?php else: ?>
            <?php 
            $pinnedItem = null;
            $regularItems = [];
            foreach ($pengumumanList as $item) {
                if ($item['is_pinned'] == 1 && $pinnedItem === null) {
                    $pinnedItem = $item;
                } else {
                    $regularItems[] = $item;
                }
            }
            ?>

            <?php if ($pinnedItem): 
                $pinnedCatSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $pinnedItem['kategori']));
            ?>
                <div class="pengumuman-pinned-card reveal">
                    <div class="pinned-card-main">
                        <div class="pengumuman-card-meta" style="margin-bottom:10px;">
                            <span class="cat-pill cat-pill-<?php echo $pinnedCatSlug; ?>">
                                <?php echo htmlspecialchars($pinnedItem['kategori']); ?>
                            </span>
                            <span class="meta-date">
                                <i class="ph-bold ph-calendar-blank"></i> <?php echo format_tanggal_indonesia($pinnedItem['tanggal'], true); ?>
                            </span>
                        </div>
                        <h3 class="pinned-card-title"><?php echo htmlspecialchars($pinnedItem['judul']); ?></h3>
                        <div class="pinned-card-desc">
                            <?php echo nl2br(htmlspecialchars(mb_substr(strip_tags($pinnedItem['isi']), 0, 240))); ?><?php echo (mb_strlen($pinnedItem['isi']) > 240) ? '...' : ''; ?>
                        </div>
                        <div class="pengumuman-card-actions">
                            <button type="button" class="pengumuman-btn pengumuman-btn-primary" onclick='openDetailPengumuman(<?php echo json_encode($pinnedItem, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                <i class="ph-bold ph-eye"></i> Baca Selengkapnya
                            </button>
                            <?php if (!empty($pinnedItem['file_lampiran'])): ?>
                                <a href="<?php echo htmlspecialchars(ltrim($pinnedItem['file_lampiran'], '/')); ?>" target="_blank" class="pengumuman-btn pengumuman-btn-secondary">
                                    <i class="ph-bold ph-file-arrow-down"></i> Unduh Berkas
                                </a>
                            <?php elseif (!empty($pinnedItem['link_tautan']) && $pinnedItem['link_tautan'] !== '#'): ?>
                                <a href="<?php echo htmlspecialchars(ltrim($pinnedItem['link_tautan'], '/')); ?>" class="pengumuman-btn pengumuman-btn-secondary">
                                    <?php echo htmlspecialchars($pinnedItem['teks_tautan'] ?: 'Buka Tautan'); ?> <i class="ph-bold ph-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($regularItems)): ?>
                <div class="pengumuman-grid">
                    <?php foreach ($regularItems as $pItem): 
                        $catSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $pItem['kategori']));
                    ?>
                        <article class="pengumuman-card reveal">
                            <div class="pengumuman-card-top">
                                <div class="pengumuman-card-meta">
                                    <span class="cat-pill cat-pill-<?php echo $catSlug; ?>">
                                        <?php echo htmlspecialchars($pItem['kategori']); ?>
                                    </span>
                                    <span class="meta-date">
                                        <i class="ph-bold ph-calendar-blank"></i> <?php echo format_tanggal_indonesia($pItem['tanggal']); ?>
                                    </span>
                                </div>
                                <?php if ($pItem['is_pinned'] == 1): ?>
                                    <span class="pinned-indicator-pill"><i class="ph-bold ph-push-pin"></i> Pinned</span>
                                <?php endif; ?>
                            </div>

                            <h3 class="pengumuman-card-title"><?php echo htmlspecialchars($pItem['judul']); ?></h3>

                            <div class="pengumuman-card-excerpt">
                                <?php echo htmlspecialchars(mb_substr(strip_tags($pItem['isi']), 0, 140)); ?>...
                            </div>

                            <div class="pengumuman-card-footer">
                                <button type="button" class="pengumuman-link-btn" onclick='openDetailPengumuman(<?php echo json_encode($pItem, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                    <span>Baca Detail</span>
                                    <i class="ph-bold ph-arrow-right"></i>
                                </button>
                                <?php if (!empty($pItem['file_lampiran'])): ?>
                                    <a href="<?php echo htmlspecialchars(ltrim($pItem['file_lampiran'], '/')); ?>" target="_blank" class="pengumuman-file-tag" title="Unduh Berkas Lampiran">
                                        <i class="ph-bold ph-file-pdf"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section class="prestasi-section">
        <div class="section-header center reveal">
            <span class="section-badge badge-yellow">PRESTASI & SANG JUARA</span>
            <h2 class="section-title">Prestasi SMK Sukapura</h2>
            <p class="section-subtitle">Ukiran Kebanggaan dan Raihan Juara Siswa-Siswi SMK Sukapura</p>
        </div>

        <?php
        $prestasiList = [
            [
                'img'      => 'assets/prestasi/kiki.jpg',
                'category' => 'MOJANG JAJAKA',
                'rank'     => 'Wakil Gandes',
                'icon'     => 'ph-trophy',
                'title'    => 'Mojang Jajaka Sukapura',
                'caption'  => 'Terpilih sebagai Pemenang Nominasi Wakil Gandes dalam ajang Pemilihan Duta Pariwisata, Budaya, dan Ekonomi Kreatif.'
            ],
            [
                'img'      => 'assets/prestasi/shota.jpg',
                'category' => 'MOJANG JAJAKA',
                'rank'     => 'Pemenang Wakil 2',
                'icon'     => 'ph-trophy',
                'title'    => 'Mojang Jajaka Sukapura',
                'caption'  => 'Terpilih sebagai Pemenang Nominasi Wakil 2 dalam ajang Pemilihan Duta Pariwisata, Budaya, dan Ekonomi Kreatif.'
            ],
            [
                'img'      => 'assets/prestasi/osis.jpg',
                'category' => 'ORGANISASI SISWA',
                'rank'     => 'Apresiasi',
                'icon'     => 'ph-medal',
                'title'    => 'Penghargaan Kepemimpinan OSIS',
                'caption'  => 'Penghargaan untuk pengurus OSIS SMK Sukapura atas dedikasi dan suksesnya berbagai program kerja kreatif.'
            ],
            [
                'img'      => 'assets/prestasi/paskibra.jpg',
                'category' => 'PASUJA PASKIBRA',
                'rank'     => 'Juara Utama',
                'icon'     => 'ph-trophy',
                'title'    => 'Juara Utama Paskibra Sukapura',
                'caption'  => 'Tim Paskibra SMK Sukapura (Pasuja) berhasil mendulang prestasi gemilang dalam kejuaraan baris-berbaris.'
            ],
            [
                'img'      => 'assets/prestasi/paskibra2.jpg',
                'category' => 'PASUJA PASKIBRA',
                'rank'     => 'Trofi Kemenangan',
                'icon'     => 'ph-trophy',
                'title'    => 'Trofi Kebanggaan Paskibra',
                'caption'  => 'Penyerahan piala penghargaan tingkat kabupaten dan provinsi atas ketangkasan formasi tim Pasuja.'
            ],
            [
                'img'      => 'assets/prestasi/vollypi.jpg',
                'category' => 'OLAHRAGA VOLI',
                'rank'     => 'Juara Voli PI',
                'icon'     => 'ph-trophy',
                'title'    => 'Juara Tim Bola Voli Putri',
                'caption'  => 'Prestasi luar biasa tim bola voli putri SMK Sukapura merebut gelar juara dalam turnamen antar pelajar.'
            ],
            [
                'img'      => 'assets/prestasi/vollypa.jpg',
                'category' => 'OLAHRAGA VOLI',
                'rank'     => 'Juara Voli PA',
                'icon'     => 'ph-trophy',
                'title'    => 'Juara Tim Bola Voli Putra',
                'caption'  => 'Semangat juang dan kekompakan tim bola voli putra SMK Sukapura membuahkan trofi kejuaraan regional.'
            ],
            [
                'img'      => 'assets/prestasi/mgmp.jpg',
                'category' => 'LOMBA PAI',
                'rank'     => 'Juara MGMP PAI',
                'icon'     => 'ph-trophy',
                'title'    => 'Juara Ajang Pentas MGMP PAI',
                'caption'  => 'Prestasi membanggakan siswa-siswi SMK Sukapura dalam Pekan Keterampilan dan Seni Pendidikan Agama Islam (Pentas PAI) MGMP.'
            ],
            [
                'img'      => 'assets/prestasi/poster.jpg',
                'category' => 'DESAIN DIGITAL',
                'rank'     => 'Juara Poster Digital',
                'icon'     => 'ph-palette',
                'title'    => 'Juara Lomba Desain Poster Digital',
                'caption'  => 'Kreativitas dan keahlian siswa SMK Sukapura dalam merancang poster digital artistik yang edukatif dan inspiratif.'
            ]
        ];
        ?>
        <div class="prestasi-grid">
            <?php foreach ($prestasiList as $idx => $item): ?>
            <div class="prestasi-card reveal reveal-scale reveal-delay-<?php echo ($idx % 3) + 1; ?>">
                <div class="prestasi-img-wrapper" onclick="openPrestasiLightbox(<?php echo $idx; ?>)" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openPrestasiLightbox(<?php echo $idx; ?>);}" title="Klik untuk memperbesar foto: <?php echo htmlspecialchars($item['title']); ?>">
                    <img src="<?php echo htmlspecialchars($item['img']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="prestasi-img" loading="lazy">
                    <span class="prestasi-category"><?php echo htmlspecialchars($item['category']); ?></span>
                    <span class="prestasi-rank"><i class="ph-bold <?php echo htmlspecialchars($item['icon']); ?>"></i> <?php echo htmlspecialchars($item['rank']); ?></span>
                    <div class="prestasi-img-overlay">
                        <span class="prestasi-zoom-btn"><i class="ph-bold ph-arrows-out-simple"></i> Buka Foto</span>
                    </div>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="prestasi-caption"><?php echo htmlspecialchars($item['caption']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="keahlian-section" id="keahlian">
        <div class="section-header center reveal">
            <span class="section-badge badge-yellow"><i class="ph-bold ph-graduation-cap"></i> PROGRAM KEAHLIAN</span>
            <h2 class="section-title">Konsentrasi Keahlian Unggulan</h2>
            <p class="section-subtitle">10 Program Keahlian Siap Kerja Berbasis Industri &amp; Teknologi di SMKS Sukapura</p>
        </div>

        <div class="keahlian-grid">
            <div class="keahlian-card reveal reveal-scale reveal-delay-1">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-blue">TI</span>
                    <span class="keahlian-code">PPLG</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/pplg.png" alt="Pengembangan Perangkat Lunak & Gim (PPLG)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">PPLG</h3>
                    <p class="keahlian-desc">Pengembangan Perangkat Lunak &amp; Gim</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-2">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-purple">TI</span>
                    <span class="keahlian-code">TJKT</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/tjkt.png" alt="Teknik Jaringan Komputer & Telekomunikasi (TJKT)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">TJKT</h3>
                    <p class="keahlian-desc">Teknik Jaringan Komputer &amp; Telekomunikasi</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-3">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-pink">TI</span>
                    <span class="keahlian-code">DKV</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/dkv.png" alt="Desain Komunikasi Visual (DKV)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">DKV</h3>
                    <p class="keahlian-desc">Desain Komunikasi Visual</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-4">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-green">BISNIS MANAJEMEN</span>
                    <span class="keahlian-code">MPLB</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/mp.png" alt="Manajemen Perkantoran & Layanan Bisnis (MP)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">MPLB</h3>
                    <p class="keahlian-desc">Manajemen Perkantoran &amp; Layanan Bisnis</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-5">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-yellow">BISNIS MANAJEMEN</span>
                    <span class="keahlian-code">AKL</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/ak.png" alt="Akuntansi & Keuangan Lembaga (AK)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">AKL</h3>
                    <p class="keahlian-desc">Akuntansi &amp; Keuangan Lembaga</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-1">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-orange">BISNIS MANAJEMEN</span>
                    <span class="keahlian-code">BD</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/bd.png" alt="Bisnis Digital (BD)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">BD</h3>
                    <p class="keahlian-desc">Pemasaran &amp; E-Commerce</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-2">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-dark">BISNIS MANAJEMEN</span>
                    <span class="keahlian-code">AB</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/ab.png" alt="Teknik Bodi Otomotif (AB)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">AB</h3>
                    <p class="keahlian-desc">Bisnis Pertanian</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-3">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-red">PARAWISATA</span>
                    <span class="keahlian-code">DPB</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/dpb.png" alt="Desain & Produksi Busana (DPB)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">DPB</h3>
                    <p class="keahlian-desc">Desain &amp; Produksi Busana</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-4">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-teal">PARIWISATA</span>
                    <span class="keahlian-code">KLN</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/kln.png" alt="Kuliner (KLN)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">Kuliner</h3>
                    <p class="keahlian-desc">Tata Boga &amp; Seni Kuliner</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-5">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-dark">OTOMOTIF</span>
                    <span class="keahlian-code">TSM</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/tsm.png" alt="Teknik Sepeda Motor (TSM)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">TSM</h3>
                    <p class="keahlian-desc">Teknik Sepeda Motor </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SEKSI DOKUMENTASI GALERI DI BERANDA -->
    <?php if (!empty($berandaPhotos)): ?>
    <section class="galeri-beranda-section" id="galeri">
        <div class="section-header-wrap reveal">
            <div class="section-header">
                <span class="section-badge badge-yellow"><i class="ph-bold ph-images"></i> DOKUMENTASI SEKOLAH</span>
                <h2 class="section-title">Galeri &amp; Kegiatan Siswa</h2>
                <p class="section-subtitle">Potret kebersamaan, kreativitas, dan momen inspiratif civitas akademika SMKS Sukapura</p>
            </div>
            <a href="galeri/index.php" class="btn-galeri-more">
                <span>Lihat Semua Galeri</span> <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

        <div class="galeri-beranda-grid">
            <?php 
            $categoryBadges = [
                'lomba'     => 'Kegiatan Lomba',
                'istigosah' => 'Doa & Istigosah',
                'porsekas'  => 'Porsekas Seni & Olahraga',
                'sertijab'  => 'Organisasi & OSIS',
                'tka'       => 'Akademik & TKA',
                'upacara'   => 'Upacara Bendera',
                'expo'      => 'Expo & Pameran Karya',
                'prestasi'  => 'Prestasi Siswa'
            ];
            foreach ($berandaPhotos as $idx => $bp): 
                $imgUrl = $bp['file_path'];
                $badgeLabel = $categoryBadges[$bp['kategori']] ?? ucfirst($bp['kategori']);
            ?>
                <div class="galeri-beranda-card reveal reveal-scale reveal-delay-<?php echo ($idx % 4) + 1; ?>" 
                     onclick="openBerandaLightbox('<?php echo htmlspecialchars($imgUrl); ?>', '<?php echo htmlspecialchars(addslashes($bp['judul'])); ?>', '<?php echo htmlspecialchars(addslashes($badgeLabel)); ?>')">
                    <div class="galeri-beranda-img-wrapper">
                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($bp['judul']); ?>" loading="lazy">
                        <div class="galeri-beranda-badge"><?php echo htmlspecialchars($badgeLabel); ?></div>
                        <div class="galeri-beranda-overlay">
                            <div class="galeri-beranda-overlay-inner">
                                <span class="galeri-beranda-zoom"><i class="ph-bold ph-arrows-out-simple"></i></span>
                                <h3 class="galeri-beranda-title"><?php echo htmlspecialchars($bp['judul']); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="galeri-beranda-footer-cta reveal">
            <a href="galeri/index.php" class="btn-galeri-explore">
                <i class="ph-bold ph-squares-four"></i> Jelajahi Seluruh Dokumentasi Galeri
            </a>
        </div>
    </section>

    <?php endif; ?>

    <section class="mitra-section">
        <div class="section-header center reveal">
            <h2 class="section-title">Mitra Kerja Sama</h2>
            <p class="section-subtitle">Dunia Usaha & Dunia Industri Terkemuka</p>
        </div>

        <div class="mitra-carousel-wrapper reveal">
            <button class="mitra-control prev" id="mitra-prev" aria-label="Mitra Sebelumnya">
                <i class="ph-bold ph-caret-left"></i>
            </button>
            <div class="mitra-carousel-track-container" id="mitra-track-container">
                <div class="mitra-carousel-track" id="mitra-track">
                    <div class="mitra-card"><img src="assets/mitra/abl.png" alt="ABL"></div>
                    <div class="mitra-card"><img src="assets/mitra/ahm.webp" alt="AHM"></div>
                    <div class="mitra-card"><img src="assets/mitra/bni.png" alt="BNI"></div>
                    <div class="mitra-card"><img src="assets/mitra/btn.webp" alt="BTN"></div>
                    <div class="mitra-card"><img src="assets/mitra/byu.webp" alt="by.U"></div>
                    <div class="mitra-card"><img src="assets/mitra/gamelab.png" alt="Gamelab"></div>
                    <div class="mitra-card"><img src="assets/mitra/joyday.webp" alt="Joyday"></div>
                    <div class="mitra-card"><img src="assets/mitra/pnm.webp" alt="PNM"></div>
                    <div class="mitra-card"><img src="assets/mitra/prilude.png" alt="Prilude"></div>
                    <div class="mitra-card"><img src="assets/mitra/rabbani.png" alt="Rabbani"></div>
                    <div class="mitra-card"><img src="assets/mitra/radar.png" alt="Radar"></div>
                    <div class="mitra-card"><img src="assets/mitra/suzuki.webp" alt="Suzuki"></div>
                    <div class="mitra-card"><img src="assets/mitra/telkom.webp" alt="Telkom Indonesia"></div>
                </div>
            </div>
            <button class="mitra-control next" id="mitra-next" aria-label="Mitra Selanjutnya">
                <i class="ph-bold ph-caret-right"></i>
            </button>
        </div>
    </section>

    <!-- Lightbox Modal untuk Beranda (Prestasi & Galeri) -->
    <div id="berandaLightboxModal" class="beranda-lightbox" onclick="closeBerandaLightbox()" role="dialog" aria-modal="true" aria-label="Pratinjau Foto">
        <div class="beranda-lightbox-content" onclick="event.stopPropagation()">
            <div class="beranda-lightbox-top">
                <span class="beranda-lightbox-tag" id="berandaLightboxTag" title="Kategori Prestasi">
                    <i class="ph-bold ph-trophy"></i> <span>Prestasi</span>
                </span>
                <div class="beranda-lightbox-top-right">
                    <span class="beranda-lightbox-counter" id="berandaLightboxCounter">1 / 9</span>
                    <button type="button" class="beranda-lightbox-close" onclick="closeBerandaLightbox()" aria-label="Tutup Pratinjau">&times;</button>
                </div>
            </div>
            <div class="beranda-lightbox-body">
                <button type="button" class="beranda-lightbox-nav prev" id="berandaLightboxPrev" onclick="prevPrestasiLightbox(event)" aria-label="Foto Sebelumnya">
                    <i class="ph-bold ph-caret-left"></i>
                </button>
                <img id="berandaLightboxImg" src="" alt="Full Preview Foto">
                <button type="button" class="beranda-lightbox-nav next" id="berandaLightboxNext" onclick="nextPrestasiLightbox(event)" aria-label="Foto Selanjutnya">
                    <i class="ph-bold ph-caret-right"></i>
                </button>
                <div class="beranda-lightbox-swipe-badge" id="berandaLightboxSwipeBadge">
                    <i class="ph-bold ph-arrows-left-right"></i>
                    <span>Geser untuk lihat foto lain</span>
                </div>
            </div>
            <div class="beranda-lightbox-footer">
                <h3 class="beranda-lightbox-caption" id="berandaLightboxCaption"></h3>
                <p class="beranda-lightbox-desc" id="berandaLightboxDesc"></p>
            </div>
        </div>
    </div>
    <script>
    const prestasiData = <?php echo json_encode($prestasiList ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    let currentPrestasiIndex = 0;
    let isPrestasiMode = false;

    function syncModalWidth(imgEl) {
        if (!imgEl) return;
        const contentEl = document.querySelector('#berandaLightboxModal .beranda-lightbox-content');
        if (!contentEl) return;

        const applyWidth = () => {
            if (window.innerWidth <= 768) {
                contentEl.style.width = '';
                return;
            }
            if (imgEl.naturalWidth && imgEl.naturalHeight) {
                const maxH = Math.min(window.innerHeight * 0.58, 520);
                const ratio = imgEl.naturalWidth / imgEl.naturalHeight;
                const targetW = Math.round(maxH * ratio);
                const finalW = Math.max(420, Math.min(targetW, 840, window.innerWidth * 0.85));
                contentEl.style.width = finalW + 'px';
            }
        };

        if (imgEl.complete && imgEl.naturalWidth) {
            applyWidth();
        } else {
            imgEl.onload = applyWidth;
        }
    }

    function openPrestasiLightbox(index) {
        if (!prestasiData || index < 0 || index >= prestasiData.length) return;
        currentPrestasiIndex = index;
        isPrestasiMode = true;
        updatePrestasiModal();
        const modal = document.getElementById('berandaLightboxModal');
        if (modal) modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function updatePrestasiModal() {
        const item = prestasiData[currentPrestasiIndex];
        if (!item) return;

        const imgEl = document.getElementById('berandaLightboxImg');
        if (imgEl) {
            imgEl.src = item.img;
            imgEl.alt = item.title;
            syncModalWidth(imgEl);
        }

        const capEl = document.getElementById('berandaLightboxCaption');
        if (capEl) capEl.textContent = item.title;

        const descEl = document.getElementById('berandaLightboxDesc');
        if (descEl) {
            descEl.textContent = item.caption || '';
            descEl.style.display = item.caption ? 'block' : 'none';
        }

        const tagEl = document.getElementById('berandaLightboxTag');
        if (tagEl) {
            const iconClass = item.icon || 'ph-trophy';
            const fullTag = item.category + ' \u2022 ' + item.rank;
            tagEl.title = fullTag;
            tagEl.innerHTML = '<i class="ph-bold ' + iconClass + '"></i> <span>' + escapeLightboxHtml(fullTag) + '</span>';
        }

        const counterEl = document.getElementById('berandaLightboxCounter');
        if (counterEl) {
            counterEl.textContent = (currentPrestasiIndex + 1) + ' / ' + prestasiData.length;
            counterEl.style.display = 'inline-flex';
        }

        const prevBtn = document.getElementById('berandaLightboxPrev');
        const nextBtn = document.getElementById('berandaLightboxNext');
        if (prevBtn) prevBtn.style.display = 'flex';
        if (nextBtn) nextBtn.style.display = 'flex';

        const swipeBadge = document.getElementById('berandaLightboxSwipeBadge');
        if (swipeBadge) {
            swipeBadge.style.display = (prestasiData && prestasiData.length > 1) ? 'inline-flex' : 'none';
        }
    }

    function prevPrestasiLightbox(e) {
        if (e) e.stopPropagation();
        if (!isPrestasiMode || !prestasiData.length) return;
        currentPrestasiIndex = (currentPrestasiIndex - 1 + prestasiData.length) % prestasiData.length;
        updatePrestasiModal();
    }

    function nextPrestasiLightbox(e) {
        if (e) e.stopPropagation();
        if (!isPrestasiMode || !prestasiData.length) return;
        currentPrestasiIndex = (currentPrestasiIndex + 1) % prestasiData.length;
        updatePrestasiModal();
    }

    function openBerandaLightbox(src, title, tag) {
        isPrestasiMode = false;
        const imgEl = document.getElementById('berandaLightboxImg');
        if (imgEl) {
            imgEl.src = src;
            imgEl.alt = title;
            syncModalWidth(imgEl);
        }

        const capEl = document.getElementById('berandaLightboxCaption');
        if (capEl) capEl.textContent = title;

        const descEl = document.getElementById('berandaLightboxDesc');
        if (descEl) {
            descEl.textContent = '';
            descEl.style.display = 'none';
        }

        const tagEl = document.getElementById('berandaLightboxTag');
        if (tagEl) {
            tagEl.title = tag;
            tagEl.innerHTML = '<i class="ph-bold ph-image"></i> <span>' + escapeLightboxHtml(tag) + '</span>';
        }

        const counterEl = document.getElementById('berandaLightboxCounter');
        if (counterEl) counterEl.style.display = 'none';

        const prevBtn = document.getElementById('berandaLightboxPrev');
        const nextBtn = document.getElementById('berandaLightboxNext');
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';

        const swipeBadge = document.getElementById('berandaLightboxSwipeBadge');
        if (swipeBadge) swipeBadge.style.display = 'none';

        const modal = document.getElementById('berandaLightboxModal');
        if (modal) modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeBerandaLightbox() {
        const modal = document.getElementById('berandaLightboxModal');
        if (modal) modal.classList.remove('active');
        document.body.style.overflow = '';
        const contentEl = document.querySelector('#berandaLightboxModal .beranda-lightbox-content');
        if (contentEl) contentEl.style.width = '';
    }

    function escapeLightboxHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('berandaLightboxModal');
        if (!modal || !modal.classList.contains('active')) return;
        if (e.key === 'Escape') {
            closeBerandaLightbox();
        } else if (e.key === 'ArrowLeft') {
            if (isPrestasiMode) prevPrestasiLightbox();
        } else if (e.key === 'ArrowRight') {
            if (isPrestasiMode) nextPrestasiLightbox();
        }
    });

    window.addEventListener('resize', function() {
        const modal = document.getElementById('berandaLightboxModal');
        if (modal && modal.classList.contains('active')) {
            const imgEl = document.getElementById('berandaLightboxImg');
            syncModalWidth(imgEl);
        }
    });

    // Swipe & Drag Gesture Support for Lightbox (Mobile touch + Mouse drag)
    (function initLightboxSwipe() {
        const bodyEl = document.querySelector('#berandaLightboxModal .beranda-lightbox-body');
        const imgEl = document.getElementById('berandaLightboxImg');
        if (!bodyEl || !imgEl) return;

        let startX = 0;
        let startY = 0;
        let diffX = 0;
        let isSwiping = false;
        let isMouseDown = false;

        function handleStart(x, y) {
            if (!isPrestasiMode) return;
            startX = x;
            startY = y;
            diffX = 0;
            isSwiping = true;
        }

        function handleMove(x, y) {
            if (!isSwiping || !isPrestasiMode) return;
            diffX = x - startX;
            const diffY = y - startY;

            // Jika tarikan horizontal dominan
            if (Math.abs(diffX) > Math.abs(diffY)) {
                bodyEl.classList.add('is-dragging');
                const damped = Math.max(-90, Math.min(90, diffX * 0.45));
                imgEl.style.transform = 'translateX(' + damped + 'px)';
                imgEl.style.transition = 'none';
            }
        }

        function handleEnd() {
            if (!isSwiping) return;
            isSwiping = false;
            bodyEl.classList.remove('is-dragging');
            imgEl.style.transition = 'transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease';
            imgEl.style.transform = '';

            if (isPrestasiMode && Math.abs(diffX) > 42) {
                if (diffX > 0) {
                    prevPrestasiLightbox();
                } else {
                    nextPrestasiLightbox();
                }
            }
            diffX = 0;
        }

        // Touch events (Mobile)
        bodyEl.addEventListener('touchstart', function(e) {
            if (e.touches && e.touches.length === 1) {
                handleStart(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: true });

        bodyEl.addEventListener('touchmove', function(e) {
            if (e.touches && e.touches.length === 1) {
                handleMove(e.touches[0].clientX, e.touches[0].clientY);
            }
        }, { passive: true });

        bodyEl.addEventListener('touchend', function() {
            handleEnd();
        }, { passive: true });

        bodyEl.addEventListener('touchcancel', function() {
            handleEnd();
        }, { passive: true });

        // Mouse drag events (Desktop / Devtools)
        bodyEl.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            if (e.target.closest('.beranda-lightbox-nav') || e.target.closest('.beranda-lightbox-close')) return;
            isMouseDown = true;
            handleStart(e.clientX, e.clientY);
        });

        window.addEventListener('mousemove', function(e) {
            if (!isMouseDown) return;
            handleMove(e.clientX, e.clientY);
        });

        window.addEventListener('mouseup', function() {
            if (!isMouseDown) return;
            isMouseDown = false;
            handleEnd();
        });
    })();
    </script>
</main>

<!-- =========================================================================
     MODAL DETAIL PENGUMUMAN SEKOLAH (LIGHTBOX)
     ========================================================================= -->
<div id="modal-pengumuman-detail" class="detail-modal-overlay" onclick="if(event.target===this) closeDetailPengumuman();" style="display:none;" role="dialog" aria-modal="true">
    <div class="detail-modal-card">
        <div class="detail-modal-header">
            <div class="detail-modal-meta">
                <span id="detail-kategori" class="cat-pill cat-pill-umum">Umum</span>
                <span id="detail-tanggal" class="meta-date"></span>
            </div>
            <button type="button" class="detail-modal-close" onclick="closeDetailPengumuman()" aria-label="Tutup Pengumuman">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>

        <div class="detail-modal-body">
            <div id="detail-gambar-container" style="display:none; margin-bottom:16px;">
                <img id="detail-gambar" src="" alt="Banner Pengumuman" style="width:100%; max-height:260px; object-fit:cover; border-radius:10px; border:2.5px solid #000;">
            </div>
            <h3 id="detail-judul" class="detail-modal-title"></h3>
            <div id="detail-isi" class="detail-modal-text"></div>
        </div>

        <div class="detail-modal-footer">
            <div id="detail-actions" class="detail-modal-actions"></div>
            <button type="button" class="pengumuman-btn pengumuman-btn-secondary" onclick="closeDetailPengumuman()">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
// Fungsi Modal Detail Pengumuman
function openDetailPengumuman(item) {
    if (!item) return;
    const modal = document.getElementById('modal-pengumuman-detail');
    if (!modal) return;

    // Kategori
    const katEl = document.getElementById('detail-kategori');
    katEl.textContent = item.kategori || 'Umum';
    katEl.className = 'cat-pill cat-pill-' + (item.kategori ? item.kategori.toLowerCase().replace(/[^a-z0-9]/g, '') : 'umum');

    // Tanggal
    const tglEl = document.getElementById('detail-tanggal');
    if (item.tanggal) {
        tglEl.innerHTML = '<i class="ph-bold ph-calendar-blank"></i> ' + item.tanggal;
    } else {
        tglEl.innerHTML = '';
    }

    // Judul & Isi
    document.getElementById('detail-judul').textContent = item.judul || '';
    document.getElementById('detail-isi').textContent = item.isi || '';

    // Gambar banner
    const gContainer = document.getElementById('detail-gambar-container');
    const gImg = document.getElementById('detail-gambar');
    if (item.gambar) {
        gImg.src = item.gambar.replace(/^\/+/, '');
        gContainer.style.display = 'block';
    } else {
        gContainer.style.display = 'none';
        gImg.src = '';
    }

    // Tombol Tindakan
    const actContainer = document.getElementById('detail-actions');
    actContainer.innerHTML = '';

    if (item.file_lampiran) {
        const btnLampiran = document.createElement('a');
        btnLampiran.href = item.file_lampiran.replace(/^\/+/, '');
        btnLampiran.target = '_blank';
        btnLampiran.className = 'pengumuman-btn pengumuman-btn-primary';
        btnLampiran.innerHTML = '<i class="ph-bold ph-file-arrow-down"></i> Unduh Berkas Lampiran';
        actContainer.appendChild(btnLampiran);
    }

    if (item.link_tautan && item.link_tautan !== '#') {
        const btnLink = document.createElement('a');
        btnLink.href = item.link_tautan.replace(/^\/+/, '');
        btnLink.className = 'pengumuman-btn pengumuman-btn-primary';
        btnLink.innerHTML = (item.teks_tautan || 'Buka Tautan') + ' <i class="ph-bold ph-arrow-right"></i>';
        actContainer.appendChild(btnLink);
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDetailPengumuman() {
    const modal = document.getElementById('modal-pengumuman-detail');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailPengumuman();
    }
});
</script>

<?php if (!empty($popupConfig['aktif'])): ?>
<!-- =========================================================================
     POPUP PENGUMUMAN MASUK WEB (DISENTING OLEH ADMIN)
     ========================================================================= -->
<div id="popup-sekolah-modal" class="popup-sekolah-overlay" role="dialog" aria-modal="true" aria-labelledby="popup-sekolah-title" style="display:none;">
    <div class="popup-sekolah-backdrop" onclick="closeWebPopup()"></div>
    <div class="popup-sekolah-wrapper">
        <div class="popup-sekolah-card">
            <!-- Header Strip -->
            <div class="popup-sekolah-header">
                <div class="popup-sekolah-badge">
                    <i class="ph-bold ph-megaphone-simple"></i>
                    <span><?php echo htmlspecialchars($popupConfig['badge'] ?: 'PENGUMUMAN RESMI'); ?></span>
                </div>
                <button type="button" class="popup-sekolah-close" onclick="closeWebPopup()" aria-label="Tutup Pengumuman">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            <!-- Optional Banner Image -->
            <?php if (!empty($popupConfig['gambar'])): ?>
                <div class="popup-sekolah-media">
                    <img src="<?php echo htmlspecialchars(ltrim($popupConfig['gambar'], '/')); ?>" alt="<?php echo htmlspecialchars($popupConfig['judul']); ?>">
                </div>
            <?php endif; ?>

            <!-- Body Content -->
            <div class="popup-sekolah-body">
                <h3 class="popup-sekolah-title" id="popup-sekolah-title">
                    <?php echo htmlspecialchars($popupConfig['judul']); ?>
                </h3>
                <div class="popup-sekolah-text">
                    <?php echo nl2br(htmlspecialchars($popupConfig['isi'])); ?>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="popup-sekolah-footer">
                <label class="popup-sekolah-check">
                    <input type="checkbox" id="popup-dont-show-checkbox">
                    <span>Jangan tampilkan lagi hari ini</span>
                </label>
                <div class="popup-sekolah-actions">
                    <button type="button" class="popup-btn-dismiss" onclick="closeWebPopup()">
                        Tutup
                    </button>
                    <?php if (!empty($popupConfig['tombol_link'])): ?>
                        <a href="<?php echo htmlspecialchars(ltrim($popupConfig['tombol_link'], '/')); ?>" 
                           target="<?php echo htmlspecialchars($popupConfig['target'] ?: '_self'); ?>" 
                           class="popup-btn-action" 
                           onclick="recordPopupAction()">
                            <span><?php echo htmlspecialchars($popupConfig['tombol_teks'] ?: 'Lihat Detail'); ?></span>
                            <i class="ph-bold ph-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Logika Popup Pengumuman Masuk Web
(function() {
    const popupMode = <?php echo json_encode($popupConfig['mode'] ?? 'sekali_sehari'); ?>;
    const todayStr  = new Date().toISOString().slice(0, 10);
    const modalEl   = document.getElementById('popup-sekolah-modal');
    const checkEl   = document.getElementById('popup-dont-show-checkbox');

    if (!modalEl) return;

    let shouldShow = true;
    if (popupMode === 'sekali_sehari') {
        const dismissedDate = localStorage.getItem('popup_sekolah_dismissed_date');
        if (dismissedDate === todayStr) {
            shouldShow = false;
        }
    } else if (popupMode === 'sekali_sesi') {
        const seenInSession = sessionStorage.getItem('popup_sekolah_seen_session');
        if (seenInSession === '1') {
            shouldShow = false;
        }
    }

    if (shouldShow) {
        setTimeout(function() {
            modalEl.style.display = 'flex';
            sessionStorage.setItem('popup_sekolah_seen_session', '1');
        }, 400);
    }

    window.closeWebPopup = function() {
        if (checkEl && checkEl.checked) {
            localStorage.setItem('popup_sekolah_dismissed_date', todayStr);
        }
        modalEl.style.display = 'none';
    };

    window.recordPopupAction = function() {
        if (checkEl && checkEl.checked) {
            localStorage.setItem('popup_sekolah_dismissed_date', todayStr);
        }
        closeWebPopup();
    };

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalEl.style.display === 'flex') {
            closeWebPopup();
        }
    });
})();
</script>
<?php endif; ?>

<?php include 'components/footer.php'; ?>

<script src="script.js?v=<?php echo @filemtime(__DIR__ . '/script.js') ?: time(); ?>"></script>
</body>
</html>