<?php
/*
|--------------------------------------------------------------------------
| File        : login.php
| Folder      : auth
| Fungsi      : Menampilkan halaman login
|--------------------------------------------------------------------------
*/

// Memulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, arahkan sesuai level pengguna
if (isset($_SESSION['login'])) {
    switch ($_SESSION['level']) {
        case 'Administrator':
            header('Location: ../administrator/dashboard.php');
            break;

        case 'Petugas':
            header('Location: ../petugas/dashboard.php');
            break;

        case 'Peminjam':
            header('Location: ../peminjam/dashboard.php');
            break;

        default:
            session_destroy();
            header('Location: login.php');
            break;
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Aplikasi Peminjaman Alat</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Aplikasi Peminjaman Alat</h3>
                    </div>
                    <div class="card-body">
                        <h5 class="text-center mb-4">
                            Login Sistem
                        </h5>
                        <?php
                        if (isset($_SESSION['pesan'])) {
                        ?>

                            <div class="alert alert-danger">

                                <?= $_SESSION['pesan'] ?>

                            </div>

                            <?php
                            unset($_SESSION['pesan']);
                        }
                        ?>
                        <form action="proses_login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">
                                    Username
                                </label>
                                <input type="text" name="username" class="form-control" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    Password
                                </label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>
                            UKK Rekayasa Perangkat Lunak 2025/2026
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
