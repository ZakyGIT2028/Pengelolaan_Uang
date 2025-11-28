<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: ../../login.php');
    exit();
}


include_once '../../config/db.php';
$current_user_id = $_SESSION['id_pengguna'];
$current_user_name = $_SESSION['nama_pengguna'];
$current_user_role = $_SESSION['role'] ?? 'user';


$methods_result = $conn->query("SELECT id_metode, nama_metode FROM payment_methods ORDER BY nama_metode ASC");
if (!$methods_result) die("Query Metode Gagal: " . $conn->error);

$currencies_result = $conn->query("SELECT id_mata_uang, kode_mata_uang, nama_mata_uang FROM currencies ORDER BY kode_mata_uang ASC");
if (!$currencies_result) die("Query Mata Uang Gagal: " . $conn->error);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pemasukan - FinSet</title>
    <link rel="stylesheet" href="../../assets/style.css">

    <style>
         :root {
            --sidebar-bg: #111827; 
            --sidebar-text: #e5e7eb;
            --sidebar-text-hover: #ffffff;
            --sidebar-active-bg: var(--primary-color);
        }

        body {
            margin: 0;
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6;
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
            box-shadow: 2px 0 5px rgba(0,0,0,0.1); 
        }

        .sidebar-profile {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #374151;
        }
        .sidebar-profile .avatar {
            width: 80px; height: 80px; border-radius: 50%; background-color: var(--primary-color);
            color: white; display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: 600; margin: 0 auto 15px auto;
        }
        .sidebar-profile h3 { margin: 0; color: var(--white); font-weight: 600; }

        
        .sidebar-nav { flex-grow: 1; overflow-y: auto; }
        .sidebar-nav h2 {
            font-size: 0.8rem; text-transform: uppercase; color: #9ca3af;
            font-weight: 500; margin-top: 20px; padding-left: 15px; 
        }
        .sidebar-nav a {
            display: block; color: var(--sidebar-text); text-decoration: none;
            padding: 12px 15px; border-radius: 8px; margin-bottom: 5px;
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

        .sidebar-footer { padding-top: 20px; border-top: 1px solid #374151; margin-top: auto; }
        .sidebar-footer a { display: block; text-align: center; }

        
        .main-content { flex-grow: 1; margin-left: 260px; padding: 30px; }

        .main-header { margin-bottom: 30px; }
        .main-header h1 { margin: 0; font-size: 2rem; font-weight: 700; color: var(--secondary-color); border: none; }
        .main-header p { font-size: 1.1rem; color: #6c757d; }

        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: var(--white); padding: 20px; border-radius: 12px; box-shadow: var(--box-shadow); }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 1rem; color: #6c757d; font-weight: 500; }
        .stat-card .amount { font-size: 1.8rem; font-weight: 700; color: var(--secondary-color); }
        .stat-card .amount.income { color: var(--success-color); }
        .stat-card .amount.expense { color: var(--danger-color); }

        
         .widget-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            padding: 25px;
        }
        .widget-card h1, .widget-card h2 {
             margin-top: 0;
             margin-bottom: 20px;
             border-bottom: 1px solid var(--border-color);
             padding-bottom: 15px;
             font-size: 1.5rem;
             color: var(--secondary-color); 
        }

        
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;}
        .content-header h1 { margin: 0; border: none; padding: 0; }

        
        table {
            border: none; 
            border-radius: 0; 
            box-shadow: none; 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 0; 
        }
        table th { background-color: #f8f9fa;  text-align: left; padding: 12px 15px; border-bottom: 2px solid var(--border-color); }
        table td { vertical-align: middle; padding: 12px 15px; border-bottom: 1px solid var(--border-color); }
        table tbody tr:last-child td { border-bottom: none; } 
        table tbody tr:hover { background-color: #f1f1f1; } 

        
        .form-group label { color: #495057; display: block; margin-bottom: 8px; font-weight: 500;}
        button[type="submit"] { padding: 12px 25px; font-size: 1rem; } 

        
        .action-link { font-size: 0.9em; padding: 6px 10px;}
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
                <a href="read.php" class="active">Pemasukan</a> <a href="../expenses/read.php">Pengeluaran</a>
                <h2>Perencanaan</h2>
                <a href="../budgets/read.php">Anggaran (Budgets)</a>
                <a href="../financial_goals/read.php">Tujuan Keuangan (Goals)</a>
                <h2>Aset & Kewajiban</h2>
                <a href="../payment_methods/read.php">Metode Bayar (Dompet)</a>
                <a href="../debts/read.php">Utang (Debts)</a>
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

                <h1>Tambah Pemasukan Baru ✏️</h1>
                <p style="margin-top: -20px; margin-bottom: 20px;"><a href="read.php" class="btn btn-primary"> &lt;-- Kembali ke Daftar Pemasukan</a></p>

                <form action="process.php" method="POST">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="id_pengguna" value="<?= $current_user_id ?>">

                    <div class="form-group">
                        <label for="tanggal">Tanggal:</label>
                        <input type="date" name="tanggal" id="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>

                     <div class="form-group">
                        <label for="sumber">Sumber Pemasukan (cth: Gaji, Bonus):</label>
                        <input type="text" name="sumber" id="sumber" required>
                    </div>

                    <div class="form-group">
                        <label for="jumlah">Jumlah:</label>
                        <input type="number" name="jumlah" id="jumlah" step="1" required>
                    </div>

                    <div class="form-group">
                        <label for="id_metode">Metode Pembayaran:</label>
                        <select name="id_metode" id="id_metode" required>
                            <option value="">-- Pilih Metode --</option>
                            <?php if ($methods_result->num_rows > 0): ?>
                                <?php while ($row = $methods_result->fetch_assoc()): ?>
                                    <option value="<?= $row['id_metode'] ?>"><?= htmlspecialchars($row['nama_metode']) ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                 <option value="" disabled>Belum ada metode</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_mata_uang">Mata Uang:</label>
                        <select name="id_mata_uang" id="id_mata_uang" required>
                            <option value="">-- Pilih Mata Uang --</option>
                             <?php if ($currencies_result->num_rows > 0): ?>
                                <?php while ($row = $currencies_result->fetch_assoc()): ?>
                                    <option value="<?= $row['id_mata_uang'] ?>"><?= htmlspecialchars($row['kode_mata_uang']) ?> - <?= htmlspecialchars($row['nama_mata_uang']) ?></option>
                                <?php endwhile; ?>
                             <?php else: ?>
                                 <option value="" disabled>Belum ada mata uang</option>
                             <?php endif; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan Pemasukan</button>
                </form>

            </div>
        </main>
    </div>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>