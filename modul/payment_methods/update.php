<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: ../../login.php?message=Silakan login terlebih dahulu&status=error');
    exit();
}
include_once '../../config/db.php';
$current_user_id = $_SESSION['id_pengguna'];
$current_user_name = $_SESSION['nama_pengguna'];
$current_user_role = $_SESSION['role'] ?? 'user';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: read.php?message=ID Metode tidak valid&status=error');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM payment_methods WHERE id_metode = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: read.php?message=Metode tidak ditemukan&status=error');
    exit();
}
$method = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Metode Pembayaran - FinSet</title>
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        :root {
            --sidebar-bg: #111827;
            --sidebar-text: #e5e7eb;
            --sidebar-text-hover: #ffffff;
            --sidebar-active-bg: var(--primary-color);
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            --border-color: #e9ecef;
        }

        body {
            margin: 0;
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--secondary-color);
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding: 25px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            box-sizing: border-box;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-profile {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #374151;
        }

        .sidebar-profile .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0 auto 15px auto;
        }

        .sidebar-profile h3 {
            margin: 0;
            color: var(--white);
            font-weight: 600;
        }

        .sidebar-nav {
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-nav h2 {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #9ca3af;
            font-weight: 500;
            margin-top: 20px;
            padding-left: 15px;
        }

        .sidebar-nav a {
            display: block;
            color: var(--sidebar-text);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: background-color 0.2s, color 0.2s;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: #374151;
            color: var(--sidebar-text-hover);
        }

        .sidebar-nav a.active-dashboard {
            background-color: var(--primary-color);
            color: var(--sidebar-text-hover);
        }

        .sidebar-footer {
            padding-top: 20px;
            border-top: 1px solid #374151;
            margin-top: auto;
        }

        .sidebar-footer a {
            display: block;
            text-align: center;
        }

        .main-content {
            flex-grow: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .widget-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            padding: 25px;
            overflow: hidden;
        }

        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        button[type="submit"] {
            padding: 12px 25px;
            font-size: 1rem;
        }
    </style>
</head>

<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-profile">
                <div class="avatar"><?= htmlspecialchars(strtoupper(substr($current_user_name, 0, 1))) ?></div>
                <h3><?= htmlspecialchars($current_user_name) ?></h3>
            </div>
            <nav class="sidebar-nav">
                <h2>Menu Utama</h2>
                <a href="../../index.php">Dashboard</a>
                <a href="../incomes/read.php">Pemasukan</a>
                <a href="../expenses/read.php">Pengeluaran</a>
                <h2>Perencanaan</h2>
                <a href="../budgets/read.php">Anggaran (Budgets)</a>
                <a href="../financial_goals/read.php">Tujuan Keuangan (Goals)</a>
                <h2>Aset & Kewajiban</h2>
                <a href="read.php" class="active">Metode Bayar (Dompet)</a> <a href="../debts/read.php">Utang (Debts)</a>
                <a href="../receivables/read.php">Piutang (Receivables)</a>
                <h2>Fitur Lain</h2>
                <a href="../social_user_relations/read.php">Relasi Sosial</a>
                <a href="../financial_predictions/read.php">Prediksi Keuangan</a>
                <h2>Pengaturan Akun</h2>
                <a href="../users/update.php?id=<?= $current_user_id ?>">Edit Profil Saya</a>
                <a href="../categories/read.php">Kelola Kategori Saya</a>
                <?php if ($current_user_role == 'admin'): ?>
                    <h2 style="color: var(--danger-color);">Area Admin</h2>
                    <a href="../users/read.php">Kelola Semua Pengguna</a>
                    <a href="../currencies/read.php">Kelola Mata Uang</a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <a href="../../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <div class="widget-card">
                <h1>Edit Metode Pembayaran ✏️</h1>
                <p style="margin-top: -20px; margin-bottom: 20px;"><a href="read.php" class="btn btn-primary"> &lt;-- Kembali ke Daftar</a></p>

                <form action="process.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_metode" value="<?= $method['id_metode'] ?>">

                    <div class="form-group">
                        <label for="nama_metode">Nama Metode / Dompet:</label>
                        <input type="text" name="nama_metode" id="nama_metode" value="<?= htmlspecialchars($method['nama_metode']) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>
<?php $conn->close(); ?>