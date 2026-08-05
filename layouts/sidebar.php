<?php
/*
|--------------------------------------------------------------------------
| File        : sidebar.php
| Folder      : layouts
| Fungsi      : Sidebar Menu Aplikasi
|--------------------------------------------------------------------------
|
| Sidebar akan menampilkan menu sesuai level pengguna.
|
|--------------------------------------------------------------------------
*/
?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 bg-dark text-white min-vh-100 p-0">
            <div class="p-3">
                <h5 class="text-center mb-4">
                    MENU
                </h5>
                <div class="list-group list-group-flush">
                    <?php if ($_SESSION['level'] == 'Administrator') { ?>
                        <!-- Dashboard -->
                        <a href="<?= BASE_URL ?>/administrator/dashboard.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-house-door"></i>
                            Dashboard
                        </a>
                        <a href="<?= BASE_URL ?>/administrator/user/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-people"></i>
                            Data User
                        </a>
                        <a href="<?= BASE_URL ?>/administrator/kategori/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-tags"></i>
                            Kategori
                        </a>
                        <a href="<?= BASE_URL ?>/administrator/alat/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-tools"></i>
                            Data Alat
                        </a>
                        <a href="<?= BASE_URL ?>/administrator/peminjaman/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-left-right"></i>
                            Data Peminjaman
                        </a>
                        <a href="<?= BASE_URL ?>/administrator/pengembalian/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-return-left"></i>
                            Data Pengembalian
                        </a>
                        <a href="laporan/index.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-printer"></i>
                            Laporan
                        </a>
                        <a href="<?= BASE_URL ?>/administrator/log_aktivitas/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-clock-history"></i>
                            Log Aktivitas
                        </a>
                    <?php } ?>
                    <?php if ($_SESSION['level'] == 'Petugas') { ?>
                        <a href="<?= BASE_URL ?>/petugas/dashboard/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-speedometer2"></i>
                            Dashboard
                        </a>
                        <!-- Persetujuan Peminjaman -->
                        <a href="<?= BASE_URL ?>/petugas/peminjaman/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-check2-square"></i>
                            Persetujuan Peminjaman
                        </a>
                        <!-- Verifikasi Pengembalian -->
                        <a href="<?= BASE_URL ?>/petugas/verifikasi_pengembalian/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-clipboard-check"></i>
                            Verifikasi Pengembalian
                        </a>
                        <!-- Riwayat Pengembalian -->
                        <a href="<?= BASE_URL ?>/petugas/pengembalian/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-box-arrow-in-down"></i>
                            Riwayat Pengembalian
                        </a>
                        <!-- Laporan -->
                        <a href="<?= BASE_URL ?>/petugas/laporan/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-printer"></i>
                            Laporan
                        </a>
                    <?php } ?>
                    <?php if ($_SESSION['level'] == 'Peminjam') { ?>
                        <a href="<?= BASE_URL ?>/peminjam/dashboard/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-speedometer2"></i>
                            Dashboard
                        </a>
                        <a href="<?= BASE_URL ?>/peminjam/alat/index.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-tools"></i>
                            Daftar Alat
                        </a>
                        <a href="<?= BASE_URL ?>/peminjam/peminjaman/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Peminjaman
                        </a>
                        <a href="<?= BASE_URL ?>/peminjam/pengembalian/index.php"
                            class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-return-left"></i>
                            Pengembalian
                        </a>
                        <a href="<?= BASE_URL ?>/peminjam/riwayat/index.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-clock-history"></i>
                            Riwayat Peminjaman
                        </a>
                        <a href="<?= BASE_URL ?>/peminjam/profil/index.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-circle"></i>
                            Profil Saya
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- Content -->
        <div class="col-md-10 p-4">