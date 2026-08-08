<?php
/*
|--------------------------------------------------------------------------
| File        : create.php
| Folder      : administrator/kategori
| Fungsi      : Form Tambah Data Kategori
|--------------------------------------------------------------------------
*/

require_once '../../config/session.php';
require_once '../../config/database.php';

if ($_SESSION['level'] != 'Administrator') {
    header('Location: ../../auth/login.php');
    exit();
}

$title = 'Tambah Kategori';

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>
                <i class="bi bi-tags-fill"></i>
                Tambah Kategori
            </h3>
            <p class="text-muted mb-0">
                Form untuk menambahkan kategori alat.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Form Data Kategori
        </div>
        <div class="card-body">
            <form action="simpan.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">
                        Nama Kategori <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nama_kategori" class="form-control" maxlength="100"
                        placeholder="Masukkan nama kategori" autofocus required>
                    <small class="text-muted">
                        Contoh: Laptop, Proyektor, Kamera, Printer.
                    </small>
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
