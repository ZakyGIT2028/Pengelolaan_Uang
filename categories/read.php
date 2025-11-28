<?php
include_once '../../config/db.php';


$sql = "SELECT * FROM categories ORDER BY nama_kategori ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kategori</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Manajemen Kategori 🏷️</h1>
        <div class="nav-menu">
            <a href="../../index.php"> &lt;-- Dashboard</a>
            <a href="create.php" class="btn-success">➕ Tambah Kategori Baru</a>
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
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Tipe Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id_kategori'] . "</td>";
                        echo "<td>" . $row['nama_kategori'] . "</td>";
                        echo "<td>" . $row['tipe_kategori'] . "</td>";
                        echo "<td>
                                <a href='update.php?id=" . $row['id_kategori'] . "' class='btn btn-primary'>Edit</a>
                                <a href='process.php?action=delete&id=" . $row['id_kategori'] . "' class='btn btn-danger' onclick=\"return confirm('Yakin ingin menghapus kategori ini? Semua pengeluaran terkait mungkin terpengaruh.')\">Hapus</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Tidak ada data kategori.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</body>
</html>
<?php $conn->close(); ?>