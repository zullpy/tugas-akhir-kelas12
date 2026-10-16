<?php
// spmb/data_sekolah_init.php
// Inisialisasi Tabel & CRUD Helper untuk Data Sekolah: Guru, Siswa, Kelas, Ekstrakurikuler

require_once __DIR__ . '/data_sekolah_seeds.php';

function init_data_sekolah_tables($pdo) {
    // 1. TABEL DATA GURU & STAFF
    $pdo->exec("CREATE TABLE IF NOT EXISTS `data_guru` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `no_urut` VARCHAR(10) NOT NULL,
        `nama` VARCHAR(191) NOT NULL,
        `peran` VARCHAR(100) NOT NULL DEFAULT 'Guru Pengajar',
        `mapel` TEXT NOT NULL,
        `kategori` VARCHAR(50) NOT NULL DEFAULT 'guru',
        `foto` VARCHAR(255) DEFAULT NULL,
        `is_aktif` TINYINT(1) NOT NULL DEFAULT 1,
        `urutan` INT NOT NULL DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_guru_kategori (`kategori`),
        INDEX idx_guru_aktif (`is_aktif`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. TABEL DATA SISWA
    $pdo->exec("CREATE TABLE IF NOT EXISTS `data_siswa` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `no_urut` VARCHAR(10) NOT NULL,
        `nis` VARCHAR(50) NOT NULL,
        `nisn` VARCHAR(50) NOT NULL,
        `nama` VARCHAR(191) NOT NULL,
        `jk` VARCHAR(10) NOT NULL DEFAULT 'L',
        `tingkat` VARCHAR(10) NOT NULL DEFAULT 'X',
        `jurusan` VARCHAR(50) NOT NULL,
        `kelas` VARCHAR(100) NOT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'Aktif',
        `foto` VARCHAR(255) DEFAULT NULL,
        `is_aktif` TINYINT(1) NOT NULL DEFAULT 1,
        `urutan` INT NOT NULL DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_siswa_tingkat (`tingkat`),
        INDEX idx_siswa_jurusan (`jurusan`),
        INDEX idx_siswa_kelas (`kelas`),
        INDEX idx_siswa_aktif (`is_aktif`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. TABEL DATA KELAS & ROMBEL
    $pdo->exec("CREATE TABLE IF NOT EXISTS `data_kelas` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `no_urut` VARCHAR(10) NOT NULL,
        `kelas` VARCHAR(100) NOT NULL,
        `tingkat` VARCHAR(10) NOT NULL DEFAULT 'X',
        `jurusan` VARCHAR(50) NOT NULL,
        `jurusan_full` VARCHAR(191) NOT NULL,
        `kode_walikelas` VARCHAR(50) DEFAULT '',
        `nama_walikelas` VARCHAR(191) NOT NULL,
        `mapel_walikelas` VARCHAR(191) DEFAULT '',
        `siswa_l` INT NOT NULL DEFAULT 0,
        `siswa_p` INT NOT NULL DEFAULT 0,
        `jumlah_siswa` INT NOT NULL DEFAULT 0,
        `ruang` VARCHAR(100) DEFAULT '',
        `is_aktif` TINYINT(1) NOT NULL DEFAULT 1,
        `urutan` INT NOT NULL DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_kelas_tingkat (`tingkat`),
        INDEX idx_kelas_jurusan (`jurusan`),
        INDEX idx_kelas_aktif (`is_aktif`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Auto-migration untuk data_kelas (siswa_l & siswa_p)
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `data_kelas` LIKE 'siswa_l'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `data_kelas` ADD COLUMN `siswa_l` INT NOT NULL DEFAULT 0 AFTER `mapel_walikelas`, ADD COLUMN `siswa_p` INT NOT NULL DEFAULT 0 AFTER `siswa_l`");
        }
    } catch (Exception $e) {}

    // 4. TABEL DATA EKSTRAKURIKULER
    $pdo->exec("CREATE TABLE IF NOT EXISTS `data_ekskul` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `kode_ekskul` VARCHAR(20) DEFAULT '',
        `nama` VARCHAR(191) NOT NULL,
        `kategori` VARCHAR(50) NOT NULL DEFAULT 'olahraga',
        `kategori_label` VARCHAR(100) NOT NULL DEFAULT 'Olahraga & Bela Diri',
        `cat_class` VARCHAR(50) DEFAULT 'cat-olahraga',
        `icon` VARCHAR(100) DEFAULT 'ph-soccer-ball',
        `icon_bg` VARCHAR(50) DEFAULT 'icon-olahraga',
        `pembina` VARCHAR(191) NOT NULL,
        `ketua` VARCHAR(191) DEFAULT '',
        `jadwal` VARCHAR(191) DEFAULT '',
        `lokasi` VARCHAR(191) DEFAULT '',
        `jumlah_anggota` INT NOT NULL DEFAULT 0,
        `prestasi` TEXT DEFAULT '',
        `deskripsi` TEXT DEFAULT '',
        `foto` VARCHAR(255) DEFAULT NULL,
        `is_aktif` TINYINT(1) NOT NULL DEFAULT 1,
        `urutan` INT NOT NULL DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_ekskul_kategori (`kategori`),
        INDEX idx_ekskul_aktif (`is_aktif`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // AUTO SEED: GURU
    $cntGuru = (int)$pdo->query("SELECT COUNT(*) FROM `data_guru`")->fetchColumn();
    if ($cntGuru === 0) {
        $defaultGuru = get_default_seed_guru();
        $ins = $pdo->prepare("INSERT INTO `data_guru` (`no_urut`, `nama`, `peran`, `mapel`, `kategori`, `is_aktif`, `urutan`) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $u = 1;
        foreach ($defaultGuru as $g) {
            $mapelJson = json_encode($g['mapel'], JSON_UNESCAPED_UNICODE);
            $ins->execute([
                $g['no'] ?? sprintf('%02d', $u),
                $g['nama'] ?? '',
                $g['peran'] ?? 'Guru Pengajar',
                $mapelJson,
                $g['kategori'] ?? 'guru',
                $u
            ]);
            $u++;
        }
    }

    // AUTO SEED: SISWA
    $cntSiswa = (int)$pdo->query("SELECT COUNT(*) FROM `data_siswa`")->fetchColumn();
    if ($cntSiswa === 0) {
        $defaultSiswa = get_default_seed_siswa();
        $ins = $pdo->prepare("INSERT INTO `data_siswa` (`no_urut`, `nis`, `nisn`, `nama`, `jk`, `tingkat`, `jurusan`, `kelas`, `status`, `is_aktif`, `urutan`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $u = 1;
        foreach ($defaultSiswa as $s) {
            $ins->execute([
                $s['no'] ?? sprintf('%02d', $u),
                $s['nis'] ?? '',
                $s['nisn'] ?? '',
                $s['nama'] ?? '',
                $s['jk'] ?? 'L',
                $s['tingkat'] ?? 'X',
                $s['jurusan'] ?? '',
                $s['kelas'] ?? '',
                $s['status'] ?? 'Aktif',
                $u
            ]);
            $u++;
        }
    }

    // AUTO SEED: KELAS
    $cntKelas = (int)$pdo->query("SELECT COUNT(*) FROM `data_kelas`")->fetchColumn();
    if ($cntKelas === 0) {
        $defaultKelas = get_default_seed_kelas();
        $ins = $pdo->prepare("INSERT INTO `data_kelas` (`no_urut`, `kelas`, `tingkat`, `jurusan`, `jurusan_full`, `kode_walikelas`, `nama_walikelas`, `mapel_walikelas`, `jumlah_siswa`, `ruang`, `is_aktif`, `urutan`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $u = 1;
        foreach ($defaultKelas as $k) {
            $ins->execute([
                $k['no'] ?? sprintf('%02d', $u),
                $k['kelas'] ?? '',
                $k['tingkat'] ?? 'X',
                $k['jurusan'] ?? '',
                $k['jurusan_full'] ?? '',
                $k['kode_walikelas'] ?? '',
                $k['nama_walikelas'] ?? '',
                $k['mapel_walikelas'] ?? '',
                (int)($k['jumlah_siswa'] ?? 0),
                $k['ruang'] ?? '',
                $u
            ]);
            $u++;
        }
    }

    // AUTO SEED: EKSTRAKURIKULER
    $cntEkskul = (int)$pdo->query("SELECT COUNT(*) FROM `data_ekskul`")->fetchColumn();
    if ($cntEkskul === 0) {
        $defaultEkskul = get_default_seed_ekskul();
        $ins = $pdo->prepare("INSERT INTO `data_ekskul` (`kode_ekskul`, `nama`, `kategori`, `kategori_label`, `cat_class`, `icon`, `icon_bg`, `pembina`, `ketua`, `jadwal`, `lokasi`, `jumlah_anggota`, `prestasi`, `deskripsi`, `is_aktif`, `urutan`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $u = 1;
        foreach ($defaultEkskul as $e) {
            $ins->execute([
                $e['id'] ?? sprintf('%02d', $u),
                $e['nama'] ?? '',
                $e['kategori'] ?? 'olahraga',
                $e['kategori_label'] ?? 'Olahraga',
                $e['cat_class'] ?? 'cat-olahraga',
                $e['icon'] ?? 'ph-soccer-ball',
                $e['icon_bg'] ?? 'icon-olahraga',
                $e['pembina'] ?? '',
                $e['ketua'] ?? '',
                $e['jadwal'] ?? '',
                $e['lokasi'] ?? '',
                (int)($e['jumlah_anggota'] ?? 0),
                $e['prestasi'] ?? '',
                $e['deskripsi'] ?? '',
                $u
            ]);
            $u++;
        }
    }
}

// =============================================================================
// CRUD HELPER: GURU & STAFF
// =============================================================================

function get_all_guru($onlyAktif = false, $kategori = null, $search = null) {
    $pdo = get_db_connection();
    $sql = "SELECT * FROM `data_guru` WHERE 1=1";
    $params = [];

    if ($onlyAktif) {
        $sql .= " AND `is_aktif` = 1";
    }
    if (!empty($kategori) && $kategori !== 'semua' && $kategori !== 'all') {
        $sql .= " AND `kategori` = ?";
        $params[] = $kategori;
    }
    if (!empty($search)) {
        $sql .= " AND (`nama` LIKE ? OR `peran` LIKE ? OR `mapel` LIKE ?)";
        $term = "%$search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= " ORDER BY `urutan` ASC, `id` ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format mapel as array
    foreach ($rows as &$r) {
        $r['no'] = $r['no_urut'];
        $decoded = json_decode($r['mapel'], true);
        if (is_array($decoded)) {
            $r['mapel'] = $decoded;
        } else {
            $r['mapel'] = array_filter(array_map('trim', explode(',', $r['mapel'])));
        }
    }
    return $rows;
}

function get_guru_by_id($id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM `data_guru` WHERE `id` = ?");
    $stmt->execute([(int)$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $r['no'] = $r['no_urut'];
        $decoded = json_decode($r['mapel'], true);
        $r['mapel'] = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $r['mapel'])));
    }
    return $r;
}

function add_guru($data) {
    $pdo = get_db_connection();
    $maxUrutan = (int)$pdo->query("SELECT MAX(`urutan`) FROM `data_guru`")->fetchColumn();
    $nextUrutan = $maxUrutan + 1;
    $noUrut = !empty($data['no_urut']) ? $data['no_urut'] : sprintf('%02d', $nextUrutan);

    $mapel = is_array($data['mapel']) ? json_encode(array_values($data['mapel']), JSON_UNESCAPED_UNICODE) : json_encode(array_filter(array_map('trim', explode(',', (string)$data['mapel']))), JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("INSERT INTO `data_guru` (`no_urut`, `nama`, `peran`, `mapel`, `kategori`, `foto`, `is_aktif`, `urutan`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $noUrut,
        trim($data['nama']),
        trim($data['peran'] ?? 'Guru Pengajar'),
        $mapel,
        trim($data['kategori'] ?? 'guru'),
        $data['foto'] ?? null,
        isset($data['is_aktif']) ? (int)$data['is_aktif'] : 1,
        (int)($data['urutan'] ?? $nextUrutan)
    ]);
}

function update_guru($id, $data) {
    $pdo = get_db_connection();
    $mapel = is_array($data['mapel']) ? json_encode(array_values($data['mapel']), JSON_UNESCAPED_UNICODE) : json_encode(array_filter(array_map('trim', explode(',', (string)$data['mapel']))), JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("UPDATE `data_guru` SET `no_urut` = ?, `nama` = ?, `peran` = ?, `mapel` = ?, `kategori` = ?, `foto` = ?, `is_aktif` = ?, `urutan` = ? WHERE `id` = ?");
    return $stmt->execute([
        trim($data['no_urut']),
        trim($data['nama']),
        trim($data['peran']),
        $mapel,
        trim($data['kategori']),
        $data['foto'] ?? null,
        isset($data['is_aktif']) ? (int)$data['is_aktif'] : 1,
        (int)($data['urutan'] ?? 0),
        (int)$id
    ]);
}

function delete_guru($id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("DELETE FROM `data_guru` WHERE `id` = ?");
    return $stmt->execute([(int)$id]);
}

function toggle_guru_aktif($id) {
    $pdo = get_db_connection();
    $curr = (int)$pdo->query("SELECT `is_aktif` FROM `data_guru` WHERE `id` = " . (int)$id)->fetchColumn();
    $new = $curr ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE `data_guru` SET `is_aktif` = ? WHERE `id` = ?");
    $stmt->execute([$new, (int)$id]);
    return $new;
}

// =============================================================================
// CRUD HELPER: SISWA
// =============================================================================

function get_all_siswa($onlyAktif = false, $tingkat = null, $jurusan = null, $search = null, $kelas = null) {
    $pdo = get_db_connection();
    $sql = "SELECT * FROM `data_siswa` WHERE 1=1";
    $params = [];

    if ($onlyAktif) {
        $sql .= " AND `is_aktif` = 1";
    }
    if (!empty($tingkat) && $tingkat !== 'semua' && $tingkat !== 'all') {
        $sql .= " AND `tingkat` = ?";
        $params[] = $tingkat;
    }
    if (!empty($jurusan) && $jurusan !== 'semua' && $jurusan !== 'all') {
        $sql .= " AND `jurusan` = ?";
        $params[] = $jurusan;
    }
    if (!empty($kelas) && $kelas !== 'semua' && $kelas !== 'all') {
        $sql .= " AND (`kelas` = ? OR `kelas` LIKE ?)";
        $params[] = $kelas;
        $params[] = $kelas . ' %';
    }
    if (!empty($search)) {
        $sql .= " AND (`nama` LIKE ? OR `nis` LIKE ? OR `nisn` LIKE ? OR `kelas` LIKE ?)";
        $term = "%$search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= " ORDER BY `urutan` ASC, `tingkat` DESC, `kelas` ASC, `id` ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['no'] = $r['no_urut'];
    }
    return $rows;
}

function get_siswa_by_id($id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM `data_siswa` WHERE `id` = ?");
    $stmt->execute([(int)$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) $r['no'] = $r['no_urut'];
    return $r;
}

function add_siswa($data) {
    $pdo = get_db_connection();
    $maxUrutan = (int)$pdo->query("SELECT MAX(`urutan`) FROM `data_siswa`")->fetchColumn();
    $nextUrutan = $maxUrutan + 1;
    $noUrut = !empty($data['no_urut']) ? $data['no_urut'] : sprintf('%02d', $nextUrutan);

    $stmt = $pdo->prepare("INSERT INTO `data_siswa` (`no_urut`, `nis`, `nisn`, `nama`, `jk`, `tingkat`, `jurusan`, `kelas`, `status`, `foto`, `is_aktif`, `urutan`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $noUrut,
        trim($data['nis'] ?? ''),
        trim($data['nisn'] ?? ''),
        trim($data['nama']),
        trim($data['jk'] ?? 'L'),
        trim($data['tingkat'] ?? 'X'),
        trim($data['jurusan'] ?? ''),
        trim($data['kelas'] ?? ''),
        trim($data['status'] ?? 'Aktif'),
        $data['foto'] ?? null,
        isset($data['is_aktif']) ? (int)$data['is_aktif'] : 1,
        (int)($data['urutan'] ?? $nextUrutan)
    ]);
}

function update_siswa($id, $data) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("UPDATE `data_siswa` SET `no_urut` = ?, `nis` = ?, `nisn` = ?, `nama` = ?, `jk` = ?, `tingkat` = ?, `jurusan` = ?, `kelas` = ?, `status` = ?, `foto` = ?, `is_aktif` = ?, `urutan` = ? WHERE `id` = ?");
    return $stmt->execute([
        trim($data['no_urut']),
        trim($data['nis']),
        trim($data['nisn']),
        trim($data['nama']),
        trim($data['jk']),
        trim($data['tingkat']),
        trim($data['jurusan']),
        trim($data['kelas']),
        trim($data['status']),
        $data['foto'] ?? null,
        isset($data['is_aktif']) ? (int)$data['is_aktif'] : 1,
        (int)($data['urutan'] ?? 0),
        (int)$id
    ]);
}

function delete_siswa($id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("DELETE FROM `data_siswa` WHERE `id` = ?");
    return $stmt->execute([(int)$id]);
}

function toggle_siswa_aktif($id) {
    $pdo = get_db_connection();
    $curr = (int)$pdo->query("SELECT `is_aktif` FROM `data_siswa` WHERE `id` = " . (int)$id)->fetchColumn();
    $new = $curr ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE `data_siswa` SET `is_aktif` = ? WHERE `id` = ?");
    $stmt->execute([$new, (int)$id]);
    return $new;
}

// =============================================================================
// CRUD HELPER: KELAS & ROMBEL
// =============================================================================

function get_all_kelas($onlyAktif = false, $tingkat = null, $jurusan = null, $search = null) {
    $pdo = get_db_connection();
    $sql = "SELECT * FROM `data_kelas` WHERE 1=1";
    $params = [];

    if ($onlyAktif) {
        $sql .= " AND `is_aktif` = 1";
    }
    if (!empty($tingkat) && $tingkat !== 'semua' && $tingkat !== 'all') {
        $sql .= " AND `tingkat` = ?";
        $params[] = $tingkat;
    }
    if (!empty($jurusan) && $jurusan !== 'semua' && $jurusan !== 'all') {
        $sql .= " AND `jurusan` = ?";
        $params[] = $jurusan;
    }
    if (!empty($search)) {
        $sql .= " AND (`kelas` LIKE ? OR `jurusan_full` LIKE ? OR `nama_walikelas` LIKE ? OR `ruang` LIKE ?)";
        $term = "%$search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= " ORDER BY `urutan` ASC, `tingkat` ASC, `kelas` ASC, `id` ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['no'] = $r['no_urut'];
    }
    return $rows;
}

function get_kelas_by_id($id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM `data_kelas` WHERE `id` = ?");
    $stmt->execute([(int)$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) $r['no'] = $r['no_urut'];
    return $r;
}

function add_kelas($data) {
    $pdo = get_db_connection();
    $maxUrutan = (int)$pdo->query("SELECT MAX(`urutan`) FROM `data_kelas`")->fetchColumn();
    $nextUrutan = $maxUrutan + 1;
    $noUrut = !empty($data['no_urut']) ? $data['no_urut'] : sprintf('%02d', $nextUrutan);
    $siswaL = (int)($data['siswa_l'] ?? 0);
    $siswaP = (int)($data['siswa_p'] ?? 0);
    $jmlSiswa = ($siswaL + $siswaP > 0) ? ($siswaL + $siswaP) : (int)($data['jumlah_siswa'] ?? 0);

    $stmt = $pdo->prepare("INSERT INTO `data_kelas` (`no_urut`, `kelas`, `tingkat`, `jurusan`, `jurusan_full`, `kode_walikelas`, `nama_walikelas`, `mapel_walikelas`, `siswa_l`, `siswa_p`, `jumlah_siswa`, `ruang`, `is_aktif`, `urutan`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $noUrut,
        trim($data['kelas']),
        trim($data['tingkat'] ?? 'X'),
        trim($data['jurusan'] ?? ''),
        trim($data['jurusan_full'] ?? ''),
        trim($data['kode_walikelas'] ?? ''),
        trim($data['nama_walikelas'] ?? ''),
        trim($data['mapel_walikelas'] ?? ''),
        $siswaL,
        $siswaP,
        $jmlSiswa,
        trim($data['ruang'] ?? ''),
        isset($data['is_aktif']) ? (int)$data['is_aktif'] : 1,
        (int)($data['urutan'] ?? $nextUrutan)
    ]);
}

function update_kelas($id, $data) {
    $pdo = get_db_connection();
    $siswaL = (int)($data['siswa_l'] ?? 0);
    $siswaP = (int)($data['siswa_p'] ?? 0);
    $jmlSiswa = ($siswaL + $siswaP > 0) ? ($siswaL + $siswaP) : (int)($data['jumlah_siswa'] ?? 0);

    $stmt = $pdo->prepare("UPDATE `data_kelas` SET `no_urut` = ?, `kelas` = ?, `tingkat` = ?, `jurusan` = ?, `jurusan_full` = ?, `kode_walikelas` = ?, `nama_walikelas` = ?, `mapel_walikelas` = ?, `siswa_l` = ?, `siswa_p` = ?, `jumlah_siswa` = ?, `ruang` = ?, `is_aktif` = ?, `urutan` = ? WHERE `id` = ?");
    return $stmt->execute([
        trim($data['no_urut']),
        trim($data['kelas']),
        trim($data['tingkat']),
        trim($data['jurusan']),
        trim($data['jurusan_full']),
        trim($data['kode_walikelas']),
        trim($data['nama_walikelas']),
        trim($data['mapel_walikelas']),
        $siswaL,
        $siswaP,
        $jmlSiswa,
        trim($data['ruang']),
        isset($data['is_aktif']) ? (int)$data['is_aktif'] : 1,
        (int)($data['urutan'] ?? 0),
        (int)$id
    ]);
}

function delete_kelas($id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("DELETE FROM `data_kelas` WHERE `id` = ?");
    return $stmt->execute([(int)$id]);
}

function toggle_kelas_aktif($id) {
    $pdo = get_db_connection();
    $curr = (int)$pdo->query("SELECT `is_aktif` FROM `data_kelas` WHERE `id` = " . (int)$id)->fetchColumn();
    $new = $curr ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE `data_kelas` SET `is_aktif` = ? WHERE `id` = ?");
    $stmt->execute([$new, (int)$id]);
    return $new;
}

// =============================================================================
// CRUD HELPER: EKSTRAKURIKULER
// =============================================================================

function get_all_ekskul($onlyAktif = false, $kategori = null, $search = null) {
    $pdo = get_db_connection();
    $sql = "SELECT * FROM `data_ekskul` WHERE 1=1";
    $params = [];

    if ($onlyAktif) {
        $sql .= " AND `is_aktif` = 1";
    }
    if (!empty($kategori) && $kategori !== 'semua' && $kategori !== 'all') {
        $sql .= " AND `kategori` = ?";
        $params[] = $kategori;
    }
    if (!empty($search)) {
        $sql .= " AND (`nama` LIKE ? OR `pembina` LIKE ? OR `ketua` LIKE ? OR `deskripsi` LIKE ?)";
        $term = "%$search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= " ORDER BY `urutan` ASC, `id` ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['no'] = $r['kode_ekskul'];
    }
    return $rows;
}

function get_ekskul_by_id($id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM `data_ekskul` WHERE `id` = ?");
    $stmt->execute([(int)$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) $r['no'] = $r['kode_ekskul'];
    return $r;
}

function add_ekskul($data) {
    $pdo = get_db_connection();
    $maxUrutan = (int)$pdo->query("SELECT MAX(`urutan`) FROM `data_ekskul`")->fetchColumn();
    $nextUrutan = $maxUrutan + 1;
    $maxKode = (int)$pdo->query("SELECT MAX(CAST(`kode_ekskul` AS UNSIGNED)) FROM `data_ekskul`")->fetchColumn();
    $autoKode = sprintf('%02d', max($maxKode, $maxUrutan) + 1);
    $kode = !empty($data['kode_ekskul']) ? $data['kode_ekskul'] : $autoKode;

    $cat = strtolower(trim($data['kategori'] ?? 'olahraga'));
    $catMap = [
        'olahraga'   => ['label' => 'Olahraga & Bela Diri', 'class' => 'cat-olahraga', 'icon' => 'ph-soccer-ball', 'bg' => 'icon-olahraga'],
        'teknologi'  => ['label' => 'Teknologi & Sains', 'class' => 'cat-teknologi', 'icon' => 'ph-code-block', 'bg' => 'icon-teknologi'],
        'kebangsaan' => ['label' => 'Kebangsaan & Kepanduan', 'class' => 'cat-kebangsaan', 'icon' => 'ph-compass', 'bg' => 'icon-kebangsaan'],
        'seni'       => ['label' => 'Seni & Budaya', 'class' => 'cat-seni', 'icon' => 'ph-palette', 'bg' => 'icon-seni'],
        'bahasa'     => ['label' => 'Bahasa & Komunikasi', 'class' => 'cat-bahasa', 'icon' => 'ph-translate', 'bg' => 'icon-bahasa'],
        'keagamaan'  => ['label' => 'Keagamaan & Rohis', 'class' => 'cat-keagamaan', 'icon' => 'ph-hands-praying', 'bg' => 'icon-keagamaan'],
    ];

    $label    = $data['kategori_label'] ?? ($catMap[$cat]['label'] ?? ucfirst($cat));
    $catClass = $data['cat_class'] ?? ($catMap[$cat]['class'] ?? 'cat-olahraga');
    $icon     = !empty($data['icon']) ? $data['icon'] : ($catMap[$cat]['icon'] ?? 'ph-trophy');
    $iconBg   = $data['icon_bg'] ?? ($catMap[$cat]['bg'] ?? 'icon-olahraga');

    $stmt = $pdo->prepare("INSERT INTO `data_ekskul` (`kode_ekskul`, `nama`, `kategori`, `kategori_label`, `cat_class`, `icon`, `icon_bg`, `pembina`, `ketua`, `jadwal`, `lokasi`, `jumlah_anggota`, `prestasi`, `deskripsi`, `foto`, `is_aktif`, `urutan`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $kode,
        trim($data['nama']),
        $cat,
        $label,
        $catClass,
        $icon,
        $iconBg,
        trim($data['pembina'] ?? ''),
        trim($data['ketua'] ?? ''),
        trim($data['jadwal'] ?? ''),
        trim($data['lokasi'] ?? ''),
        (int)($data['jumlah_anggota'] ?? 0),
        trim($data['prestasi'] ?? ''),
        trim($data['deskripsi'] ?? ''),
        $data['foto'] ?? null,
        isset($data['is_aktif']) ? (int)$data['is_aktif'] : 1,
        (int)($data['urutan'] ?? $nextUrutan)
    ]);
}

function update_ekskul($id, $data) {
    $pdo = get_db_connection();
    $cat = strtolower(trim($data['kategori'] ?? 'olahraga'));
    $catMap = [
        'olahraga'   => ['label' => 'Olahraga & Bela Diri', 'class' => 'cat-olahraga', 'icon' => 'ph-soccer-ball', 'bg' => 'icon-olahraga'],
        'teknologi'  => ['label' => 'Teknologi & Sains', 'class' => 'cat-teknologi', 'icon' => 'ph-code-block', 'bg' => 'icon-teknologi'],
        'kebangsaan' => ['label' => 'Kebangsaan & Kepanduan', 'class' => 'cat-kebangsaan', 'icon' => 'ph-compass', 'bg' => 'icon-kebangsaan'],
        'seni'       => ['label' => 'Seni & Budaya', 'class' => 'cat-seni', 'icon' => 'ph-palette', 'bg' => 'icon-seni'],
        'bahasa'     => ['label' => 'Bahasa & Komunikasi', 'class' => 'cat-bahasa', 'icon' => 'ph-translate', 'bg' => 'icon-bahasa'],
        'keagamaan'  => ['label' => 'Keagamaan & Rohis', 'class' => 'cat-keagamaan', 'icon' => 'ph-hands-praying', 'bg' => 'icon-keagamaan'],
    ];

    $label    = !empty($data['kategori_label']) ? $data['kategori_label'] : ($catMap[$cat]['label'] ?? ucfirst($cat));
    $catClass = !empty($data['cat_class']) ? $data['cat_class'] : ($catMap[$cat]['class'] ?? 'cat-olahraga');
    $icon     = !empty($data['icon']) ? $data['icon'] : ($catMap[$cat]['icon'] ?? 'ph-trophy');
    $iconBg   = !empty($data['icon_bg']) ? $data['icon_bg'] : ($catMap[$cat]['bg'] ?? 'icon-olahraga');

    $kodeEkskul = trim($data['kode_ekskul'] ?? '');
    if (empty($kodeEkskul)) {
        $existing = get_ekskul_by_id($id);
        $kodeEkskul = $existing['kode_ekskul'] ?? sprintf('%02d', (int)$id);
    }

    $stmt = $pdo->prepare("UPDATE `data_ekskul` SET `kode_ekskul` = ?, `nama` = ?, `kategori` = ?, `kategori_label` = ?, `cat_class` = ?, `icon` = ?, `icon_bg` = ?, `pembina` = ?, `ketua` = ?, `jadwal` = ?, `lokasi` = ?, `jumlah_anggota` = ?, `prestasi` = ?, `deskripsi` = ?, `foto` = ?, `is_aktif` = ?, `urutan` = ? WHERE `id` = ?");
    return $stmt->execute([
        $kodeEkskul,
        trim($data['nama']),
        $cat,
        $label,
        $catClass,
        $icon,
        $iconBg,
        trim($data['pembina']),
        trim($data['ketua']),
        trim($data['jadwal']),
        trim($data['lokasi']),
        (int)($data['jumlah_anggota'] ?? 0),
        trim($data['prestasi']),
        trim($data['deskripsi']),
        $data['foto'] ?? null,
        isset($data['is_aktif']) ? (int)$data['is_aktif'] : 1,
        (int)($data['urutan'] ?? 0),
        (int)$id
    ]);
}

function delete_ekskul($id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("DELETE FROM `data_ekskul` WHERE `id` = ?");
    return $stmt->execute([(int)$id]);
}

function toggle_ekskul_aktif($id) {
    $pdo = get_db_connection();
    $curr = (int)$pdo->query("SELECT `is_aktif` FROM `data_ekskul` WHERE `id` = " . (int)$id)->fetchColumn();
    $new = $curr ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE `data_ekskul` SET `is_aktif` = ? WHERE `id` = ?");
    $stmt->execute([$new, (int)$id]);
    return $new;
}
