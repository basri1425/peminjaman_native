<?php
/*
|--------------------------------------------------------------------------
| File        : navbar.php
| Folder      : layouts
| Fungsi      : Navbar Aplikasi
|--------------------------------------------------------------------------
|
| Navbar digunakan pada seluruh halaman setelah pengguna login.
|
|--------------------------------------------------------------------------
*/
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">

    <div class="container-fluid">

        <!-- Logo / Nama Aplikasi -->
        <a class="navbar-brand fw-bold" href="#">

            Aplikasi Peminjaman Alat

        </a>

        <!-- Tombol Collapse -->
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu"
                aria-controls="navbarMenu"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>

        </button>

        <!-- Menu Navbar -->
        <div class="collapse navbar-collapse"
             id="navbarMenu">

            <ul class="navbar-nav ms-auto align-items-center">

                <!-- Nama User -->
                <li class="nav-item me-3">

                    <span class="text-white">

                        <i class="bi bi-person-circle"></i>

                        <?= $_SESSION['nama_lengkap']; ?>

                        |

                        <?= $_SESSION['level']; ?>

                    </span>

                </li>

                <!-- Logout -->
                <li class="nav-item">

                    <a href="../auth/logout.php"
                       class="btn btn-danger btn-sm">

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>