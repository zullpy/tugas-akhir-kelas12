<?php
// spmb/cek-status.php
// Cek Status Pendaftaran Calon Siswa Baru SPMB SMKS SUKAPURA
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'cek-status';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

// Calon siswa wajib login akun SPMB terlebih dahulu sebelum mengecek status formulir
// (Jika admin sedang login di tab yang sama, admin tetap diizinkan membuka/melihat data siswa via URL ?no= atau ?nisn=)
$currentUser = null;
$isAdmin = !empty($_SESSION['spmb_admin_id']);

if (!empty($_SESSION['spmb_user_id'])) {
    $currentUser = get_spmb_user();
} elseif ($isAdmin) {
    // Mode admin (tidak dipaksa redirect ke login siswa)
} else {
    // Calon siswa wajib login akun SPMB terlebih dahulu (persis seperti formulir daftar.php)
    check_spmb_login(true);
    $currentUser = get_spmb_user();
}

$pendaftarList = [];
$linkError = '';
$linkSuccess = '';
$searchedKeyword = sanitize_input($_GET['no'] ?? ($_GET['nisn'] ?? ($_POST['keyword'] ?? '')));

if ($currentUser) {
    // 1. Ambil semua data pendaftar milik akun yang sedang login (id_akun)
    $stmt = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `id_akun` = ? ORDER BY `id` ASC");
    $stmt->execute([$currentUser['id']]);
    $pendaftarList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Jika keyword pencarian dikirimkan via URL dan belum masuk dalam list
    if (!empty($searchedKeyword)) {
        $stmtKey = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `no_pendaftaran` = ? OR `nisn` = ? OR `nik` = ? LIMIT 1");
        $stmtKey->execute([$searchedKeyword, $searchedKeyword, $searchedKeyword]);
        $found = $stmtKey->fetch(PDO::FETCH_ASSOC);
        if (!$found) {
            $found = find_pendaftar_by_phone($pdo, $searchedKeyword);
        }

        if ($found) {
            if (empty($found['id_akun'])) {
                try {
                    $sync = $pdo->prepare("UPDATE `spmb_pendaftar` SET `id_akun` = ? WHERE `id` = ?");
                    $sync->execute([$currentUser['id'], $found['id']]);
                    $found['id_akun'] = $currentUser['id'];
                } catch (Exception $e) {}
            }
            if ($found['id_akun'] == $currentUser['id']) {
                $alreadyInList = false;
                foreach ($pendaftarList as $pItem) {
                    if ($pItem['id'] == $found['id']) {
                        $alreadyInList = true;
                        break;
                    }
                }
                if (!$alreadyInList) {
                    $pendaftarList[] = $found;
                }
            }
        }
    }
} elseif ($isAdmin && !empty($searchedKeyword)) {
    // Mode Admin melihat data siswa spesifik
    $stmtAdmin = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `no_pendaftaran` = ? OR `nisn` = ? OR `nik` = ? LIMIT 1");
    $stmtAdmin->execute([$searchedKeyword, $searchedKeyword, $searchedKeyword]);
    $pAdmin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
    if (!$pAdmin) {
        $pAdmin = find_pendaftar_by_phone($pdo, $searchedKeyword);
    }
    if ($pAdmin) {
        $pendaftarList[] = $pAdmin;
    }
}

// Tangani aksi manual menautkan pendaftaran jika ada formulir yang belum terhubung
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'link_registration' && $currentUser) {
    $inputKeyword = sanitize_input($_POST['link_keyword'] ?? '');
    if (empty($inputKeyword)) {
        $linkError = "Masukkan Nomor Registrasi (contoh: REG-SKPR-XXXXX), NISN, atau No. WhatsApp Anda.";
    } else {
        $stmtL = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `no_pendaftaran` = ? OR `nisn` = ? OR `nik` = ? LIMIT 1");
        $stmtL->execute([$inputKeyword, $inputKeyword, $inputKeyword]);
        $found = $stmtL->fetch(PDO::FETCH_ASSOC);
        if (!$found) {
            $found = find_pendaftar_by_phone($pdo, $inputKeyword);
        }

        if (!$found) {
            $linkError = "Data pendaftaran tidak ditemukan. Pastikan data yang dimasukkan sudah sesuai.";
        } elseif (!empty($found['id_akun']) && $found['id_akun'] != $currentUser['id']) {
            $linkError = "Data pendaftaran tersebut sudah terhubung dengan akun lain. Silakan hubungi panitia jika Anda pemilik data ini.";
        } else {
            try {
                $sync = $pdo->prepare("UPDATE `spmb_pendaftar` SET `id_akun` = ? WHERE `id` = ?");
                $sync->execute([$currentUser['id'], $found['id']]);
                $found['id_akun'] = $currentUser['id'];

                $alreadyInList = false;
                foreach ($pendaftarList as $pItem) {
                    if ($pItem['id'] == $found['id']) {
                        $alreadyInList = true;
                        break;
                    }
                }
                if (!$alreadyInList) {
                    $pendaftarList[] = $found;
                }
                $linkSuccess = "Formulir pendaftaran berhasil dihubungkan ke akun Anda!";
            } catch (Exception $e) {
                $linkError = "Terjadi kesalahan saat menghubungkan data pendaftaran.";
            }
        }
    }
}

$totalPendaftar = count($pendaftarList);

$waPanitia = !empty($settings['fonnte_target_admin']) ? preg_replace('/[^0-9]/', '', $settings['fonnte_target_admin']) : (!empty($settings['hotline_wa']) ? preg_replace('/[^0-9]/', '', $settings['hotline_wa']) : '6281958251765');
if (substr($waPanitia, 0, 1) === '0') {
    $waPanitia = '62' . substr($waPanitia, 1);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran | SPMB SMKS SUKAPURA</title>
    <meta name="description" content="Lacak status verifikasi berkas dan hasil seleksi formulir pendaftaran SPMB SMKS Sukapura secara online.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="spmb.css?v=<?php echo @filemtime(__DIR__ . '/spmb.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    /* Styling Khusus Status Pendaftaran Neubrutalism */
    .status-account-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #FFFFFF;
        border: var(--border-md);
        border-radius: var(--radius-btn);
        padding: 14px 20px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .status-nav-chips {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        overflow-x: auto;
        padding-bottom: 6px;
        scrollbar-width: thin;
    }

    .status-nav-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #FFFFFF;
        border: 2px solid #000000;
        border-radius: 10px;
        padding: 8px 14px;
        text-decoration: none;
        color: #0F172A;
        font-weight: 700;
        font-size: 0.86rem;
        box-shadow: 2.5px 2.5px 0 #000000;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .status-nav-chip:hover {
        transform: translate(-1px, -1px);
        box-shadow: 3.5px 3.5px 0 #000000;
        background: #FFFDF0;
    }

    .status-result-card {
        background: var(--nb-white);
        border: var(--border-thick);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-xl);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .status-card-header {
        padding: 22px 28px;
        background: var(--nb-gray-bg);
        border-bottom: var(--border-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .status-card-body {
        padding: 30px;
    }

    .status-jadwal-box {
        background: var(--nb-yellow-light);
        border: var(--border-md);
        border-radius: var(--radius-btn);
        padding: 18px 20px;
        margin-bottom: 28px;
    }

    .status-jadwal-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .status-biodata-grid {
        display: grid;
        grid-template-columns: 1fr 140px;
        gap: 24px;
        align-items: start;
        margin-bottom: 30px;
    }

    .status-biodata-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }

    .status-biodata-table td {
        padding: 8px 0;
        vertical-align: top;
        line-height: 1.5;
    }

    .status-biodata-table td.status-label {
        font-weight: 700;
        width: 170px;
        color: #374151;
        padding-right: 10px;
    }

    .status-biodata-table td.status-val {
        color: var(--nb-black);
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .status-photo-wrap {
        text-align: center;
    }

    .status-photo-frame {
        width: 125px;
        height: 160px;
        border: var(--border-md);
        border-radius: 8px;
        overflow: hidden;
        background: #f0f0f0;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-sm);
    }

    .status-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .status-photo-badge {
        display: none;
    }

    .status-action-btns {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: center;
    }

    .status-empty-card {
        background: #FFFFFF;
        border: var(--border-thick);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-xl);
        padding: 45px 30px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .spmb-container.status-main {
            padding: 24px 12px 60px !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .status-account-banner {
            padding: 12px 14px !important;
            margin-bottom: 20px !important;
        }

        .status-result-card,
        .status-empty-card {
            box-shadow: var(--shadow-md) !important;
            border-radius: 12px !important;
        }

        .status-empty-card {
            padding: 30px 18px !important;
        }

        .status-card-header {
            padding: 16px !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 12px !important;
        }

        .status-card-header h2 {
            font-size: 1.25rem !important;
        }

        .status-card-body {
            padding: 18px 14px !important;
        }

        .status-jadwal-box {
            padding: 14px !important;
            margin-bottom: 20px !important;
        }

        .status-jadwal-grid {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }

        /* Foto ditaruh di atas tabel di mobile */
        .status-biodata-grid {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 18px !important;
            margin-bottom: 24px !important;
        }

        .status-photo-wrap {
            order: -1 !important;
            width: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            background: #F8FAFC !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 12px !important;
            padding: 16px 12px 14px !important;
        }

        .status-photo-frame {
            width: 110px !important;
            height: 142px !important;
            box-shadow: 2.5px 2.5px 0px var(--nb-black) !important;
        }

        .status-photo-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            margin-top: 10px !important;
            font-size: 0.76rem !important;
            font-weight: 800 !important;
            color: var(--nb-navy-dark) !important;
            background: var(--nb-yellow) !important;
            border: var(--border-thin) !important;
            padding: 3px 12px !important;
            border-radius: var(--radius-pill) !important;
            box-shadow: 2px 2px 0px var(--nb-black) !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }

        .status-biodata-table {
            font-size: 0.88rem !important;
        }

        .status-biodata-table td {
            padding: 7px 0 !important;
        }

        .status-biodata-table td.status-label {
            width: 120px !important;
            min-width: 110px !important;
            font-size: 0.84rem !important;
            padding-right: 6px !important;
        }

        .status-biodata-table td.status-val {
            font-size: 0.88rem !important;
        }

        .status-action-btns {
            flex-direction: column !important;
            width: 100% !important;
            gap: 10px !important;
        }

        .status-action-btns .spmb-btn {
            width: 100% !important;
            justify-content: center !important;
            text-align: center !important;
            padding: 12px 16px !important;
        }
    }
    </style>
</head>
<body>
    <?php include __DIR__ . '/header_minimal.php'; ?>

    <!-- SOFT MODERN SPMB HEADER -->
    <div style="background: linear-gradient(180deg, #FFFDF0 0%, #FFF9D2 100%); border-bottom: 2px solid #000; padding: 36px 0 32px; box-shadow: 0 3px 0px rgba(0,0,0,0.05);">
        <div class="spmb-container" style="text-align:center;">
            <div style="display:inline-flex; align-items:center; gap:8px; background:#FFE600; border:1.5px solid #000; box-shadow:2px 2px 0 #000; padding:4px 16px; border-radius:999px; font-weight:800; font-size:0.8rem; text-transform:uppercase; margin-bottom:12px; color:#0A4D68;">
                <i class="ph-bold ph-magnifying-glass"></i> Tracking SPMB Online
            </div>
            <h1 style="font-family:var(--font-heading); font-size:clamp(1.7rem, 3.2vw, 2.3rem); font-weight:800; color:#053B50; margin-bottom:6px;">
                Status Formulir Pendaftaran
            </h1>
            <?php if ($currentUser): ?>
                <p style="font-size:0.95rem; color:#475569; margin:0 auto; font-weight:600;">
                    Akun: <strong style="color:#0A4D68;"><?php echo htmlspecialchars($currentUser['nama_lengkap']); ?></strong> &bull; <?php echo htmlspecialchars($currentUser['email']); ?>
                </p>
            <?php elseif ($isAdmin): ?>
                <p style="font-size:0.95rem; color:#475569; margin:0 auto; font-weight:600;">
                    <span class="spmb-badge spmb-badge-warning" style="font-size:0.75rem;">Mode Preview Admin</span> Memeriksa data pendaftaran siswa
                </p>
            <?php endif; ?>
        </div>
    </div>

    <main class="spmb-container status-main" style="padding: 35px 15px 70px;">
        <div style="max-width: 820px; margin: 0 auto;">

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'edited'): ?>
                <div class="spmb-alert spmb-alert-success" style="padding:18px 24px; margin-bottom:25px; border:2.5px solid var(--nb-black); box-shadow:var(--shadow-md);">
                    <i class="ph-bold ph-check-circle" style="font-size:1.8rem; color:#15803D;"></i>
                    <div>
                        <strong style="font-size:1.08rem; color:#166534;">Perbaikan Berhasil Disimpan!</strong><br>
                        Data dan berkas pendaftaran Anda telah berhasil diperbarui dan diajukan ulang. Status saat ini kembali menjadi <strong>Menunggu Verifikasi</strong> untuk ditinjau oleh panitia.
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($linkSuccess)): ?>
                <div class="spmb-alert spmb-alert-success" style="padding:18px 24px; margin-bottom:25px; border:2.5px solid var(--nb-black); box-shadow:var(--shadow-md);">
                    <i class="ph-bold ph-check-circle" style="font-size:1.8rem; color:#15803D;"></i>
                    <div>
                        <strong style="font-size:1.08rem; color:#166534;">Berhasil Terhubung!</strong><br>
                        <?php echo htmlspecialchars($linkSuccess); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($linkError)): ?>
                <div class="spmb-alert spmb-alert-danger" style="padding:16px 20px; margin-bottom:25px; border:2.5px solid var(--nb-black); box-shadow:var(--shadow-md);">
                    <i class="ph-bold ph-warning-circle" style="font-size:1.6rem;"></i>
                    <div>
                        <strong>Gagal Menghubungkan Formulir</strong><br>
                        <?php echo htmlspecialchars($linkError); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($totalPendaftar > 0): ?>
                <!-- INFO BANNER: JUMLAH FORMULIR TERHUBUNG KE AKUN -->
                <?php if ($currentUser): ?>
                    <div class="status-account-banner">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:38px; height:38px; border-radius:50%; background:var(--nb-yellow); border:1.5px solid #000; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; color:#000; box-shadow:2px 2px 0 #000; flex-shrink:0;">
                                <?php echo strtoupper(substr($currentUser['nama_lengkap'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-size:0.92rem; font-weight:800; color:#0F172A; line-height:1.2;">
                                    <?php echo htmlspecialchars($currentUser['nama_lengkap']); ?>
                                </div>
                                <div style="font-size:0.78rem; color:#64748B; margin-top:2px;">
                                    <?php if ($totalPendaftar > 1): ?>
                                        Terdapat <strong><?php echo $totalPendaftar; ?> formulir pendaftaran</strong> terhubung di akun ini
                                    <?php else: ?>
                                        Formulir pendaftaran terhubung otomatis dengan akun ini
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="display:inline-flex; align-items:center; gap:5px; background:#DCFCE7; color:#15803D; border:1px solid #16A34A; padding:4px 10px; border-radius:999px; font-size:0.75rem; font-weight:800;">
                                <i class="ph-bold <?php echo $totalPendaftar > 1 ? 'ph-users' : 'ph-check-circle'; ?>"></i>
                                <?php echo $totalPendaftar > 1 ? $totalPendaftar . ' Siswa Terdaftar' : 'Sinkronisasi Akun Aktif'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- QUICK NAV TABS JIKA LEBIH DARI 1 PENDAFTAR -->
                    <?php if ($totalPendaftar > 1): ?>
                        <div class="status-nav-chips">
                            <?php foreach ($pendaftarList as $idx => $pNav): ?>
                                <a href="#pendaftar-<?php echo $pNav['id']; ?>" class="status-nav-chip">
                                    <span style="background:var(--nb-yellow); border:1px solid #000; border-radius:5px; padding:1px 6px; font-size:0.72rem; font-weight:800; color:#000;">
                                        #<?php echo $idx + 1; ?>
                                    </span>
                                    <span><?php echo htmlspecialchars($pNav['nama_lengkap']); ?></span>
                                    <span style="font-size:0.75rem; color:#64748B;">(<?php echo htmlspecialchars($pNav['no_pendaftaran']); ?>)</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- LOOP TAMPILKAN SEMUA PENDAFTAR (1 AKUN BISA 2 PENDAFTAR ATAU LEBIH) -->
                <?php foreach ($pendaftarList as $index => $pendaftar): ?>
                    
                    <div id="pendaftar-<?php echo $pendaftar['id']; ?>">
                        
                        <?php if ($totalPendaftar > 1): ?>
                            <!-- PEMISAH & JUDUL SISWA JIKA LEBIH DARI 1 PENDAFTAR -->
                            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin: <?php echo $index > 0 ? '36px' : '0'; ?> 0 12px; padding-bottom:8px; border-bottom:2px dashed #CBD5E1;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="background:var(--nb-yellow); color:#000; font-family:var(--font-heading); font-size:0.85rem; font-weight:800; padding:3px 10px; border-radius:8px; border:1.5px solid #000; box-shadow:2px 2px 0 #000;">
                                        Siswa #<?php echo $index + 1; ?>
                                    </span>
                                    <h3 style="font-family:var(--font-heading); font-size:1.2rem; font-weight:800; color:var(--nb-navy-dark); margin:0;">
                                        <?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?>
                                    </h3>
                                </div>
                                <span style="font-size:0.8rem; color:#64748B; font-weight:700;">
                                    No. Reg: <strong><?php echo htmlspecialchars($pendaftar['no_pendaftaran']); ?></strong>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- KARTU HASIL STATUS -->
                        <div class="status-result-card">
                            
                            <!-- Card Top Header with Status -->
                            <div class="status-card-header">
                                <div>
                                    <span style="font-size:0.82rem; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Nomor Registrasi</span>
                                    <h2 style="font-family:var(--font-heading); font-size:1.45rem; font-weight:800; color:var(--nb-navy-dark); margin-top:2px;">
                                        <?php echo htmlspecialchars($pendaftar['no_pendaftaran']); ?>
                                    </h2>
                                </div>
                                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                    <?php echo get_status_badge_html($pendaftar['status']); ?>
                                </div>
                            </div>

                            <div class="status-card-body">
                                
                                <!-- Catatan Status Alert -->
                                <div style="margin-bottom:28px;">
                                    <?php if ($pendaftar['status'] === 'Diterima'): 
                                        $jDiterima = !empty($pendaftar['jurusan_diterima']) ? $pendaftar['jurusan_diterima'] : $pendaftar['jurusan_1'];
                                        $isDialihkan = (!empty($pendaftar['jurusan_2']) && $jDiterima === $pendaftar['jurusan_2'] && $pendaftar['jurusan_1'] !== $pendaftar['jurusan_2']);
                                    ?>
                                        <div class="spmb-alert spmb-alert-success" style="background:#DCFCE7; border:2px solid #15803D;">
                                            <i class="ph-bold ph-check-circle" style="font-size:2rem; color:#15803D; flex-shrink:0;"></i>
                                            <div>
                                                <strong style="font-size:1.15rem; color:#166534;">Selamat! Anda Diterima di SMKS Sukapura</strong>
                                                <div style="margin-top:6px; font-size:0.95rem; color:#14532D;">
                                                    Dinyatakan Lulus pada Kompetensi Keahlian: <strong><?php echo htmlspecialchars($jDiterima); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$jDiterima]['nama'] ?? ''); ?></strong>
                                                    <?php if ($isDialihkan): ?>
                                                        <div style="margin-top:4px; font-size:0.85rem; color:#B45309; background:#FEF3C7; padding:4px 8px; border-radius:6px; display:inline-block; font-weight:700;">
                                                            <i class="ph-bold ph-arrow-bend-right-down"></i> Dialihkan ke Pilihan 2 karena kuota Pilihan 1 telah terpenuhi.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="margin-top:8px; font-size:0.9rem; color:#333; border-top:1px dashed #86EFAC; padding-top:6px;">
                                                    <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'] ?? 'Silakan segera cetak kartu bukti pendaftaran dan ikuti petunjuk daftar ulang.')); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php elseif ($pendaftar['status'] === 'Perlu Perbaikan'): ?>
                                        <div class="spmb-alert" style="background:#FEF3C7; border:2px solid #F59E0B; color:#92400E; padding:20px; display:flex; gap:16px; align-items:flex-start;">
                                            <i class="ph-bold ph-warning" style="font-size:2.2rem; color:#D97706; flex-shrink:0;"></i>
                                            <div style="flex:1;">
                                                <strong style="font-size:1.15rem; color:#78350F; display:block; margin-bottom:4px;">
                                                    Pemberitahuan: Data / Berkas Pendaftaran Perlu Diperbaiki
                                                </strong>
                                                <div style="font-size:0.92rem; line-height:1.5; color:#92400E; margin-bottom:12px;">
                                                    Panitia telah memeriksa data Anda dan menemukan beberapa hal yang perlu direvisi:
                                                    <div style="background:#FFFFFF; border:1.5px solid #F59E0B; border-radius:8px; padding:10px 14px; margin-top:8px; font-weight:600; color:#B45309; white-space:pre-line;">
                                                        <?php echo htmlspecialchars($pendaftar['catatan_admin'] ?: 'Mohon periksa kembali kesesuaian data identitas atau kejelasan foto berkas yang diunggah.'); ?>
                                                    </div>
                                                    <?php if (!empty($pendaftar['tenggat_perbaikan'])): ?>
                                                        <div style="background:#FFF1F2; border:1.5px solid #E11D48; border-radius:8px; padding:10px 14px; margin-top:10px; font-weight:700; color:#9F1239; font-size:0.85rem; display:flex; align-items:center; gap:8px;">
                                                            <i class="ph-bold ph-alarm" style="font-size:1.4rem; color:#E11D48; flex-shrink:0;"></i>
                                                            <span>Batas Waktu Ganti Jurusan: <strong><?php echo date('d F Y', strtotime($pendaftar['tenggat_perbaikan'])) . ', pukul ' . date('H:i', strtotime($pendaftar['tenggat_perbaikan'])); ?> WIB</strong> (Maksimal 1 Minggu). Lewat dari batas waktu tanpa konfirmasi, sistem akan otomatis menetapkan status Ditolak.</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <a href="edit.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&nisn=<?php echo urlencode($pendaftar['nisn']); ?>" class="spmb-btn" style="background:#F59E0B; color:#0F172A; font-weight:800; border:2px solid #000; box-shadow:2px 2px 0 #000; padding:10px 20px; display:inline-flex; align-items:center; gap:8px;">
                                                    <i class="ph-bold ph-pencil-simple-line" style="font-size:1.2rem;"></i> Perbaiki Data / Ganti Jurusan Sekarang
                                                </a>
                                            </div>
                                        </div>
                                    <?php elseif ($pendaftar['status'] === 'Cadangan'): ?>
                                        <div class="spmb-alert spmb-alert-info" style="background-color:var(--nb-yellow-light);">
                                            <i class="ph-bold ph-clock" style="font-size:1.6rem;"></i>
                                            <div>
                                                <strong style="font-size:1.05rem;">Status: Daftar Tunggu (Cadangan)</strong><br>
                                                <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'] ?? 'Pilihan jurusan Anda saat ini penuh, berkas masuk dalam antrean cadangan tahap 2.')); ?>
                                            </div>
                                        </div>
                                    <?php elseif ($pendaftar['status'] === 'Ditolak'): ?>
                                        <div class="spmb-alert spmb-alert-danger">
                                            <i class="ph-bold ph-x-circle" style="font-size:1.6rem;"></i>
                                            <div>
                                                <strong style="font-size:1.05rem;">Mohon Maaf, Berkas Belum Memenuhi Syarat</strong><br>
                                                <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'] ?? 'Berkas pendaftaran tidak memenuhi kualifikasi seleksi atau kuota jurusan pilihan telah terpenuhi seluruhnya.')); ?>
                                                <div style="background:#FFFFFF; border:1.5px solid #FCA5A5; border-radius:8px; padding:12px 14px; margin-top:12px; color:#7F1D1D; font-size:0.88rem; line-height:1.5;">
                                                    <strong>✨ Pesan Semangat &amp; Motivasi:</strong><br>
                                                    <em>"Kegagalan hari ini bukanlah akhir dari segalanya, melainkan awal dari kesempatan baru yang menantimu di depan. Percayalah bahwa setiap orang memiliki jalan dan waktu terbaiknya masing-masing untuk sukses. Tetaplah bersemangat, teruslah belajar, dan kejarlah impianmu setinggi langit!"</em>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="spmb-alert spmb-alert-info" style="display:flex; flex-direction:column; gap:12px;">
                                            <div style="display:flex; gap:12px; align-items:flex-start;">
                                                <i class="ph-bold ph-hourglass-high" style="font-size:1.8rem; flex-shrink:0;"></i>
                                                <div>
                                                    <strong style="font-size:1.05rem;">Menunggu Verifikasi Berkas Administrasi</strong><br>
                                                    <?php echo nl2br(htmlspecialchars($pendaftar['catatan_admin'] ?? 'Data pendaftaran Anda telah tercatat dan sedang dalam antrean verifikasi tim panitia SPMB. Harap periksa kembali secara berkala.')); ?>
                                                </div>
                                            </div>
                                            <div style="border-top:1px dashed #93C5FD; padding-top:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                                <span style="font-size:0.82rem; color:#1E3A8A; font-weight:600;">
                                                    <i class="ph-bold ph-info"></i> Ingin mengubah data identitas atau pilihan jurusan?
                                                </span>
                                                <a href="edit.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&nisn=<?php echo urlencode($pendaftar['nisn']); ?>" class="spmb-btn spmb-btn-secondary" style="padding:6px 14px; font-size:0.82rem; border-width:1.5px;">
                                                    <i class="ph-bold ph-pencil-simple"></i> Edit Formulir Sekarang
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Jadwal Ujian / Tes jika ada -->
                                <?php if (!empty($pendaftar['jadwal_tes']) || !empty($pendaftar['ruang_tes'])): ?>
                                    <div class="status-jadwal-box">
                                        <h4 style="font-family:var(--font-heading); font-weight:800; font-size:1.1rem; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                                            <i class="ph-bold ph-calendar"></i> Informasi Tes Minat &amp; Bakat
                                        </h4>
                                        <div class="status-jadwal-grid">
                                            <div>
                                                <span style="font-size:0.85rem; font-weight:700; color:#555;">Jadwal / Waktu:</span><br>
                                                <strong><?php echo htmlspecialchars($pendaftar['jadwal_tes'] ?: 'Akan diumumkan'); ?></strong>
                                            </div>
                                            <div>
                                                <span style="font-size:0.85rem; font-weight:700; color:#555;">Lokasi / Ruang:</span><br>
                                                <strong><?php echo htmlspecialchars($pendaftar['ruang_tes'] ?: 'Akan diumumkan'); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Table Biodata Siswa -->
                                <h3 style="font-family:var(--font-heading); font-size:1.15rem; font-weight:800; margin-bottom:14px; padding-bottom:8px; border-bottom:var(--border-thin);">
                                    Rincian Biodata Calon Siswa
                                </h3>

                                <div class="status-biodata-grid">
                                    <div class="status-table-wrap">
                                        <table class="status-biodata-table">
                                            <tr>
                                                <td class="status-label">Nama Lengkap</td>
                                                <td class="status-val">: <strong><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td class="status-label">NISN</td>
                                                <td class="status-val">: <?php echo htmlspecialchars($pendaftar['nisn']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="status-label">NIK (No. KTP/KK)</td>
                                                <td class="status-val">: <?php echo htmlspecialchars($pendaftar['nik']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="status-label">Jenis Kelamin</td>
                                                <td class="status-val">: <?php echo $pendaftar['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="status-label">Tempat, Tgl Lahir</td>
                                                <td class="status-val">: <?php echo htmlspecialchars($pendaftar['tempat_lahir']) . ', ' . tgl_indo($pendaftar['tanggal_lahir']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="status-label">WhatsApp Siswa</td>
                                                <td class="status-val">: <?php echo htmlspecialchars($pendaftar['no_hp']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="status-label">Asal Sekolah</td>
                                                <td class="status-val">: <?php echo htmlspecialchars($pendaftar['asal_sekolah']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="status-label">Pilihan Jurusan 1</td>
                                                <td class="status-val">: <strong><?php echo htmlspecialchars($pendaftar['jurusan_1']); ?></strong> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$pendaftar['jurusan_1']]['nama'] ?? ''); ?></td>
                                            </tr>
                                            <?php if (!empty($pendaftar['jurusan_2'])): ?>
                                            <tr>
                                                <td class="status-label">Pilihan Jurusan 2</td>
                                                <td class="status-val">: <?php echo htmlspecialchars($pendaftar['jurusan_2']); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$pendaftar['jurusan_2']]['nama'] ?? ''); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ($pendaftar['status'] === 'Diterima'): 
                                                $jDiterimaRow = !empty($pendaftar['jurusan_diterima']) ? $pendaftar['jurusan_diterima'] : $pendaftar['jurusan_1'];
                                            ?>
                                            <tr style="background:#F0FDF4;">
                                                <td class="status-label" style="color:#15803D; font-weight:800;">Jurusan Diterima</td>
                                                <td class="status-val" style="color:#15803D;">
                                                    <div style="display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                                        <img src="../assets/jurusan/<?php echo strtolower($jDiterimaRow); ?>.png" alt="<?php echo $jDiterimaRow; ?>" style="width:22px; height:22px; object-fit:contain; flex-shrink:0;">
                                                        <strong style="font-size:1.02rem;">: <?php echo htmlspecialchars($jDiterimaRow); ?> - <?php echo htmlspecialchars($DAFTAR_JURUSAN[$jDiterimaRow]['nama'] ?? ''); ?></strong>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td class="status-label">Jalur Pendaftaran</td>
                                                <td class="status-val">: <?php echo get_jalur_badge_html($pendaftar['jalur']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="status-label">Tanggal Mendaftar</td>
                                                <td class="status-val">: <?php echo tgl_indo($pendaftar['tanggal_daftar']); ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="status-photo-wrap">
                                        <div class="status-photo-frame">
                                            <?php if (!empty($pendaftar['foto']) && file_exists(__DIR__ . '/uploads/' . $pendaftar['foto'])): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($pendaftar['foto']); ?>" alt="Foto Calon Siswa">
                                            <?php else: ?>
                                                <div style="color:#777; font-size:0.85rem; font-weight:700;">
                                                    <i class="ph-bold ph-user" style="font-size:2rem; display:block; margin-bottom:4px;"></i>
                                                    Pas Foto
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="status-photo-badge"><i class="ph-bold ph-user-circle"></i> Pas Foto Calon Siswa</span>
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="status-action-btns">
                                    <?php if ($pendaftar['status'] === 'Perlu Perbaikan'): ?>
                                        <a href="edit.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&nisn=<?php echo urlencode($pendaftar['nisn']); ?>" class="spmb-btn" style="background:#F59E0B; color:#0F172A; font-weight:800; border:2px solid #000; box-shadow:2px 2px 0 #000;">
                                            <i class="ph-bold ph-pencil-simple-line"></i> Perbaiki Formulir &amp; Berkas
                                        </a>
                                    <?php endif; ?>

                                    <a href="cetak.php?no=<?php echo urlencode($pendaftar['no_pendaftaran']); ?>&from=cek-status" class="spmb-btn spmb-btn-primary">
                                        <i class="ph-bold ph-printer"></i> Cetak Kartu Peserta
                                    </a>
                                    <a href="https://wa.me/<?php echo htmlspecialchars($waPanitia); ?>?text=Halo%20Panitia%20SPMB%20SMKS%20Sukapura%2C%20saya%20ingin%20bertanya%20mengenai%20status%20pendaftaran%20nomor%20<?php echo urlencode($pendaftar['no_pendaftaran']); ?>" target="_blank" class="spmb-btn spmb-btn-secondary">
                                        <i class="ph-bold ph-whatsapp-logo"></i> Bantuan Panitia
                                    </a>
                                </div>

                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>

                <!-- KOTAK TAUTKAN FORMULIR SISWA LAINNYA -->
                <?php if ($currentUser): ?>
                    <div style="border-top:2px dashed #CBD5E1; margin-top:35px; padding-top:24px; text-align:left;">
                        <details style="background:#F8FAFC; border:1.5px solid #CBD5E1; border-radius:12px; padding:14px 18px;">
                            <summary style="font-weight:700; color:#0A4D68; cursor:pointer; font-size:0.92rem; display:flex; align-items:center; gap:6px;">
                                <i class="ph-bold ph-link"></i> Memiliki formulir pendaftaran siswa lainnya? Hubungkan ke akun ini
                            </summary>
                            <div style="margin-top:14px;">
                                <p style="font-size:0.85rem; color:#64748B; margin-bottom:12px; line-height:1.5;">
                                    Jika Anda pernah mendaftar sebelum membuat akun ini atau mendaftarkan siswa lain, masukkan Nomor Registrasi (contoh: <code>REG-SKPR-XXXXX</code>), NISN, atau No. WhatsApp siswa:
                                </p>
                                <form action="cek-status.php" method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
                                    <input type="hidden" name="action" value="link_registration">
                                    <input type="text" name="link_keyword" class="spmb-input" placeholder="No. Registrasi / NISN / No. WhatsApp..." required style="flex:1; min-width:220px; font-size:0.92rem; padding:10px 14px;">
                                    <button type="submit" class="spmb-btn spmb-btn-primary" style="padding:10px 18px; font-size:0.92rem;">
                                        <i class="ph-bold ph-link-simple"></i> Hubungkan Siswa
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- HASIL STATUS: BELUM MENGISI FORMULIR PENDAFTARAN -->
                <div class="status-empty-card">
                    <div style="width:76px; height:76px; background:#FFF7B2; border:2.5px solid #000; border-radius:22px; box-shadow:4px 4px 0 #000; display:inline-flex; align-items:center; justify-content:center; font-size:38px; color:#053B50; margin-bottom:20px;">
                        <i class="ph-bold ph-file-text"></i>
                    </div>

                    <h2 style="font-family:var(--font-heading); font-size:1.6rem; font-weight:800; color:#053B50; margin-bottom:10px;">
                        Belum Ada Formulir Pendaftaran
                    </h2>

                    <p style="color:#475569; font-size:0.98rem; max-width:540px; margin:0 auto 26px; line-height:1.6;">
                        Halo <strong><?php echo htmlspecialchars($currentUser['nama_lengkap'] ?? 'Calon Siswa'); ?></strong>! Akun SPMB Anda sudah aktif, namun kami belum menemukan data formulir pendaftaran yang diajukan dengan akun ini. Silakan isi formulir pendaftaran untuk memulai proses seleksi.
                    </p>

                    <div style="display:flex; justify-content:center; gap:14px; flex-wrap:wrap; margin-bottom:32px;">
                        <a href="daftar.php" class="spmb-btn spmb-btn-primary" style="padding:14px 28px; font-size:1rem; font-weight:800;">
                            <i class="ph-bold ph-pencil-simple-line"></i> Isi Formulir Pendaftaran Sekarang
                        </a>
                        <a href="index.php" class="spmb-btn spmb-btn-secondary" style="padding:14px 22px; font-size:1rem;">
                            <i class="ph-bold ph-house"></i> Beranda SPMB
                        </a>
                    </div>

                    <!-- KOTAK TAUTKAN FORMULIR JIKA PERNAH DAFTAR SEBELUMNYA -->
                    <div style="border-top:2px dashed #CBD5E1; padding-top:24px; max-width:560px; margin:0 auto; text-align:left;">
                        <details style="background:#F8FAFC; border:1.5px solid #CBD5E1; border-radius:12px; padding:14px 18px;">
                            <summary style="font-weight:700; color:#0A4D68; cursor:pointer; font-size:0.92rem; display:flex; align-items:center; gap:6px;">
                                <i class="ph-bold ph-link"></i> Sudah pernah mendaftar sebelumnya? Hubungkan formulir Anda di sini
                            </summary>
                            <div style="margin-top:14px;">
                                <p style="font-size:0.85rem; color:#64748B; margin-bottom:12px; line-height:1.5;">
                                    Jika Anda pernah mendaftar sebelum membuat akun ini, masukkan Nomor Registrasi (contoh: <code>REG-SKPR-XXXXX</code>), NISN, atau No. WhatsApp yang Anda gunakan saat mendaftar:
                                </p>
                                <form action="cek-status.php" method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
                                    <input type="hidden" name="action" value="link_registration">
                                    <input type="text" name="link_keyword" class="spmb-input" placeholder="No. Registrasi / NISN / No. WhatsApp..." required style="flex:1; min-width:220px; font-size:0.92rem; padding:10px 14px;">
                                    <button type="submit" class="spmb-btn spmb-btn-primary" style="padding:10px 18px; font-size:0.92rem;">
                                        <i class="ph-bold ph-link-simple"></i> Hubungkan
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>

                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include __DIR__ . '/footer_minimal.php'; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'edited'): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Perbaikan Berhasil Disimpan!',
            text: 'Data dan berkas pendaftaran Anda telah berhasil diperbarui dan diajukan ulang ke panitia SPMB. Status pendaftaran saat ini kembali Menunggu Verifikasi.',
            confirmButtonColor: '#0A4D68',
            confirmButtonText: 'Baik, Mengerti'
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>
