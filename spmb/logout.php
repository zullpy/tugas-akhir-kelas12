<?php
// spmb/logout.php
// Logout Calon Siswa SPMB SMKS SUKAPURA
require_once __DIR__ . '/config.php';

// Hapus hanya data session calon siswa (agar session admin jika ada tidak terpengaruh)
unset($_SESSION['spmb_user_id']);
unset($_SESSION['spmb_user_nama']);
unset($_SESSION['spmb_user_email']);
unset($_SESSION['spmb_user_nohp']);

// Redirect ke halaman login dengan notifikasi berhasil logout
header("Location: login.php?logout=1");
exit;
