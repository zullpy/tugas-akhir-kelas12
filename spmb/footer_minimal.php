<?php
// spmb/footer_minimal.php
// Footer Minimalis 1-Baris Sederhana Khusus SPMB SMKS SUKAPURA
// Ringan & Bebas Distraksi agar Calon Siswa Fokus Mengisi Formulir

if (!isset($base_url)) {
    $base_url = '../';
}
if (!isset($settings)) {
    require_once __DIR__ . '/config.php';
    $settings = get_all_settings();
}
$waPanitia = !empty($settings['fonnte_target_admin']) ? preg_replace('/[^0-9]/', '', $settings['fonnte_target_admin']) : '081958251765';
if (substr($waPanitia, 0, 1) === '0') {
    $waPanitia = '62' . substr($waPanitia, 1);
}
?>
<footer style="background: #FFFFFF; border-top: 1.5px solid #E2E8F0; padding: 22px 20px; margin-top: auto; font-family: var(--font-body, 'Plus Jakarta Sans', sans-serif);">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; font-size: 0.85rem; color: #64748B;">
        <div style="font-weight: 600;">
            &copy; <?php echo date('Y'); ?> <strong style="color: #0F172A;">SMKS Sukapura</strong> Kabupaten Tasikmalaya &bull; Portal SPMB Online
        </div>
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="https://wa.me/<?php echo htmlspecialchars($waPanitia); ?>?text=Halo%20Panitia%20SPMB%20SMKS%20Sukapura%2C%20saya%20butuh%20bantuan" 
               target="_blank" rel="noopener noreferrer" 
               style="color: #059669; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                <i class="ph-bold ph-whatsapp-logo" style="font-size: 1.1rem;"></i> Bantuan Panitia
            </a>
            <span style="color: #CBD5E1;">&bull;</span>
            <a href="<?php echo $base_url; ?>index.php" style="color: #0284C7; font-weight: 700; text-decoration: none;">
                Website Utama
            </a>
        </div>
    </div>
</footer>
