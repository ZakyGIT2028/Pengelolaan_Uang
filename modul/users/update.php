<?php
session_start();

include_once '../../config/db.php';

$id = $_GET['id'] ?? null;

if (!isset($_SESSION['id_pengguna']) || ($_SESSION['role'] != 'admin' && $_SESSION['id_pengguna'] != $id)) {
     header('Location: ../../index.php?message=Akses Ditolak&status=error');
     exit();
}

$stmt = $conn->prepare("SELECT id_pengguna, nama, email, tanggal_daftar, status_akun, role FROM users WHERE id_pengguna = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengguna</title>
    <link rel="stylesheet" href="../../assets/style.css">
    </head>
<body>
    <div class="container" style="max-width: 600px; margin: 50px auto;">
        <h1>Edit Pengguna: <?= htmlspecialchars($user['nama']) ?></h1>
        <div class="nav-menu">
            <a href="read.php" class="btn-primary"> &lt;-- Kembali ke Daftar</a>
        </div>

        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_pengguna" value="<?= $user['id_pengguna'] ?>">

            <div class="form-group">
                <label for="nama">Nama Lengkap:</label>
                <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email (UNIQUE):</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="new_kata_sandi">Kata Sandi Baru (Kosongkan jika tidak diubah):</label>
                <input type="password" name="new_kata_sandi" id="new_kata_sandi">
            </div>
            <div class="form-group">
                <label for="tanggal_daftar">Tanggal Daftar:</label>
                <input type="date" name="tanggal_daftar" id="tanggal_daftar" value="<?= htmlspecialchars($user['tanggal_daftar']) ?>" required>
            </div>
            <div class="form-group">
                <label for="status_akun">Status Akun:</label>
                <select name="status_akun" id="status_akun" required>
                    <option value="Aktif" <?= ($user['status_akun'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="Tidak Aktif" <?= ($user['status_akun'] == 'Tidak Aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="user" <?= ($user['role'] == 'user') ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>
<?php $stmt->close(); $conn->close(); ?>