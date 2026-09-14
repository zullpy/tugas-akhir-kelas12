<?php
// spmb/admin/login.php
// Login Admin Panitia SPMB SMKS SUKAPURA
require_once __DIR__ . '/../config.php';
$pdo = get_db_connection();

if (!empty($_SESSION['spmb_admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `spmb_admin` WHERE `username` = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['spmb_admin_id']   = $admin['id'];
            $_SESSION['spmb_admin_user'] = $admin['username'];
            $_SESSION['spmb_admin_nama'] = $admin['nama'];
            $_SESSION['spmb_admin_role'] = $admin['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password salah. Coba periksa kembali.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panitia SPMB | SMKS SUKAPURA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" href="../spmb.css?v=<?php echo @filemtime(__DIR__ . '/../spmb.css') ?: time(); ?>">
    <link rel="stylesheet" href="admin.css?v=<?php echo @filemtime(__DIR__ . '/admin.css') ?: time(); ?>">
    <link rel="shortcut icon" href="../../assets/favicon.ico" type="image/x-icon">
</head>
<body style="background: #F8FAFC; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">

    <div style="width: 100%; max-width: 440px; background: #fff; border: var(--border-thick); border-radius: 16px; box-shadow: var(--shadow-lg); padding: 36px 30px;">
        
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 60px; height: 60px; background: var(--adm-yellow-light); border: var(--border-md); border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;">
                <img src="../../assets/favicon.ico" alt="Logo" style="width: 36px; height: 36px; object-fit: contain;">
            </div>
            <div class="spmb-badge-pill" style="margin-bottom: 8px;">
                <i class="ph-bold ph-shield-check"></i> Portal Panitia
            </div>
            <h1 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800;">
                Login Admin SPMB
            </h1>
            <p style="font-size: 0.88rem; color: #64748B; margin-top: 4px;">
                SMKS Sukapura Kabupaten Tasikmalaya
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="spmb-alert spmb-alert-danger" style="padding: 12px 16px; font-size: 0.9rem; margin-bottom: 20px;">
                <i class="ph-bold ph-warning-circle" style="font-size: 1.4rem;"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="spmb-form-group">
                <label class="spmb-label" for="username">Username Admin</label>
                <div style="position: relative;">
                    <input type="text" name="username" id="username" class="spmb-input" placeholder="Masukkan username" required autofocus value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
            </div>

            <div class="spmb-form-group">
                <label class="spmb-label" for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" class="spmb-input" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="spmb-btn spmb-btn-primary" style="width: 100%; padding: 13px; font-size: 0.95rem; border: var(--border-md); border-radius: 10px; box-shadow: var(--shadow-sm);">
                <i class="ph-bold ph-sign-in"></i> Masuk ke Dashboard
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 16px; border-top: var(--border-thin);">
            <a href="../index.php" style="color: var(--nb-navy); font-weight: 700; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px;">
                <i class="ph-bold ph-arrow-left"></i> Kembali ke Portal Calon Siswa
            </a>
        </div>

    </div>

</body>
</html>
