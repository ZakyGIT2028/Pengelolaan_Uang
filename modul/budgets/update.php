<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: ../../login.php?message=Silakan login terlebih dahulu&status=error');
    exit();
}


include_once '../../config/db.php';
if (!$conn || $conn->connect_error) {
     $error_msg = isset($conn->connect_error) ? $conn->connect_error : "Koneksi database gagal.";
     header('Location: ../../login.php?message=' . urlencode("Error database: " . $error_msg) . '&status=error');
     exit();
}
$current_user_id = $_SESSION['id_pengguna'];
$current_user_name = $_SESSION['nama_pengguna'];
$current_user_role = $_SESSION['role'] ?? 'user';


$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: read.php?message=ID Anggaran tidak valid&status=error');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM budgets WHERE id_anggaran = ? AND id_pengguna = ?");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("ii", $id, $current_user_id);
if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
$result_budget = $stmt->get_result();

if ($result_budget->num_rows === 0) {
    header('Location: read.php?message=Anggaran tidak ditemukan atau Anda tidak punya hak akses&status=error');
    exit();
}
$budget = $result_budget->fetch_assoc();
$stmt->close();


$categories_result = $conn->query("SELECT id_kategori, nama_kategori FROM categories ORDER BY nama_kategori ASC");
if (!$categories_result) die("Query Kategori Gagal: " . $conn->error);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anggaran - FinSet</title>
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

        
         .widget-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            padding: 25px;
            overflow: hidden; 
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
        table thead th { background-color: #f8f9fa;  text-align: left; padding: 12px 15px; border-bottom: 2px solid var(--border-color); font-weight: 600; color: var(--secondary-color); }
        table tbody td { vertical-align: middle; padding: 12px 15px; border-bottom: 1px solid var(--border-color); }
        table tbody tr:last-child td { border-bottom: none; } 
        table tbody tr:hover { background-color: #f1f1f1; } 

        
        .form-group label { color: #495057; display: block; margin-bottom: 8px; font-weight: 500;}
        button[type="submit"] { padding: 12px 25px; font-size: 1rem; } 

        
        .action-link { font-size: 0.9em; padding: 6px 10px;}

        
        .message.warning { background-color: #fff3cd; color: #856404; border-color: #ffeeba; }
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
                <a href="read.php" class="active">Anggaran (Budgets)</a> <a href="../financial_goals/read.php">Tujuan Keuangan (Goals)</a>
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

                <h1>Edit Anggaran (ID: <?= $budget['id_anggaran'] ?>) ✏️</h1>
                <p style="margin-top: -20px; margin-bottom: 20px;"><a href="read.php" class="btn btn-primary"> &lt;-- Kembali ke Daftar Anggaran</a></p>

                <form action="process.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_anggaran" value="<?= $budget['id_anggaran'] ?>">
                    <input type="hidden" name="id_pengguna" value="<?= $budget['id_pengguna'] ?>">

                    <div class="form-group">
                        <label for="id_kategori">Kategori:</label>
                        <select name="id_kategori" id="id_kategori" required>
                             <?php if ($categories_result->num_rows > 0): ?>
                                <?php while ($row = $categories_result->fetch_assoc()): ?>
                                    <option value="<?= $row['id_kategori'] ?>" <?= ($budget['id_kategori'] == $row['id_kategori']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['nama_kategori']) ?>
                                    </option>
                                <?php endwhile; ?>
                             <?php else: ?>
                                 <option value="" disabled>Belum ada kategori</option>
                             <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jumlah_anggaran">Jumlah Anggaran (Rp):</label>
                        <input type="number" name="jumlah_anggaran" id="jumlah_anggaran" step="1"
                               value="<?= $budget['jumlah_anggaran'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="bulan">Bulan (1-12):</label>
                        <input type="number" name="bulan" id="bulan" min="1" max="12"
                               value="<?= $budget['bulan'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="tahun">Tahun:</label>
                        <input type="number" name="tahun" id="tahun" min="2000" max="<?= date('Y')+5 ?>"
                               value="<?= $budget['tahun'] ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>

            </div>
        </main>
    </div>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>