<?php
/*
|--------------------------------------------------------------------------
| File        : simpan.php
| Folder      : administrator/peminjaman
| Fungsi      : Menyimpan Transaksi Peminjaman
|--------------------------------------------------------------------------
*/

require_once "../../config/session.php";
require_once "../../config/database.php";

/*
|--------------------------------------------------------------------------
| Hak Akses
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['level']) || $_SESSION['level'] != "Administrator") {
    header("Location: ../../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] != "POST") {

    header("Location: index.php");

    exit();
}

/*
|--------------------------------------------------------------------------
| Mengambil Data POST
|--------------------------------------------------------------------------
*/

$idUser           = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;
$tanggalPinjam    = trim($_POST['tanggal_pinjam'] ?? '');
$tanggalKembali   = trim($_POST['tanggal_kembali'] ?? '');

$idAlat           = $_POST['id_alat'] ?? [];
$jumlahPinjam     = $_POST['jumlah'] ?? [];

/*
|--------------------------------------------------------------------------
| Validasi Header Transaksi
|--------------------------------------------------------------------------
*/

if (
    $idUser <= 0 ||
    empty($tanggalPinjam) ||
    empty($tanggalKembali)
) {

    header("Location: create.php");

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Tanggal
|--------------------------------------------------------------------------
*/

if ($tanggalKembali < $tanggalPinjam) {

    echo "<script>

            alert('Tanggal kembali tidak boleh lebih kecil dari tanggal pinjam.');

            window.history.back();

          </script>";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi Detail Transaksi
|--------------------------------------------------------------------------
*/

if (
    !is_array($idAlat) ||
    !is_array($jumlahPinjam)
) {

    echo "<script>

            alert('Data detail peminjaman tidak valid.');

            window.history.back();

          </script>";

    exit();
}

if (count($idAlat) == 0) {

    echo "<script>

            alert('Belum ada alat yang dipilih.');

            window.history.back();

          </script>";

    exit();
}

if (count($idAlat) != count($jumlahPinjam)) {

    echo "<script>

            alert('Data transaksi tidak lengkap.');

            window.history.back();

          </script>";

    exit();
}

/*
|--------------------------------------------------------------------------
| Validasi User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT
    id_user
FROM users
WHERE id_user = ?
LIMIT 1
");

$stmt->bind_param("i", $idUser);

$stmt->execute();

$resultUser = $stmt->get_result();

if ($resultUser->num_rows == 0) {

    $stmt->close();

    echo "<script>

            alert('Peminjam tidak ditemukan.');

            window.history.back();

          </script>";

    exit();
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Memulai Database Transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    /*
    --------------------------------------------------------------
    Simpan Header Transaksi
    --------------------------------------------------------------
    */

    $status = "Menunggu";

    $stmt = $conn->prepare("
        INSERT INTO peminjaman
        (
            id_user,
            tanggal_pinjam,
            tanggal_kembali,
            status
        )
        VALUES
        (
            ?, ?, ?, ?
        )
    ");

    $stmt->bind_param(
        "isss",
        $idUser,
        $tanggalPinjam,
        $tanggalKembali,
        $status
    );

    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan data peminjaman."
        );
    }

    /*
    --------------------------------------------------------------
    ID Transaksi Baru
    --------------------------------------------------------------
    */

    $idPeminjaman = $conn->insert_id;

    $stmt->close();

    /*
    --------------------------------------------------------------
    Selanjutnya:
    - Validasi stok setiap alat
    - Simpan detail_peminjaman
    - Kurangi stok alat
    - Commit / Rollback
    --------------------------------------------------------------
    */
    /*
    --------------------------------------------------------------
    Simpan Detail Transaksi
    --------------------------------------------------------------
    */

    foreach ($idAlat as $index => $alat) {

        $alat = (int) $alat;

        $jumlah = (int) $jumlahPinjam[$index];

        if ($alat <= 0 || $jumlah <= 0) {

            throw new Exception(
                "Data alat tidak valid."
            );
        }

        /*
        ----------------------------------------------------------
        Cek stok terbaru
        ----------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT
                stok
            FROM alat
            WHERE id_alat = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "i",
            $alat
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 0) {

            throw new Exception(
                "Data alat tidak ditemukan."
            );
        }

        $dataAlat = $result->fetch_assoc();

        $stokSekarang = (int) $dataAlat['stok'];

        $stmt->close();

        /*
        ----------------------------------------------------------
        Validasi stok
        ----------------------------------------------------------
        */

        if ($jumlah > $stokSekarang) {

            throw new Exception(
                "Stok alat tidak mencukupi."
            );
        }

        /*
        ----------------------------------------------------------
        Simpan detail transaksi
        ----------------------------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO detail_peminjaman
            (
                id_peminjaman,
                id_alat,
                jumlah
            )
            VALUES
            (
                ?, ?, ?
            )
        ");

        $stmt->bind_param(
            "iii",
            $idPeminjaman,
            $alat,
            $jumlah
        );

        if (!$stmt->execute()) {

            throw new Exception(
                "Gagal menyimpan detail transaksi."
            );
        }

        $stmt->close();

        /*
        ----------------------------------------------------------
        Update stok alat
        ----------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE alat
            SET
                stok = stok - ?
            WHERE
                id_alat = ?
        ");

        $stmt->bind_param(
            "ii",
            $jumlah,
            $alat
        );

        if (!$stmt->execute()) {

            throw new Exception(
                "Gagal mengurangi stok alat."
            );
        }

        $stmt->close();
    }

    /*
    --------------------------------------------------------------
    Semua proses berhasil
    --------------------------------------------------------------
    */

    $conn->commit();

    header("Location: index.php?pesan=sukses");

    exit();
} catch (Exception $e) {

    /*
    --------------------------------------------------------------
    Batalkan seluruh transaksi
    --------------------------------------------------------------
    */

    $conn->rollback();

    echo "<script>

        alert('" . $e->getMessage() . "');

        window.location='create.php';

    </script>";
}

$conn->close();
