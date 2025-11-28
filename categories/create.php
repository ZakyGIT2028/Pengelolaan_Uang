<?php

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Kategori Baru ✏️</h1>
        <div class="nav-menu">
            <a href="read.php" class="btn-primary"> &lt;-- Kembali ke Daftar</a>
        </div>

        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label for="nama_kategori">Nama Kategori:</label>
                <input type="text" name="nama_kategori" id="nama_kategori" required>
            </div>
            
            <div class="form-group">
                <label for="tipe_kategori">Tipe Kategori:</label>
                <select name="tipe_kategori" id="tipe_kategori" required>
                    <option value="Manual">Manual</option>
                    <option value="Dinamis">Dinamis</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success">Simpan Kategori</button>
        </form>

    </div>
</body>
</html>