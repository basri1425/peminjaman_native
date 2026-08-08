<?php
/*
|--------------------------------------------------------------------------
| File        : detail.php
| Folder      : administrator/user
| Fungsi      : Menampilkan Detail Data User
|--------------------------------------------------------------------------
*/

require_once '../../config/session.php';
require_once '../../config/database.php';

if ($_SESSION['level'] != 'Administrator') {
    header('Location: ../../auth/login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id_user = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Mengambil Data User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id_user,
        nama_lengkap,
        username,
        level,
        status,
        created_at,
        updated_at
    FROM users
    WHERE id_user = ?
");

$stmt->bind_param('i', $id_user);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit();
}

$user = $result->fetch_assoc();

$title = 'Detail User';

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>
                <i class="bi bi-person-vcard-fill"></i>
                Detail User
            </h3>
            <p class="text-muted mb-0">
                Informasi lengkap data pengguna.
            </p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Informasi User
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="250">ID User</th>
                    <td><?= htmlspecialchars($user['id_user']) ?></td>
                </tr>
                <tr>
                    <th>Nama Lengkap</th>
                    <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                </tr>
                <tr>
                    <th>Level</th>
                    <td>
                        <?php                     
                        switch ($user['level']) {
                            case 'Administrator':
                                echo '<span class="badge bg-danger">Administrator</span>';
                                break;                        
                            case 'Petugas':
                                echo '<span class="badge bg-primary">Petugas</span>';
                                break;                        
                            default:
                                echo '<span class="badge bg-success">Peminjam</span>';
                        }                       
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <?php if ($user['status'] == "Aktif") { ?>
                        <span class="badge bg-success">
                            Aktif
                        </span>
                        <?php } else { ?>
                        <span class="badge bg-secondary">
                            Tidak Aktif
                        </span>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th>Dibuat Pada</th>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                </tr>
                <tr>
                    <th>Terakhir Diubah</th>
                    <td><?= htmlspecialchars($user['updated_at']) ?></td>
                </tr>
            </table>
        </div>
        <div class="card-footer">
            <a href="edit.php?id=<?= $user['id_user'] ?>" class="btn btn-warning">
                <i class="bi bi-pencil-square"></i>
                Edit
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>
</div>
<?php

$stmt->close();
$conn->close();

require_once '../../layouts/footer.php';

?>
