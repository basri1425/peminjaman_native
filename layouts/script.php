<!-- Bootstrap 5 Bundle (Popper sudah termasuk) -->
<script src="<?= BASE_URL ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Script Aplikasi -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        /*
        |--------------------------------------------------------------------------
        | Konfirmasi Hapus
        |--------------------------------------------------------------------------
        */
        document.querySelectorAll('.btn-hapus').forEach(function(button) {
            button.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.preventDefault();
                }
            });
        });
        /*
        |--------------------------------------------------------------------------
        | Preview Foto
        |--------------------------------------------------------------------------
        */
        const foto = document.getElementById('foto');
        if (foto) {
            foto.addEventListener('change', function() {
                const preview = document.getElementById('previewFoto');
                if (!preview) {
                    return;
                }
                const file = this.files[0];
                if (file) {
                    preview.src = URL.createObjectURL(file);
                }
            });
        }
    });
</script>
