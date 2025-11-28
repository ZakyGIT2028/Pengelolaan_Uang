<?php
include_once '../../config/db.php';


$users_result1 = $conn->query("SELECT id_pengguna, nama FROM users ORDER BY nama ASC");
$users_result2 = $conn->query("SELECT id_pengguna, nama FROM users ORDER BY nama ASC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Relasi Sosial</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Buat Relasi Sosial Baru ✏️</h1>
        <div class="nav-menu">
            <a href="read.php" class="btn-primary"> &lt;-- Kembali ke Daftar Relasi</a>
        </div>

        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label for="id_pengguna1">Pengguna 1 (Pengirim):</label>
                <select name="id_pengguna1" id="id_pengguna1" required>
                    <?php while ($row = $users_result1->fetch_assoc()): ?>
                        <option value="<?= $row['id_pengguna'] ?>"><?= $row['nama'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_pengguna2">Pengguna 2 (Penerima):</label>
                <select name="id_pengguna2" id="id_pengguna2" required>
                    <?php while ($row = $users_result2->fetch_assoc()): ?>
                        <option value="<?= $row['id_pengguna'] ?>"><?= $row['nama'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status_relasi">Status Relasi:</label>
                <select name="status_relasi" id="status_relasi" required>
                    <option value="Pending">Pending</option>
                    <option value="Accepted">Accepted</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success">Simpan Relasi</button>
        </form>

    </div>
</body>
</html>
<?php $conn->close(); ?>