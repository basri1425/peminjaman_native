<?php
/*
|--------------------------------------------------------------------------
| File        : index.php
| Folder      : administrator/user
| Fungsi      : Menampilkan Data User
|--------------------------------------------------------------------------
*/

require_once '../../config/session.php';
require_once '../../config/database.php';

if ($_SESSION['level'] != 'Administrator') {
    header('Location: ../../auth/login.php');
    exit();
}

$title = 'Master User';

require_once '../../layouts/header.php';
require_once '../../layouts/navbar.php';
require_once '../../layouts/sidebar.php';

/*
|--------------------------------------------------------------------------
| Mengambil Data User
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
    ORDER BY nama_lengkap ASC
";

$result = $conn->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>
                <i class="bi bi-people-fill"></i>
                Master User
            </h3>
            <p class="text-muted mb-0">
                Kelola seluruh data pengguna aplikasi.
            </p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i>
            Tambah User
        </a>
    </div>
    <?php if(isset($_GET['pesan'])) : ?>
    <?php if($_GET['pesan']=="sukses") : ?>
    <div class="alert alert-success">
        Data user berhasil disimpan.
    </div>
    <?php elseif($_GET['pesan']=="update") : ?>
    <div class="alert alert-success">
        Data user berhasil diperbarui.
    </div>
    <?php elseif($_GET['pesan']=="hapus") : ?>
    <div class="alert alert-success">
        Data user berhasil dihapus.
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Daftar User
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th width="60">No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th width="140">Level</th>
                            <th width="120">Status</th>
                            <th width="220">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    if($result->num_rows > 0){

                        $no = 1;

                        while($row = $result->fetch_assoc()){
                    ?>
                        <tr>
                            <td class="text-center">
                                <?= $no++ ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['nama_lengkap']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['username']) ?>
                            </td>
                            <td class="text-center">
                                <?php                             
                                switch ($row['level']) {
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
                            <td class="text-center">
                                <?php if($row['status']=="Aktif"){ ?>
                                <span class="badge bg-success">
                                    Aktif
                                </span>
                                <?php }else{ ?>
                                <span class="badge bg-secondary">
                                    Tidak Aktif
                                </span>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <a href="detail.php?id=<?= $row['id_user'] ?>" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?= $row['id_user'] ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="hapus.php?id=<?= $row['id_user'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus user ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                        }
                    }else{
                    ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                Belum ada data user.
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../../layouts/footer.php';
?>
