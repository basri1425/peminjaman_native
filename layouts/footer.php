<?php
/*
|--------------------------------------------------------------------------
| File        : footer.php
| Folder      : layouts
| Fungsi      : Footer Layout Aplikasi
|--------------------------------------------------------------------------
|
| File ini berfungsi untuk:
| - Menutup area content
| - Menampilkan footer aplikasi
| - Memanggil Bootstrap JavaScript
| - Menutup tag HTML
|
|--------------------------------------------------------------------------
*/
?>
</div>
<!-- End Content -->
</div>
<!-- End Row -->
</div>
<!-- End Container -->
<footer class="bg-white border-top py-3 mt-auto">
    <div class="container-fluid">
        <div class="text-center text-muted">
            &copy; <?= date('Y') ?>
            Aplikasi Peminjaman Alat |
            UKK Rekayasa Perangkat Lunak
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="<?= BASE_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
