<?php
// spmb/login.php
// Login Calon Siswa SPMB SMKS SUKAPURA
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'login';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

// Tentukan redirect target setelah login berhasil
$redirectTarget = sanitize_input($_GET['redirect'] ?? ($_POST['redirect'] ?? 'daftar.php'));
// Hindari open redirect ke luar domain
if (strpos($redirectTarget, '://') !== false || strpos($redirectTarget, '//') === 0) {
    $redirectTarget = 'daftar.php';
}

// Jika sudah login, langsung alihkan
if (!empty($_SESSION['spmb_user_id'])) {
    header("Location: " . $redirectTarget);
    exit;
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = "Pendaftaran akun berhasil! Silakan masuk dengan email dan password Anda.";
} elseif (isset($_GET['reset'])) {
    $success = "Password berhasil diperbarui! Silakan masuk dengan password baru Anda.";
} elseif (isset($_GET['logout'])) {
    $success = "Anda telah berhasil keluar dari akun SPMB.";
} elseif (isset($_GET['required'])) {
    $error = "Silakan masuk ke akun SPMB terlebih dahulu sebelum mengisi formulir pendaftaran.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Alamat email dan password wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format alamat email tidak valid.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `spmb_akun` WHERE `email` = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session login calon siswa
            $_SESSION['spmb_user_id']    = $user['id'];
            $_SESSION['spmb_user_nama']  = $user['nama_lengkap'];
            $_SESSION['spmb_user_email'] = $user['email'];
            $_SESSION['spmb_user_nohp']  = $user['no_hp'];

            header("Location: " . $redirectTarget);
            exit;
        } else {
            $error = "Email atau password salah. Silakan periksa kembali data login Anda.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Akun Calon Siswa | SPMB SMKS SUKAPURA</title>
    <meta name="description" content="Masuk ke portal pendaftaran siswa baru SMKS Sukapura untuk mengisi formulir dan memantau status SPMB.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="spmb.css?v=<?php echo @filemtime(__DIR__ . '/spmb.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
    <style>
        .auth-container {
            max-width: 480px;
            margin: 40px auto 80px;
            padding: 0 20px;
        }
        .auth-card {
            background: #fff;
            border: var(--border-thick);
            border-radius: 18px;
            box-shadow: var(--shadow-xl);
            padding: 36px 32px;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .auth-icon-badge {
            width: 64px;
            height: 64px;
            background: var(--nb-yellow);
            border: var(--border-md);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #000;
            margin-bottom: 16px;
        }
        .auth-title {
            font-family: var(--font-heading);
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--nb-navy);
            margin: 0 0 6px;
        }
        .auth-subtitle {
            font-size: 0.92rem;
            color: #64748B;
            margin: 0;
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #64748B;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            padding: 4px;
        }
        .password-toggle-btn:hover {
            color: #000;
        }
        .auth-link {
            color: #0284C7;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .auth-link:hover {
            color: #0369A1;
            text-decoration: underline;
        }
    </style>
</head>
<body class="spmb-body">
    <?php include __DIR__ . '/header_minimal.php'; ?>

    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon-badge">
                    <i class="ph-bold ph-user-circle"></i>
                </div>
                <h1 class="auth-title">Masuk Akun</h1>
                <p class="auth-subtitle">Silakan masuk untuk melanjutkan pendaftaran siswa baru</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="spmb-alert spmb-alert-danger" style="margin-bottom: 22px;">
                    <i class="ph-bold ph-warning-circle" style="font-size: 1.4rem; flex-shrink: 0;"></i>
                    <div style="font-size: 0.9rem; line-height: 1.4;"><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="spmb-alert spmb-alert-success" style="margin-bottom: 22px; background: #D1FAE5; border-color: #059669; color: #065F46;">
                    <i class="ph-bold ph-check-circle" style="font-size: 1.4rem; flex-shrink: 0;"></i>
                    <div style="font-size: 0.9rem; line-height: 1.4;"><?php echo $success; ?></div>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" autocomplete="on">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectTarget); ?>">

                <div class="spmb-form-group">
                    <label class="spmb-label" for="email">
                        Alamat Email <span class="spmb-req">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="email" name="email" id="email" class="spmb-input" 
                               placeholder="nama@email.com" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               required autofocus autocomplete="email">
                    </div>
                </div>

                <div class="spmb-form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="spmb-label" for="password" style="margin-bottom: 0;">
                            Password <span class="spmb-req">*</span>
                        </label>
                        <a href="lupa-password.php" class="auth-link" style="font-size: 0.85rem;">
                            Lupa Password?
                        </a>
                    </div>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" class="spmb-input" 
                               placeholder="Masukkan password Anda" 
                               required autocomplete="current-password"
                               style="padding-right: 42px;">
                        <button type="button" class="password-toggle-btn" id="togglePasswordBtn" title="Tampilkan/Sembunyikan Password">
                            <i class="ph-bold ph-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <div style="margin-top: 8px; font-size: 0.86rem; color: #475569;">
                        Belum memiliki akun calon siswa? 
                        <a href="register.php<?php echo !empty($redirectTarget) && $redirectTarget !== 'daftar.php' ? '?redirect=' . urlencode($redirectTarget) : ''; ?>" 
                           class="auth-link">
                            Daftar di sini
                        </a>
                    </div>
                </div>

                <button type="submit" class="spmb-btn spmb-btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; margin-top: 12px;">
                    <i class="ph-bold ph-sign-in"></i> Masuk Sekarang
                </button>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/footer_minimal.php'; ?>

    <script>
    document.getElementById('togglePasswordBtn')?.addEventListener('click', function() {
        const passInput = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            icon.classList.remove('ph-eye');
            icon.classList.add('ph-eye-slash');
        } else {
            passInput.type = 'password';
            icon.classList.remove('ph-eye-slash');
            icon.classList.add('ph-eye');
        }
    });
    </script>
</body>
</html>
