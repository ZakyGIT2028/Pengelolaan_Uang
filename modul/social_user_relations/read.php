<?php
include_once '../../config/db.php';


$sql = "SELECT 
            s.id_relasi, 
            s.status_relasi,
            u1.nama AS nama_pengguna1,
            u2.nama AS nama_pengguna2
        FROM social_user_relations s
        JOIN users u1 ON s.id_pengguna1 = u1.id_pengguna
        JOIN users u2 ON s.id_pengguna2 = u2.id_pengguna
        ORDER BY s.id_relasi DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Relasi Sosial</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Manajemen Relasi Sosial Pengguna 🤝</h1>
        <div class="nav-menu">
            <a href="../../index.php"> &lt;-- Dashboard</a>
            <a href="create.php" class="btn-success">➕ Buat Relasi Baru</a>
        </div>
        
        <?php 
        if (isset($_GET['message'])) {
            $msg = htmlspecialchars($_GET['message']);
            $class = (isset($_GET['status']) && $_GET['status'] == 'success') ? 'success' : 'error';
            echo "<div class='message $class'>$msg</div>";
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID Relasi</th>
                    <th>Pengguna 1 (Pengirim)</th>
                    <th>Pengguna 2 (Penerima)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id_relasi'] . "</td>";
                        echo "<td>" . $row['nama_pengguna1'] . "</td>";
                        echo "<td>" . $row['nama_pengguna2'] . "</td>";
                        echo "<td>" . $row['status_relasi'] . "</td>";
                        echo "<td>
                                <a href='update.php?id=" . $row['id_relasi'] . "' class='btn btn-primary'>Edit</a>
                                <a href='process.php?action=delete&id=" . $row['id_relasi'] . "' class='btn btn-danger' onclick=\"return confirm('Yakin ingin menghapus relasi ini?')\">Hapus</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada data relasi sosial.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</body>
</html>
<?php $conn->close(); ?>