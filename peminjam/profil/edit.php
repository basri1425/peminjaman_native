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

$idUser = $_SESSION['id_user'];

/*
|--------------------------------------------------------------------------
| Data Profil
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    id_user,
    nama_lengkap,
    username


FROM users

WHERE id_user = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $idUser);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    $_SESSION['error'] = "Data pengguna tidak ditemukan.";

    header("Location: index.php");
    exit();
}

$user = $result->fetch_assoc();

$stmt->close();

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

                        <i class="fas fa-user-edit"></i>

                        Edit Profil

                    </h1>

                    <p class="text-muted">

                        Perbarui informasi profil akun Anda.

                    </p>

                </div>

            </div>

        </div>

    </section>

    <section class="content">

        <div class="container-fluid">
            <div class="row">

                <div class="col-md-4">

                    <div class="card card-primary card-outline">

                        <div class="card-body box-profile text-center">

                            <h3 class="profile-username">

                                <?= htmlspecialchars($user['nama_lengkap']); ?>

                            </h3>

                            <p class="text-muted">

                                @<?= htmlspecialchars($user['username']); ?>

                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-md-8">

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i class="fas fa-user-edit"></i>

                                Form Edit Profil

                            </h3>

                        </div>

                        <form
                            action="update.php"
                            method="POST"
                            enctype="multipart/form-data">

                            <div class="card-body">

                                <div class="mb-3">

                                    <label class="form-label">

                                        Nama Lengkap

                                    </label>

                                    <input
                                        type="text"
                                        name="nama_lengkap"
                                        class="form-control"
                                        maxlength="100"
                                        required
                                        value="<?= htmlspecialchars($user['nama_lengkap']); ?>">

                                </div>

                                <div class="mb-3">

                                    <label class="form-label">

                                        Username

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        value="<?= htmlspecialchars($user['username']); ?>"
                                        readonly>

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
                                    class="btn btn-primary">

                                    <i class="fas fa-save"></i>

                                    Simpan Perubahan

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
