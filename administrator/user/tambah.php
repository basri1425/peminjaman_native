<?php
/*
|--------------------------------------------------------------------------
| File        : create.php
| Folder      : administrator/user
| Fungsi      : Form Tambah Data User
|--------------------------------------------------------------------------
*/

require_once '../../config/session.php';
require_once '../../config/database.php';

if ($_SESSION['level'] != 'Administrator') {
    header('Location: ../../auth/login.php');
    exit();
}

$title = 'Tambah User';

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3>

                <i class="bi bi-person-plus-fill"></i>

                Tambah User

            </h3>

            <p class="text-muted mb-0">

                Form untuk menambahkan data pengguna baru.

            </p>

        </div>

        <a href="index.php" class="btn btn-secondary">

            <i class="bi bi-arrow-left"></i>

            Kembali

        </a>

    </div>

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            Form Data User

        </div>

        <div class="card-body">

            <form action="simpan.php" method="POST">

                <div class="mb-3">

                    <label class="form-label">

                        Nama Lengkap

                    </label>

                    <input type="text" name="nama_lengkap" class="form-control" maxlength="100" required>

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Username

                    </label>

                    <input type="text" name="username" class="form-control" maxlength="50" required>

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Password

                    </label>

                    <input type="password" name="password" class="form-control" minlength="6" required>

                    <small class="text-muted">

                        Password minimal 6 karakter.

                    </small>

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Level

                    </label>

                    <select name="level" class="form-select" required>

                        <option value="">-- Pilih Level --</option>

                        <option value="Administrator">

                            Administrator

                        </option>

                        <option value="Petugas">

                            Petugas

                        </option>

                        <option value="Peminjam">

                            Peminjam

                        </option>

                    </select>

                </div>

                <div class="mb-3">

                    <label class="form-label">

                        Status

                    </label>

                    <select name="status" class="form-select" required>

                        <option value="Aktif">

                            Aktif

                        </option>

                        <option value="Tidak Aktif">

                            Tidak Aktif

                        </option>

                    </select>

                </div>

                <hr>

                <button type="submit" class="btn btn-primary">

                    <i class="bi bi-save"></i>

                    Simpan

                </button>

                <a href="index.php" class="btn btn-secondary">

                    <i class="bi bi-x-circle"></i>

                    Batal

                </a>

            </form>

        </div>

    </div>

</div>

<?php
require_once '../../layouts/footer.php';
?>
