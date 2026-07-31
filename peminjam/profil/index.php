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
    username,
    level,
    status

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

    header("Location: ../dashboard/index.php");
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

<i class="fas fa-user-circle"></i>

Profil Saya

</h1>

<p class="text-muted">

Informasi akun dan identitas pengguna yang sedang login.

</p>

</div>

</div>

</div>

</section>

<section class="content">

<div class="container-fluid">
    <!-- Notifikasi -->

<?php if (isset($_SESSION['success'])) { ?>

    <div class="alert alert-success alert-dismissible fade show">

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert"></button>

        <?= htmlspecialchars($_SESSION['success']); ?>

    </div>

<?php unset($_SESSION['success']); } ?>

<?php if (isset($_SESSION['error'])) { ?>

    <div class="alert alert-danger alert-dismissible fade show">

        <button type="button"
            class="btn-close"
            data-bs-dismiss="alert"></button>

        <?= htmlspecialchars($_SESSION['error']); ?>

    </div>

<?php unset($_SESSION['error']); } ?>

<div class="row">

    <!-- Foto Profil -->

    <div class="col-md-4">

        <div class="card card-primary card-outline">

            <div class="card-body box-profile text-center">

                <?php

                $foto = "../../assets/img/default-user.png";

                if (!empty($user['foto']) && file_exists("../../uploads/profil/" . $user['foto'])) {

                    $foto = "../../uploads/profil/" . $user['foto'];

                }

                ?>


                <h3 class="profile-username">

                    <?= htmlspecialchars($user['nama_lengkap']); ?>

                </h3>

                <p class="text-muted">

                    <?= htmlspecialchars($user['username']); ?>

                </p>

                <p>

                    <span class="badge bg-primary">

                        <?= htmlspecialchars($user['level']); ?>

                    </span>

                    <?php

                    $statusClass = ($user['status'] == 'Aktif')
                        ? 'success'
                        : 'danger';

                    ?>

                    <span class="badge bg-<?= $statusClass; ?>">

                        <?= htmlspecialchars($user['status']); ?>

                    </span>

                </p>

            </div>

        </div>

    </div>

    <!-- Informasi Profil -->

    <div class="col-md-8">

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-id-card"></i>

                    Informasi Akun

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <th width="25%">Nama Lengkap</th>

                        <td width="3%">:</td>

                        <td>

                            <?= htmlspecialchars($user['nama_lengkap']); ?>

                        </td>

                    </tr>

                    <tr>

                        <th>Username</th>

                        <td>:</td>

                        <td>

                            <?= htmlspecialchars($user['username']); ?>

                        </td>

                    </tr>


                    <tr>

                        <th>Level</th>

                        <td>:</td>

                        <td>

                            <span class="badge bg-primary">

                                <?= htmlspecialchars($user['level']); ?>

                            </span>

                        </td>

                    </tr>

                    <tr>

                        <th>Status Akun</th>

                        <td>:</td>

                        <td>

                            <span class="badge bg-<?= $statusClass; ?>">

                                <?= htmlspecialchars($user['status']); ?>

                            </span>

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>
<!-- Tombol Aksi -->

<div class="card">

    <div class="card-body text-end">

        <a href="edit.php" class="btn btn-warning">

            <i class="fas fa-user-edit"></i>

            Edit Profil

        </a>

        <a href="ubah_password.php" class="btn btn-danger">

            <i class="fas fa-key"></i>

            Ubah Password

        </a>

    </div>

</div>

</div>

</section>

</div>

<?php

$conn->close();

?>

<?php include '../../layouts/footer.php'; ?>

