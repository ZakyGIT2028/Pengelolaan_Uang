<?php






?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pengguna</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin: 50px auto;">
        <h1>Registrasi Pengguna Baru ✏️</h1>
        <div class="nav-menu">
            <a href="read.php" class="btn-primary"> &lt;-- Kembali ke Daftar</a> 
        </div>

        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label for="nama">Nama Lengkap:</label>
                <input type="text" name="nama" id="nama" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email (UNIQUE):</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="kata_sandi">Kata Sandi:</label>
                <input type="password" name="kata_sandi" id="kata_sandi" required>
            </div>
            
            <div class="form-group">
                <label for="tanggal_daftar">Tanggal Daftar:</label>
                <input type="date" name="tanggal_daftar" id="tanggal_daftar" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="status_akun">Status Akun:</label>
                <select name="status_akun" id="status_akun" required>
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="user">User</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success">Daftarkan Pengguna</button>
        </form>

    </div>
</body>
</html>