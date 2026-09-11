<?php
// spmb/admin/hapus.php
require_once __DIR__ . '/../config.php';
check_admin_login();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $pdo = get_db_connection();
    
    // Ambil info foto dan seluruh berkas untuk dihapus dari server
    $stmt = $pdo->prepare("SELECT `foto`, `berkas_kk`, `berkas_akta`, `berkas_ijazah`, `berkas_ktp_ortu`, `berkas_kip`, `berkas_prestasi` FROM `spmb_pendaftar` WHERE `id` = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row) {
        $fileKeys = ['foto', 'berkas_kk', 'berkas_akta', 'berkas_ijazah', 'berkas_ktp_ortu', 'berkas_kip', 'berkas_prestasi'];
        $uploadDir = __DIR__ . '/../uploads/';
        foreach ($fileKeys as $k) {
            if (!empty($row[$k])) {
                $filePath = $uploadDir . $row[$k];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
    }

    $del = $pdo->prepare("DELETE FROM `spmb_pendaftar` WHERE `id` = ?");
    $del->execute([$id]);
}

header("Location: pendaftar.php?msg=deleted");
exit;
