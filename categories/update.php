<?php
include_once '../../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header('Location: read.php?message=ID Kategori tidak valid&status=error');
    exit();
}


$stmt = $conn->prepare("SELECT * FROM categories WHERE id_kategori = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: read.php?message=Kategori tidak ditemukan&status=error');
    exit();
}

$category = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Kategori: <?= htmlspecialchars($category['nama_kategori']) ?></h1>
        <div class="nav-menu">
            <a href="read.php" class="btn-primary"> &lt;-- Kembali ke Daftar</a>
        </div>

        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_kategori" value="<?= $category['id_kategori'] ?>">

            <div class="form-group">
                <label for="nama_kategori">Nama Kategori:</label>
                <input type="text" name="nama_kategori" id="nama_kategori" 
                       value="<?= htmlspecialchars($category['nama_kategori']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tipe_kategori">Tipe Kategori:</label>
                <select name="tipe_kategori" id="tipe_kategori" required>
                    <option value="Manual" <?= ($category['tipe_kategori'] == 'Manual') ? 'selected' : '' ?>>Manual</option>
                    <option value="Dinamis" <?= ($category['tipe_kategori'] == 'Dinamis') ? 'selected' : '' ?>>Dinamis</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>

    </div>
</body>
</html>
<?php $conn->close(); ?>