<?php
// spmb/admin/hapus.php
require_once __DIR__ . '/../config.php';
check_admin_login();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $pdo = get_db_connection();
    
    // Ambil info foto untuk dihapus jika ada
    $stmt = $pdo->prepare("SELECT `foto` FROM `spmb_pendaftar` WHERE `id` = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row && !empty($row['foto'])) {
        $fotoFile = __DIR__ . '/../uploads/' . $row['foto'];
        if (file_exists($fotoFile)) {
            @unlink($fotoFile);
        }
    }

    $del = $pdo->prepare("DELETE FROM `spmb_pendaftar` WHERE `id` = ?");
    $del->execute([$id]);
}

header("Location: pendaftar.php?msg=deleted");
exit;
