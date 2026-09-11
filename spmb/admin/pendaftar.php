<?php
// spmb/admin/pendaftar.php
// Kelola Data Pendaftar SPMB SMKS SUKAPURA
$adminPageTitle = 'Data Pendaftar';
$adminPageHeading = 'Manajemen Calon Peserta Didik Baru';

require_once __DIR__ . '/header.php';

// Filter & Pencarian
$qStatus  = sanitize_input($_GET['status'] ?? '');
$qJurusan = sanitize_input($_GET['jurusan'] ?? '');
$qJalur   = sanitize_input($_GET['jalur'] ?? '');
$qSearch  = sanitize_input($_GET['q'] ?? '');

$sql = "SELECT * FROM `spmb_pendaftar` WHERE 1=1";
$params = [];

if (!empty($qStatus)) {
    $sql .= " AND `status` = ?";
    $params[] = $qStatus;
}

if (!empty($qJurusan)) {
    $sql .= " AND (`jurusan_1` = ? OR `jurusan_2` = ?)";
    $params[] = $qJurusan;
    $params[] = $qJurusan;
}

if (!empty($qJalur)) {
    $sql .= " AND `jalur` = ?";
    $params[] = $qJalur;
}

if (!empty($qSearch)) {
    $sql .= " AND (`no_pendaftaran` LIKE ? OR `nisn` LIKE ? OR `nama_lengkap` LIKE ? OR `asal_sekolah` LIKE ?)";
    $term = "%$qSearch%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY `id` DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pendaftarList = $stmt->fetchAll();

// Handle pesan alert
$msg = sanitize_input($_GET['msg'] ?? '');
?>

<?php if ($msg === 'deleted'): ?>
    <div class="spmb-alert spmb-alert-success">
        <i class="ph-bold ph-check-circle" style="font-size:1.4rem;"></i>
        <div>Data pendaftar telah berhasil dihapus dari sistem.</div>
    </div>
<?php elseif ($msg === 'updated'): ?>
    <div class="spmb-alert spmb-alert-success">
        <i class="ph-bold ph-check-circle" style="font-size:1.4rem;"></i>
        <div>Status dan data pendaftar berhasil diperbarui!</div>
    </div>
<?php elseif ($msg === 'added'): ?>
    <div class="spmb-alert spmb-alert-success">
        <i class="ph-bold ph-check-circle" style="font-size:1.4rem;"></i>
        <div>Pendaftar baru berhasil ditambahkan secara manual.</div>
    </div>
<?php endif; ?>

<!-- FILTER & SEARCH BAR -->
<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-funnel"></i> Filter &amp; Pencarian
        </h3>
        <!-- <div style="display:flex; gap:10px;">
            <a href="tambah.php" class="adm-btn adm-btn-sm adm-btn-primary">
                <i class="ph-bold ph-plus"></i> Tambah Pendaftar
            </a>
            <a href="export.php?status=<?php echo urlencode($qStatus); ?>&jurusan=<?php echo urlencode($qJurusan); ?>&jalur=<?php echo urlencode($qJalur); ?>&q=<?php echo urlencode($qSearch); ?>" class="adm-btn adm-btn-sm adm-btn-secondary">
                <i class="ph-bold ph-file-csv"></i> Ekspor CSV
            </a>
        </div> -->
    </div>
    <div class="adm-card-body">
        <?php $hasFilter = !empty($qStatus) || !empty($qJurusan) || !empty($qJalur) || !empty($qSearch); ?>
        <form action="pendaftar.php" method="GET" class="adm-filter-form">
            <!-- Baris 1: Kolom Pencarian Utama & Tombol Aksi -->
            <div class="adm-filter-main-row">
                <div class="adm-search-input-group">
                    <i class="ph-bold ph-magnifying-glass adm-search-icon"></i>
                    <input type="text" name="q" class="adm-search-input" placeholder="Cari nama pendaftar, NISN, no registrasi, asal sekolah..." value="<?php echo htmlspecialchars($qSearch); ?>">
                    <?php if (!empty($qSearch)): ?>
                        <a href="pendaftar.php?status=<?php echo urlencode($qStatus); ?>&jurusan=<?php echo urlencode($qJurusan); ?>&jalur=<?php echo urlencode($qJalur); ?>" class="adm-search-clear" title="Hapus teks pencarian">
                            <i class="ph-bold ph-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="adm-filter-actions">
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="ph-bold ph-magnifying-glass"></i> Filter
                    </button>
                    <?php if ($hasFilter): ?>
                        <a href="pendaftar.php" class="adm-btn adm-btn-secondary" title="Reset semua filter">
                            <i class="ph-bold ph-arrow-counter-clockwise"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Baris 2: Grid 3 Dropdown Kategori Filter -->
            <div class="adm-filter-grid">
                <div class="adm-filter-col">
                    <label class="adm-filter-label">
                        <i class="ph-bold ph-traffic-signal"></i> Status Pendaftar
                    </label>
                    <div class="adm-select-wrapper">
                        <select name="status" class="adm-filter-select" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="Menunggu Verifikasi" <?php echo ($qStatus === 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                            <option value="Diterima" <?php echo ($qStatus === 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                            <option value="Cadangan" <?php echo ($qStatus === 'Cadangan') ? 'selected' : ''; ?>>Cadangan</option>
                            <option value="Perlu Perbaikan" <?php echo ($qStatus === 'Perlu Perbaikan') ? 'selected' : ''; ?>>Perlu Perbaikan</option>
                            <option value="Ditolak" <?php echo ($qStatus === 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                </div>

                <div class="adm-filter-col">
                    <label class="adm-filter-label">
                        <i class="ph-bold ph-graduation-cap"></i> Pilihan Jurusan
                    </label>
                    <div class="adm-select-wrapper">
                        <select name="jurusan" class="adm-filter-select" onchange="this.form.submit()">
                            <option value="">-- Semua Jurusan --</option>
                            <?php foreach ($DAFTAR_JURUSAN as $kode => $j): ?>
                                <option value="<?php echo $kode; ?>" <?php echo ($qJurusan === $kode) ? 'selected' : ''; ?>>
                                    <?php echo $kode; ?> - <?php echo $j['nama']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="adm-filter-col">
                    <label class="adm-filter-label">
                        <i class="ph-bold ph-path"></i> Jalur Pendaftaran
                    </label>
                    <div class="adm-select-wrapper">
                        <select name="jalur" class="adm-filter-select" onchange="this.form.submit()">
                            <option value="">-- Semua Jalur --</option>
                            <?php foreach ($DAFTAR_JALUR as $kJalur => $vJalur): ?>
                                <option value="<?php echo $kJalur; ?>" <?php echo ($qJalur === $kJalur) ? 'selected' : ''; ?>>
                                    Jalur <?php echo $kJalur; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Baris 3: Indikator Chip Filter Aktif (bila ada yang dipilih) -->
            <?php if ($hasFilter): ?>
                <div class="adm-filter-badges">
                    <span class="adm-filter-badge-title">
                        <i class="ph-bold ph-sliders"></i> Filter aktif:
                    </span>
                    <?php if (!empty($qSearch)): ?>
                        <span class="adm-filter-chip">
                            Pencarian: <strong>"<?php echo htmlspecialchars($qSearch); ?>"</strong>
                            <a href="pendaftar.php?status=<?php echo urlencode($qStatus); ?>&jurusan=<?php echo urlencode($qJurusan); ?>&jalur=<?php echo urlencode($qJalur); ?>" title="Hapus filter ini"><i class="ph-bold ph-x"></i></a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($qStatus)): ?>
                        <span class="adm-filter-chip">
                            Status: <strong><?php echo htmlspecialchars($qStatus); ?></strong>
                            <a href="pendaftar.php?q=<?php echo urlencode($qSearch); ?>&jurusan=<?php echo urlencode($qJurusan); ?>&jalur=<?php echo urlencode($qJalur); ?>" title="Hapus filter ini"><i class="ph-bold ph-x"></i></a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($qJurusan)): ?>
                        <span class="adm-filter-chip">
                            Jurusan: <strong><?php echo htmlspecialchars($qJurusan); ?></strong>
                            <a href="pendaftar.php?q=<?php echo urlencode($qSearch); ?>&status=<?php echo urlencode($qStatus); ?>&jalur=<?php echo urlencode($qJalur); ?>" title="Hapus filter ini"><i class="ph-bold ph-x"></i></a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($qJalur)): ?>
                        <span class="adm-filter-chip">
                            Jalur: <strong><?php echo htmlspecialchars($qJalur); ?></strong>
                            <a href="pendaftar.php?q=<?php echo urlencode($qSearch); ?>&status=<?php echo urlencode($qStatus); ?>&jurusan=<?php echo urlencode($qJurusan); ?>" title="Hapus filter ini"><i class="ph-bold ph-x"></i></a>
                        </span>
                    <?php endif; ?>
                    <a href="pendaftar.php" class="adm-filter-clear-link">
                        <i class="ph-bold ph-trash"></i> Hapus Semua Filter
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- DATA TABLE -->
<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="ph-bold ph-list-numbers"></i> Total: <?php echo count($pendaftarList); ?> Calon Siswa
        </h3>
    </div>
    <div class="adm-card-body" style="padding:0;">
        <div class="adm-table-responsive">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>No. Registrasi</th>
                        <th>Data Siswa</th>
                        <th>Asal SMP</th>
                        <th>Pilihan Jurusan</th>
                        <th>Jalur</th>
                        <th>Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendaftarList)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:#666;">
                                Tidak ada data pendaftar yang sesuai filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($pendaftarList as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <strong style="font-family:monospace;"><?php echo htmlspecialchars($row['no_pendaftaran']); ?></strong><br>
                                    <span style="font-size:0.75rem; color:#666;"><?php echo date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></span>
                                </td>
                                <td>
                                    <strong style="font-size:0.95rem;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong><br>
                                    <span style="font-size:0.8rem; color:#555;">NISN: <?php echo htmlspecialchars($row['nisn']); ?> (<?php echo $row['jenis_kelamin']; ?>)</span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['asal_sekolah']); ?>
                                </td>
                                <td>
                                    <strong>P1: <?php echo htmlspecialchars($row['jurusan_1']); ?></strong>
                                    <?php if (!empty($row['jurusan_2'])): ?>
                                        <br><span style="font-size:0.8rem; color:#666;">P2: <?php echo htmlspecialchars($row['jurusan_2']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($row['status'] === 'Diterima' && !empty($row['jurusan_diterima'])): ?>
                                        <br><span class="spmb-badge" style="background:#DCFCE7; color:#15803D; font-size:0.75rem; border:1px solid #86EFAC; margin-top:3px;">
                                            <i class="ph-bold ph-check"></i> Lulus: <?php echo htmlspecialchars($row['jurusan_diterima']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo get_jalur_badge_html($row['jalur']); ?>
                                </td>
                                <td>
                                    <?php echo get_status_badge_html($row['status']); ?>
                                </td>
                                <td>
                                    <div style="display:flex; gap:6px; justify-content:center;">
                                        <a href="detail.php?id=<?php echo $row['id']; ?>" class="adm-btn adm-btn-sm adm-btn-primary" title="Detail &amp; Verifikasi">
                                            <i class="ph-bold ph-pencil-simple"></i> Detail
                                        </a>
                                        <a href="../cetak.php?no=<?php echo urlencode($row['no_pendaftaran']); ?>&from=admin" class="adm-btn adm-btn-sm adm-btn-secondary" title="Cetak Bukti">
                                            <i class="ph-bold ph-printer"></i>
                                        </a>
                                        <a href="hapus.php?id=<?php echo $row['id']; ?>" class="adm-btn adm-btn-sm adm-btn-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus calon siswa <?php echo addslashes($row['nama_lengkap']); ?>? Data tidak dapat dikembalikan.');">
                                            <i class="ph-bold ph-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
