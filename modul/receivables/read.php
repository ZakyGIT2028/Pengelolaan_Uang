<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: ../../login.php?message=Silakan login terlebih dahulu&status=error');
    exit();
}


include_once '../../config/db.php';
if (!$conn || $conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}
$current_user_id = $_SESSION['id_pengguna'];
$current_user_name = $_SESSION['nama_pengguna'];
$current_user_role = $_SESSION['role'] ?? 'user';


$sql = $conn->prepare("SELECT * FROM receivables WHERE id_pengguna = ? ORDER BY status_piutang ASC, tanggal_tenggat ASC");
$sql->bind_param("i", $current_user_id);
$sql->execute();
$result = $sql->get_result();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Piutang - FinSet</title>
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

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .content-header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--secondary-color);
        }

        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table thead th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 12px 15px;
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
        }

        table tbody td {
            vertical-align: middle;
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .action-link {
            font-size: 0.9em;
            padding: 6px 10px;
        }

        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
            color: white;
            display: inline-block;
            white-space: nowrap;
        }

        .status-Belum-Diterima {
            background-color: var(--danger-color);
        }

        
        .status-Diterima-Sebagian {
            background-color: #ffc107;
            color: #333;
        }

        
        .status-Lunas {
            background-color: var(--success-color);
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
                <a href="../payment_methods/read.php">Metode Bayar (Dompet)</a>
                <a href="../debts/read.php">Utang (Debts)</a>
                <a href="read.php" class="active">Piutang (Receivables)</a>
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

                <div class="content-header">
                    <h1>Daftar Piutang Anda 📈</h1>
                    <a href="create.php" class="btn btn-success">➕ Catat Piutang Baru</a>
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
                            <th>Tanggal Tenggat</th>
                            <th>Jumlah Piutang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                
                                $status_class = 'status-' . str_replace(' ', '-', $row['status_piutang']);

                                echo "<tr>";
                                echo "<td>" . date('d M Y', strtotime($row['tanggal_tenggat'])) . "</td>";
                                echo "<td>Rp " . number_format($row['jumlah_piutang'], 0, ',', '.') . "</td>";
                                echo "<td><span class='status-badge " . $status_class . "'>" . htmlspecialchars($row['status_piutang']) . "</span></td>";
                                echo "<td>
                                        <a href='update.php?id=" . $row['id_piutang'] . "' class='btn btn-primary action-link'>Edit</a>
                                        <a href='process.php?action=delete&id=" . $row['id_piutang'] . "' class='btn btn-danger action-link' onclick=\"return confirm('Yakin ingin menghapus data piutang ini?')\">Hapus</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; padding: 20px;'>Tidak ada catatan piutang.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </main>
    </div>
</body>

</html>
<?php
if (isset($sql)) $sql->close();
$conn->close();
?>