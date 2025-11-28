<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    
    header('Location: ../../index.php?message=Akses Ditolak&status=error');
    exit();
}

include_once '../../config/db.php';



$sql = "SELECT id_pengguna, nama, email, tanggal_daftar, status_akun, role FROM users ORDER BY tanggal_daftar DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pengguna (Admin)</title>
    <link rel="stylesheet" href="../../assets/style.css">
    </head>
<body>
    <div class="container" style="max-width: 1000px; margin: 50px auto;">
        <h1>Manajemen Pengguna (Admin) 👤</h1>
        <div class="nav-menu">
            <a href="../../index.php"> &lt;-- Dashboard</a>
            <a href="create.php" class="btn-success">➕ Tambah Pengguna Baru</a>
        </div>
        
        <?php 
        if (isset($_GET['message'])) {
            
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Tgl Daftar</th>
                    <th>Status</th>
                    <th>Role</th> <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id_pengguna'] . "</td>";
                        echo "<td>" . $row['nama'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['tanggal_daftar'] . "</td>";
                        echo "<td>" . $row['status_akun'] . "</td>";
                        echo "<td>" . $row['role'] . "</td>"; 
                        echo "<td>
                                <a href='update.php?id=" . $row['id_pengguna'] . "' class='btn btn-primary'>Edit</a>
                                <a href='process.php?action=delete&id=" . $row['id_pengguna'] . "' class='btn btn-danger' onclick=\"return confirm('Yakin ingin menghapus pengguna ini?')\">Hapus</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Tidak ada data pengguna.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</body>
</html>
<?php $conn->close(); ?>