<?php
// setting/keamanan.php
// Pengaturan Keamanan Akun Administrator & Status Sistem SMKS SUKAPURA

require_once __DIR__ . '/../spmb/config.php';
check_admin_login(true, '../spmb/admin/login.php');

$pdo      = get_db_connection();
$settings = get_all_settings();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'password') {
        $lama = $_POST['pass_lama'] ?? '';
        $baru = $_POST['pass_baru'] ?? '';
        $konf = $_POST['pass_konfirm'] ?? '';

        if (empty($lama) || empty($baru) || empty($konf)) {
            $error = "Semua kolom password wajib diisi.";
        } elseif ($baru !== $konf) {
            $error = "Konfirmasi password baru tidak cocok.";
        } elseif (strlen($baru) < 6) {
            $error = "Password baru minimal 6 karakter.";
        } else {
            $adminId = $_SESSION['spmb_admin_id'];
            $chk = $pdo->prepare("SELECT `password` FROM `spmb_admin` WHERE `id` = ?");
            $chk->execute([$adminId]);
            $hash = $chk->fetchColumn();

            if (password_verify($lama, $hash)) {
                $newHash = password_hash($baru, PASSWORD_BCRYPT);
                $updPass = $pdo->prepare("UPDATE `spmb_admin` SET `password` = ? WHERE `id` = ?");
                $updPass->execute([$newHash, $adminId]);
                $success = "Password akun admin berhasil diubah!";
            } else {
                $error = "Password lama yang Anda masukkan salah.";
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => !empty($success),
                    'message' => !empty($success) ? $success : $error
                ]);
                exit;
            }
        }
    }
}

$adminPageTitle   = 'Keamanan Akun Admin';
$adminPageHeading = 'Keamanan Akun &amp; Lingkungan Server';
require_once __DIR__ . '/../spmb/admin/header.php';
?>

<?php $activeTab = 'keamanan.php'; require_once __DIR__ . '/tabs.php'; ?>

<?php if (!empty($success)): ?>
    <div class="spmb-alert spmb-alert-success">
        <i class="ph-bold ph-check-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $success; ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="spmb-alert spmb-alert-danger">
        <i class="ph-bold ph-warning-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $error; ?></div>
    </div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1.2fr 1fr; gap:24px;">

    <!-- KOLOM KIRI: FORM GANTI PASSWORD -->
    <div>
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-lock-key" style="color:#9333EA;"></i> Ganti Password Administrator
                </h3>
            </div>
            <div class="adm-card-body">
                <p style="font-size:0.88rem; color:#64748B; margin-bottom:18px; line-height:1.6;">
                    Perbarui kata sandi akun panitia secara berkala untuk menjaga kerahasiaan data calon peserta didik baru.
                </p>

                <form id="form-password" action="keamanan.php" method="POST">
                    <input type="hidden" name="action" value="password">

                    <div class="spmb-form-group">
                        <label class="spmb-label">Username Saat Ini</label>
                        <input type="text" class="spmb-input" value="<?php echo htmlspecialchars($_SESSION['spmb_admin_user'] ?? 'admin'); ?>" disabled style="background:#F1F5F9; font-weight:700;">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Password Lama <span class="required">*</span></label>
                        <input type="password" name="pass_lama" class="spmb-input" placeholder="Masukkan password lama" required>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Password Baru <span class="required">*</span></label>
                        <input type="password" name="pass_baru" class="spmb-input" placeholder="Minimal 6 karakter" required minlength="6">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label">Konfirmasi Password Baru <span class="required">*</span></label>
                        <input type="password" name="pass_konfirm" class="spmb-input" placeholder="Ketik ulang password baru" required minlength="6">
                    </div>

                    <button type="submit" class="adm-btn adm-btn-secondary" style="width:100%; padding:12px; font-size:0.95rem;">
                        <i class="ph-bold ph-key"></i> Perbarui Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: STATUS DATABASE & SERVER -->
    <div>
        <div class="adm-card" style="margin-bottom:24px;">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-database" style="color:#0A4D68;"></i> Status Database MySQL
                </h3>
            </div>
            <div class="adm-card-body" style="font-size:0.88rem;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr style="border-bottom:1px dashed #E2E8F0;">
                        <td style="padding:8px 0; font-weight:700; color:#475569;">Host Database</td>
                        <td style="padding:8px 0; font-weight:700;">: <?php echo htmlspecialchars(DB_HOST); ?></td>
                    </tr>
                    <tr style="border-bottom:1px dashed #E2E8F0;">
                        <td style="padding:8px 0; font-weight:700; color:#475569;">Nama Database</td>
                        <td style="padding:8px 0;">: <code><?php echo htmlspecialchars(DB_NAME); ?></code></td>
                    </tr>
                    <tr style="border-bottom:1px dashed #E2E8F0;">
                        <td style="padding:8px 0; font-weight:700; color:#475569;">Driver &amp; Port</td>
                        <td style="padding:8px 0;">: PDO MySQL (Port <?php echo htmlspecialchars(DB_PORT); ?>)</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; font-weight:700; color:#475569;">Tabel Sistem Aktif</td>
                        <td style="padding:8px 0;">: <code>spmb_pendaftar</code>, <code>spmb_admin</code>, <code>spmb_pengaturan</code>, <code>galeri_foto</code></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-cpu" style="color:#16A34A;"></i> Lingkungan Server
                </h3>
            </div>
            <div class="adm-card-body" style="font-size:0.88rem;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr style="border-bottom:1px dashed #E2E8F0;">
                        <td style="padding:8px 0; font-weight:700; color:#475569;">Versi PHP</td>
                        <td style="padding:8px 0; font-weight:700;">: PHP <?php echo phpversion(); ?></td>
                    </tr>
                    <tr style="border-bottom:1px dashed #E2E8F0;">
                        <td style="padding:8px 0; font-weight:700; color:#475569;">Mode Sistem</td>
                        <td style="padding:8px 0;">: <span style="background:<?php echo (APP_ENV === 'production') ? '#DCFCE7' : '#FEF3C7'; ?>; padding:2px 8px; border-radius:6px; font-weight:800; font-size:0.75rem;"><?php echo strtoupper(APP_ENV); ?></span></td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; font-weight:700; color:#475569;">Web Server</td>
                        <td style="padding:8px 0;">: <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI Server'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
const formPassword = document.getElementById('form-password');
if (formPassword) {
    formPassword.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = formPassword.querySelector('button[type="submit"]');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner" style="animation:spin 1s linear infinite"></i> Memproses...'; }

        fetch('keamanan.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(formPassword)
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message });
                formPassword.reset();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
            }
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            formPassword.submit();
        });
    });
}
</script>

<?php require_once __DIR__ . '/../spmb/admin/footer.php'; ?>
