<?php
// spmb/admin/logout.php
require_once __DIR__ . '/../config.php';

unset($_SESSION['spmb_admin_id']);
unset($_SESSION['spmb_admin_user']);
unset($_SESSION['spmb_admin_nama']);
unset($_SESSION['spmb_admin_role']);

header("Location: login.php");
exit;
