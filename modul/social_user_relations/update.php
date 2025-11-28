<?php
include_once '../../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header('Location: read.php?message=ID Relasi tidak valid&status=error');
    exit();
}


$stmt = $conn->prepare("SELECT * FROM social_user_relations WHERE id_relasi = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result_relasi = $stmt->get_result();

if ($result_relasi->num_rows === 0) {
    header('Location: read.php?message=Relasi tidak ditemukan&status=error');
    exit();
}
$relasi = $result_relasi->fetch_assoc();
$stmt->close();


$users_result1 = $conn->query("SELECT id_pengguna, nama FROM users ORDER BY nama ASC");
$users_result2 = $conn->query("SELECT id_pengguna, nama FROM users ORDER BY nama ASC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Relasi Sosial</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Relasi Sosial (ID: <?= $relasi['id_relasi'] ?>) ✏️</h1>
        <div class="nav-menu">
            <a href="read.php" class="btn-primary"> &lt;-- Kembali ke Daftar Relasi</a>
        </div>

        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_relasi" value="<?= $relasi['id_relasi'] ?>">

            <div class="form-group">
                <label for="id_pengguna1">Pengguna 1 (Pengirim):</label>
                <select name="id_pengguna1" id="id_pengguna1" required>
                    <?php while ($row = $users_result1->fetch_assoc()): ?>
                        <option value="<?= $row['id_pengguna'] ?>" <?= ($relasi['id_pengguna1'] == $row['id_pengguna']) ? 'selected' : '' ?>>
                            <?= $row['nama'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_pengguna2">Pengguna 2 (Penerima):</label>
                <select name="id_pengguna2" id="id_pengguna2" required>
                    <?php while ($row = $users_result2->fetch_assoc()): ?>
                        <option value="<?= $row['id_pengguna'] ?>" <?= ($relasi['id_pengguna2'] == $row['id_pengguna']) ? 'selected' : '' ?>>
                            <?= $row['nama'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status_relasi">Status Relasi:</label>
                <select name="status_relasi" id="status_relasi" required>
                    <option value="Pending" <?= ($relasi['status_relasi'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Accepted" <?= ($relasi['status_relasi'] == 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>

    </div>
</body>
</html>
<?php $conn->close(); ?>