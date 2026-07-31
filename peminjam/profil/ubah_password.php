<?php

require '../../config/session.php';
require '../../config/database.php';

/*
|--------------------------------------------------------------------------
| Validasi Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_user'])) {

    header("Location: ../../login.php");
    exit();

}

/*
|--------------------------------------------------------------------------
| Validasi Hak Akses
|--------------------------------------------------------------------------
*/

if ($_SESSION['level'] != 'Peminjam') {

    header("Location: ../../unauthorized.php");
    exit();

}

?>

<?php include '../../layouts/header.php'; ?>

<?php include '../../layouts/navbar.php'; ?>

<?php include '../../layouts/sidebar.php'; ?>

<div class="content-wrapper">

<section class="content-header">

<div class="container-fluid">

<div class="row mb-2">

<div class="col-sm-6">

<h1>

<i class="fas fa-key"></i>

Ubah Password

</h1>

<p class="text-muted">

Silakan masukkan password lama dan password baru untuk menjaga keamanan akun Anda.

</p>

</div>

</div>

</div>

</section>

<section class="content">

<div class="container-fluid">

<?php if (isset($_SESSION['success'])) { ?>

<div class="alert alert-success alert-dismissible fade show">

<button
type="button"
class="btn-close"
data-bs-dismiss="alert"></button>

<?= htmlspecialchars($_SESSION['success']); ?>

</div>

<?php unset($_SESSION['success']); } ?>

<?php if (isset($_SESSION['error'])) { ?>

<div class="alert alert-danger alert-dismissible fade show">

<button
type="button"
class="btn-close"
data-bs-dismiss="alert"></button>

<?= htmlspecialchars($_SESSION['error']); ?>

</div>

<?php unset($_SESSION['error']); } ?>

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-lock"></i>

Form Ubah Password

</h3>

</div>

<form action="proses_password.php" method="POST">

<div class="card-body">

<div class="mb-3">

<label class="form-label">

Password Lama

</label>

<input
type="password"
name="password_lama"
class="form-control"
required
autocomplete="current-password">

</div>

<div class="mb-3">

<label class="form-label">

Password Baru

</label>

<input
type="password"
name="password_baru"
class="form-control"
required
minlength="8"
autocomplete="new-password">

<small class="text-muted">

Password minimal terdiri dari 8 karakter.

</small>

</div>

<div class="mb-3">

<label class="form-label">

Konfirmasi Password Baru

</label>

<input
type="password"
name="konfirmasi_password"
class="form-control"
required
autocomplete="new-password">

</div>
                </div>

                <div class="card-footer text-end">

                    <a
                        href="index.php"
                        class="btn btn-secondary">

                        <i class="fas fa-arrow-left"></i>

                        Batal

                    </a>

                    <button
                        type="submit"
                        class="btn btn-danger">

                        <i class="fas fa-key"></i>

                        Simpan Password

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</div>

</section>

</div>

<?php

$conn->close();

?>

<?php include '../../layouts/footer.php'; ?>
