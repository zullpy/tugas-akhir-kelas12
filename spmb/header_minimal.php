<?php
// spmb/header_minimal.php
// Header Minimalis Khusus Formulir & Akun SPMB SMKS SUKAPURA
// Desain Distraction-Free (Neubrutalism) dengan Menu Profil Akun & Logout

if (!isset($base_url)) {
    $base_url = '../';
}

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$spmbUser = function_exists('get_spmb_user') ? get_spmb_user() : null;

// Ambil ringkasan status pendaftaran calon siswa jika sudah login
$pendaftarInfo = null;
$pendaftarCount = 0;
if ($spmbUser && !empty($spmbUser['id'])) {
    $stmtC = $pdo->prepare("SELECT COUNT(*) FROM `spmb_pendaftar` WHERE `id_akun` = ?");
    $stmtC->execute([$spmbUser['id']]);
    $pendaftarCount = (int)$stmtC->fetchColumn();

    if ($pendaftarCount > 0) {
        $stmtP = $pdo->prepare("SELECT `id`, `no_pendaftaran`, `status`, `jurusan_1`, `tanggal_daftar` FROM `spmb_pendaftar` WHERE `id_akun` = ? ORDER BY `id` DESC LIMIT 1");
        $stmtP->execute([$spmbUser['id']]);
        $pendaftarInfo = $stmtP->fetch();
    }
}

$currentPageFile = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!-- SweetAlert2 Library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- STYLES KHUSUS MINIMAL HEADER SPMB -->
<style>
.spmb-min-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #FFFFFF;
    border-bottom: 2.5px solid #000000;
    box-shadow: 0 4px 0 rgba(0, 0, 0, 0.05);
}

.spmb-min-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
}

.spmb-min-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
}

.spmb-min-logo {
    width: 38px;
    height: 38px;
    object-fit: contain;
    border: 1.5px solid #000;
    border-radius: 10px;
    background: var(--nb-yellow, #FFE600);
    padding: 3px;
    box-shadow: 2px 2px 0 #000;
}

.spmb-min-titles {
    display: flex;
    flex-direction: column;
}

.spmb-min-school-name {
    font-family: var(--font-heading, 'Space Grotesk', sans-serif);
    font-weight: 800;
    font-size: 1.02rem;
    color: #053B50;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.spmb-min-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: var(--nb-yellow, #FFE600);
    border: 1px solid #000;
    border-radius: 4px;
    padding: 1px 6px;
    font-size: 0.7rem;
    font-weight: 800;
    color: #000;
    width: fit-content;
    margin-top: 2px;
}

.spmb-min-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
}

.spmb-min-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.86rem;
    font-weight: 700;
    color: #475569;
    text-decoration: none;
    padding: 7px 12px;
    border-radius: 8px;
    transition: all 0.15s ease;
}

.spmb-min-back-link:hover {
    color: #0284C7;
    background: #F1F5F9;
}

/* User Account Trigger Button */
.spmb-user-trigger {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    background: #FFFFFF;
    border: 2px solid #000000;
    border-radius: 30px;
    padding: 5px 12px 5px 6px;
    cursor: pointer;
    box-shadow: 2.5px 2.5px 0 #000000;
    font-family: inherit;
    transition: all 0.15s ease;
}

.spmb-user-trigger:hover,
.spmb-user-trigger[aria-expanded="true"] {
    transform: translate(-1px, -1px);
    box-shadow: 3.5px 3.5px 0 #000000;
    background: #FFFDF0;
}

.spmb-user-trigger:active {
    transform: translate(1px, 1px);
    box-shadow: 1px 1px 0 #000000;
}

.spmb-user-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--nb-yellow, #FFE600);
    border: 1.5px solid #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.85rem;
    color: #000;
}

.spmb-user-name {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0F172A;
    max-width: 140px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.spmb-user-caret {
    font-size: 0.85rem;
    color: #64748B;
    transition: transform 0.2s ease;
}

.spmb-user-trigger[aria-expanded="true"] .spmb-user-caret {
    transform: rotate(180deg);
}

/* Dropdown Menu */
.spmb-user-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 270px;
    background: #FFFFFF;
    border: 2.5px solid #000000;
    border-radius: 14px;
    box-shadow: 5px 5px 0 #000000;
    padding: 10px;
    display: none;
    z-index: 1050;
    animation: spmbMenuIn 0.15s ease-out;
}

.spmb-user-menu.show {
    display: block;
}

@keyframes spmbMenuIn {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.spmb-user-menu-header {
    padding: 8px 10px 12px;
    border-bottom: 1.5px solid #E2E8F0;
    margin-bottom: 6px;
}

.spmb-user-menu-fullname {
    font-weight: 800;
    font-size: 0.95rem;
    color: #0F172A;
    line-height: 1.25;
}

.spmb-user-menu-email {
    font-size: 0.78rem;
    color: #64748B;
    margin-top: 3px;
    word-break: break-all;
}

.spmb-user-menu-status {
    margin-top: 8px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 999px;
    border: 1px solid #000;
}

.spmb-status-terdaftar {
    background: #D1FAE5;
    color: #065F46;
}

.spmb-status-belum {
    background: #FEF3C7;
    color: #92400E;
}

.spmb-menu-item {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    padding: 9px 12px;
    font-size: 0.88rem;
    font-weight: 700;
    color: #1E293B;
    background: none;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    text-align: left;
    font-family: inherit;
    transition: all 0.12s ease;
}

.spmb-menu-item:hover {
    background: #F1F5F9;
    color: #0284C7;
}

.spmb-menu-item i {
    font-size: 1.15rem;
}

.spmb-menu-item.logout-item {
    color: #DC2626;
    border-top: 1px solid #F1F5F9;
    margin-top: 4px;
    padding-top: 10px;
}

.spmb-menu-item.logout-item:hover {
    background: #FEE2E2;
    color: #B91C1C;
}

/* MODAL PROFIL */
.spmb-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(3px);
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: spmbFadeIn 0.2s ease-out;
}

.spmb-modal-overlay.show {
    display: flex;
}

@keyframes spmbFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.spmb-modal-box {
    background: #FFFFFF;
    border: 3px solid #000000;
    border-radius: 18px;
    box-shadow: 7px 7px 0 #000000;
    width: 100%;
    max-width: 480px;
    padding: 24px;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
    animation: spmbPopIn 0.2s ease-out;
}

@keyframes spmbPopIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.spmb-modal-close {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1.5px solid #000;
    background: #F1F5F9;
    cursor: pointer;
    font-size: 1.2rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.12s ease;
}

.spmb-modal-close:hover {
    background: #EF4444;
    color: #FFF;
    border-color: #000;
}

.spmb-modal-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px dashed #E2E8F0;
}

.spmb-modal-avatar {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    background: var(--nb-yellow, #FFE600);
    border: 2px solid #000;
    box-shadow: 2px 2px 0 #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: #000;
    flex-shrink: 0;
}

.spmb-modal-title {
    font-family: var(--font-heading, 'Space Grotesk', sans-serif);
    font-size: 1.35rem;
    font-weight: 800;
    color: #0F172A;
    margin: 0;
    line-height: 1.2;
}

.spmb-modal-subtitle {
    font-size: 0.82rem;
    color: #64748B;
    margin: 3px 0 0;
}

.spmb-profile-field {
    margin-bottom: 14px;
}

.spmb-profile-label {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748B;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.spmb-profile-value {
    background: #F8FAFC;
    border: 1.5px solid #000;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.92rem;
    font-weight: 700;
    color: #0F172A;
    word-break: break-all;
}

.spmb-modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 22px;
    padding-top: 16px;
    border-top: 1.5px solid #E2E8F0;
}

@media (max-width: 640px) {
    .spmb-min-container {
        padding: 8px 14px;
    }
    .spmb-min-school-name {
        font-size: 0.9rem;
    }
    .spmb-user-name {
        max-width: 90px;
        font-size: 0.82rem;
    }
    .hide-mobile {
        display: none !important;
    }
}
</style>

<header class="spmb-min-header">
    <div class="spmb-min-container">
        <!-- BRAND LOGO & TITLE -->
        <a href="index.php" class="spmb-min-brand" title="Ke Beranda SPMB">
            <img src="<?php echo $base_url; ?>assets/favicon.ico" alt="Logo SMKS Sukapura" class="spmb-min-logo">
            <div class="spmb-min-titles">
                <span class="spmb-min-school-name">SMKS SUKAPURA</span>
                <span class="spmb-min-badge">
                    <i class="ph-bold ph-graduation-cap"></i> SPMB Online
                </span>
            </div>
        </a>

        <!-- ACTIONS & USER MENU -->
        <div class="spmb-min-actions">
            <a href="<?php echo $base_url; ?>index.php" class="spmb-min-back-link" title="Kembali ke Website Sekolah Utama">
                <i class="ph-bold ph-arrow-square-out"></i>
                <span class="hide-mobile">Web Utama</span>
            </a>

            <?php if (!empty($spmbUser)): 
                $namaParts = explode(' ', trim($spmbUser['nama_lengkap']));
                $panggilan = $namaParts[0] ?? 'Siswa';
                $initial = strtoupper(substr($spmbUser['nama_lengkap'], 0, 1));
            ?>
                <!-- TRIGGER PROFIL USER -->
                <button type="button" class="spmb-user-trigger" id="spmbUserTrigger" aria-expanded="false" aria-haspopup="true">
                    <div class="spmb-user-avatar">
                        <?php echo htmlspecialchars($initial); ?>
                    </div>
                    <span class="spmb-user-name">
                        <?php echo htmlspecialchars($panggilan); ?>
                    </span>
                    <i class="ph-bold ph-caret-down spmb-user-caret"></i>
                </button>

                <!-- DROPDOWN MENU -->
                <div class="spmb-user-menu" id="spmbUserMenu" role="menu">
                    <div class="spmb-user-menu-header">
                        <div class="spmb-user-menu-fullname">
                            <?php echo htmlspecialchars($spmbUser['nama_lengkap']); ?>
                        </div>
                        <div class="spmb-user-menu-email">
                            <?php echo htmlspecialchars($spmbUser['email']); ?>
                        </div>
                        <?php if ($pendaftarCount > 1): ?>
                            <div class="spmb-user-menu-status spmb-status-terdaftar">
                                <i class="ph-bold ph-users"></i> <?php echo $pendaftarCount; ?> Siswa Terdaftar
                            </div>
                        <?php elseif ($pendaftarCount === 1): ?>
                            <div class="spmb-user-menu-status spmb-status-terdaftar">
                                <i class="ph-bold ph-check-circle"></i> Sudah Mendaftar
                            </div>
                        <?php else: ?>
                            <div class="spmb-user-menu-status spmb-status-belum">
                                <i class="ph-bold ph-pencil-simple"></i> Belum Mengisi Formulir
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="spmb-menu-item" id="btnLihatProfilTrigger" role="menuitem">
                        <i class="ph-bold ph-user-circle"></i>
                        <span>Lihat Profil Akun</span>
                    </button>

                    <?php if ($currentPageFile !== 'daftar.php'): ?>
                        <a href="daftar.php" class="spmb-menu-item" role="menuitem">
                            <i class="ph-bold ph-pencil-simple-line"></i>
                            <span><?php echo $pendaftarInfo ? 'Formulir SPMB' : 'Isi Formulir Baru'; ?></span>
                        </a>
                    <?php endif; ?>

                    <a href="cek-status.php" class="spmb-menu-item" role="menuitem">
                        <i class="ph-bold ph-magnifying-glass"></i>
                        <span>Cek Status</span>
                    </a>

                    <?php if ($pendaftarInfo && $pendaftarInfo['status'] !== 'Diterima' && $pendaftarInfo['status'] !== 'Ditolak'): ?>
                        <a href="edit.php" class="spmb-menu-item" role="menuitem">
                            <i class="ph-bold ph-pencil-simple"></i>
                            <span>Edit Formulir</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($currentPageFile !== 'index.php'): ?>
                        <a href="index.php" class="spmb-menu-item" role="menuitem">
                            <i class="ph-bold ph-house"></i>
                            <span>Kembali ke Beranda</span>
                        </a>
                    <?php endif; ?>

                    <a href="logout.php" class="spmb-menu-item logout-item" role="menuitem">
                        <i class="ph-bold ph-sign-out"></i>
                        <span>Keluar (Logout)</span>
                    </a>
                </div>

            <?php else: ?>
                <!-- JIKA BELUM LOGIN -->
                <?php if ($currentPageFile === 'login.php'): ?>
                    <a href="register.php" class="spmb-btn spmb-btn-primary" style="padding: 6px 14px; font-size: 0.85rem; border-radius: 8px;">
                        <i class="ph-bold ph-user-plus"></i> <span class="hide-mobile">Daftar Akun</span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="spmb-btn spmb-btn-primary" style="padding: 6px 14px; font-size: 0.85rem; border-radius: 8px;">
                        <i class="ph-bold ph-sign-in"></i> <span class="hide-mobile">Masuk Akun</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if (!empty($spmbUser)): 
    // Format tanggal lahir
    $tglLahirFmt = '-';
    if (!empty($spmbUser['tanggal_lahir'])) {
        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $ts = strtotime($spmbUser['tanggal_lahir']);
        if ($ts) {
            $tglLahirFmt = date('j', $ts) . ' ' . ($bulanIndo[(int)date('n', $ts)] ?? '') . ' ' . date('Y', $ts);
        }
    }
?>
<!-- MODAL LIHAT PROFIL AKUN CALON SISWA -->
<div class="spmb-modal-overlay" id="spmbProfileModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="spmb-modal-box">
        <button type="button" class="spmb-modal-close" id="btnCloseProfileModal" aria-label="Tutup Profil">
            &times;
        </button>

        <div class="spmb-modal-header">
            <div class="spmb-modal-avatar">
                <i class="ph-bold ph-user"></i>
            </div>
            <div>
                <h3 class="spmb-modal-title">Profil Calon Siswa</h3>
                <p class="spmb-modal-subtitle">Informasi Akun Pendaftaran SPMB</p>
            </div>
        </div>

        <div class="spmb-profile-field">
            <div class="spmb-profile-label">
                <i class="ph-bold ph-identification-card"></i> Nama Lengkap
            </div>
            <div class="spmb-profile-value">
                <?php echo htmlspecialchars($spmbUser['nama_lengkap']); ?>
            </div>
        </div>

        <div class="spmb-profile-field">
            <div class="spmb-profile-label">
                <i class="ph-bold ph-envelope-simple"></i> Alamat Email
            </div>
            <div class="spmb-profile-value">
                <?php echo htmlspecialchars($spmbUser['email']); ?>
            </div>
        </div>

        <div class="spmb-profile-field">
            <div class="spmb-profile-label">
                <i class="ph-bold ph-whatsapp-logo"></i> Nomor WhatsApp / HP
            </div>
            <div class="spmb-profile-value">
                <?php echo htmlspecialchars($spmbUser['no_hp']); ?>
            </div>
        </div>

        <div class="spmb-profile-field">
            <div class="spmb-profile-label">
                <i class="ph-bold ph-calendar"></i> Tanggal Lahir
            </div>
            <div class="spmb-profile-value">
                <?php echo htmlspecialchars($tglLahirFmt); ?>
            </div>
        </div>

        <div class="spmb-profile-field">
            <div class="spmb-profile-label">
                <i class="ph-bold ph-certificate"></i> Status Formulir Pendaftaran
            </div>
            <div class="spmb-profile-value" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px;">
                <?php if ($pendaftarInfo): ?>
                    <span>
                        <strong style="color:#059669;"><?php echo htmlspecialchars($pendaftarInfo['no_pendaftaran']); ?></strong>
                        <span style="font-size:0.8rem; color:#64748B;">(<?php echo htmlspecialchars($pendaftarInfo['status']); ?>)</span>
                    </span>
                    <div style="display:inline-flex; gap:8px; align-items:center;">
                        <a href="cek-status.php?no=<?php echo urlencode($pendaftarInfo['no_pendaftaran']); ?>" style="font-size:0.8rem; font-weight:800; color:#0284C7; text-decoration:underline; display:inline-flex; align-items:center; gap:3px;">
                            <i class="ph-bold ph-magnifying-glass"></i> Cek Status
                        </a>
                        <span style="color:#CBD5E1;">•</span>
                        <a href="cetak.php?no=<?php echo urlencode($pendaftarInfo['no_pendaftaran']); ?>" target="_blank" style="font-size:0.8rem; font-weight:800; color:#0284C7; text-decoration:underline; display:inline-flex; align-items:center; gap:3px;">
                            <i class="ph-bold ph-printer"></i> Lihat Bukti
                        </a>
                    </div>
                <?php else: ?>
                    <span style="color:#D97706;">
                        <i class="ph-bold ph-clock"></i> Belum mengisi formulir
                    </span>
                    <a href="daftar.php" style="font-size:0.8rem; font-weight:800; color:#0284C7; text-decoration:underline;">
                        Isi Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="spmb-modal-actions">
            <?php if ($pendaftarInfo): ?>
                <a href="cek-status.php?no=<?php echo urlencode($pendaftarInfo['no_pendaftaran']); ?>" class="spmb-btn" style="flex:1.2; padding:10px; background:#FFE600; border:2px solid #000; color:#000; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:6px; font-weight:800; font-size:0.85rem; box-shadow:2px 2px 0 #000;">
                    <i class="ph-bold ph-magnifying-glass"></i> Cek Status
                </a>
            <?php endif; ?>
            <button type="button" class="spmb-btn spmb-btn-secondary" id="btnDismissProfileModal" style="flex:1; padding:10px;">
                Tutup
            </button>
            <a href="logout.php" class="spmb-btn" style="flex:1; padding:10px; background:#FEE2E2; border:2px solid #000; color:#B91C1C; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:6px; font-weight:800; font-size:0.85rem;">
                <i class="ph-bold ph-sign-out"></i> Logout
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SCRIPT PENGENDALI DROPDOWN & MODAL PROFIL -->
<script>
(function() {
    const trigger = document.getElementById('spmbUserTrigger');
    const menu = document.getElementById('spmbUserMenu');
    const modal = document.getElementById('spmbProfileModal');
    const btnOpenModal = document.getElementById('btnLihatProfilTrigger');
    const btnCloseModal = document.getElementById('btnCloseProfileModal');
    const btnDismissModal = document.getElementById('btnDismissProfileModal');

    // Toggle Dropdown Menu
    if (trigger && menu) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = menu.classList.contains('show');
            menu.classList.toggle('show', !isOpen);
            trigger.setAttribute('aria-expanded', !isOpen);
        });

        // Tutup dropdown saat klik di luar
        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                menu.classList.remove('show');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Modal Profil Calon Siswa
    function openModal() {
        if (modal) {
            if (menu) menu.classList.remove('show');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal() {
        if (modal) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
    }

    if (btnOpenModal) btnOpenModal.addEventListener('click', openModal);
    if (btnCloseModal) btnCloseModal.addEventListener('click', closeModal);
    if (btnDismissModal) btnDismissModal.addEventListener('click', closeModal);

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
        });
    }

    // Expose secara global jika halaman lain butuh trigger
    window.openSpmbProfileModal = openModal;
    window.closeSpmbProfileModal = closeModal;
})();
</script>
