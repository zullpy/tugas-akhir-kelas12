        </main>
    </div>
</div>

<script>
// Helper Konfirmasi Logout dengan SweetAlert2
function konfirmasiLogout(e, url) {
    if (e) e.preventDefault();
    Swal.fire({
        title: 'Keluar dari Panel Admin?',
        text: 'Sesi Anda saat ini akan diakhiri.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i class="ph-bold ph-sign-out"></i> Ya, Logout',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
    return false;
}

// Helper Konfirmasi Hapus Data Pendaftar dengan SweetAlert2
function konfirmasiHapus(e, url, nama) {
    if (e) e.preventDefault();
    const namaSiswa = nama ? `<strong>${nama}</strong>` : 'calon siswa ini';
    Swal.fire({
        title: 'Hapus Data Pendaftar?',
        html: `Apakah Anda yakin ingin menghapus data ${namaSiswa}?<br><span style="color:#DC2626; font-size:0.9rem; font-weight:700;">Perhatian: Seluruh berkas dan data pendaftar ini akan dihapus permanen dari server!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#64748B',
        confirmButtonText: '<i class="ph-bold ph-trash"></i> Ya, Hapus Permanen',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
    return false;
}

// Deteksi Toast Otomatis berdasarkan URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    
    if (msg === 'deleted') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Data pendaftar berhasil dihapus!',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });
    } else if (msg === 'edited') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Perubahan data berhasil disimpan!',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });
    }
});
</script>

</body>
</html>
