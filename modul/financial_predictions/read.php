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


$sql = "SELECT * FROM financial_predictions WHERE id_pengguna = ? ORDER BY id_prediksi DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$prediction = $result->fetch_assoc();


$month_names = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Keuangan (AI) - FinSet</title>
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

        
        .ai-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
            text-align: center;
        }

        .ai-header h1 {
            border: none;
            color: white;
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .ai-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        .prediction-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .prediction-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            border-top: 5px solid #ddd;
        }

        .prediction-card h3 {
            color: #6c757d;
            margin-bottom: 10px;
            font-size: 1rem;
            font-weight: 600;
        }

        .prediction-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .card-income {
            border-top-color: var(--success-color);
        }

        .card-income .value {
            color: var(--success-color);
        }

        .card-expense {
            border-top-color: var(--danger-color);
        }

        .card-expense .value {
            color: var(--danger-color);
        }

        .card-balance {
            border-top-color: var(--primary-color);
        }

        .card-balance .value {
            color: var(--primary-color);
        }

        .insight-box {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            display: flex;
            align-items: start;
            gap: 20px;
        }

        .insight-icon {
            font-size: 2.5rem;
        }

        .insight-content h3 {
            margin-top: 0;
            color: #3730a3;
        }

        .insight-content p {
            color: #4338ca;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .generate-btn {
            background-color: white;
            color: #764ba2;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: transform 0.2s;
        }

        .generate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .status-safe {
            color: var(--success-color);
            font-weight: bold;
        }

        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }

        .status-danger {
            color: var(--danger-color);
            font-weight: bold;
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
                <a href="../receivables/read.php">Piutang (Receivables)</a>
                <h2>Fitur Lain</h2>
                <a href="../social_user_relations/read.php">Relasi Sosial</a>
                <a href="read.php" class="active">Prediksi Keuangan</a>
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

            <div class="ai-header">
                <h1>FinSet AI Predictor 🔮</h1>
                <p>Analisis cerdas berdasarkan data historis keuangan Anda untuk memprediksi tren bulan depan.</p>
                <a href="process.php?action=generate" class="generate-btn">⚡ Analisis & Buat Prediksi Baru</a>
            </div>

            <?php if ($prediction): ?>
                <?php
                $saldo = $prediction['prediksi_saldo_akhir'];
                $status_kesehatan = "";
                $advice_text = "";

                if ($saldo > 0) {
                    $status_kesehatan = "<span class='status-safe'>SEHAT 🟢</span>";
                    $advice_text = "Kondisi keuangan Anda diprediksi positif bulan depan. Ini saat yang tepat untuk mengalokasikan surplus dana ke <b>Tujuan Keuangan</b> Anda atau investasi.";
                } elseif ($saldo == 0) {
                    $status_kesehatan = "<span class='status-warning'>WASPADA 🟡</span>";
                    $advice_text = "Anda diprediksi impas (Break Even). Cobalah kurangi pengeluaran sekunder agar memiliki dana darurat.";
                } else {
                    $status_kesehatan = "<span class='status-danger'>BAHAYA 🔴</span>";
                    $advice_text = "Peringatan! Pengeluaran Anda diprediksi melebihi pemasukan. Segera tinjau ulang <b>Anggaran</b> Anda dan potong pengeluaran yang tidak perlu.";
                }

                $bulan_text = $month_names[$prediction['bulan_prediksi']] ?? 'Bulan Depan';
                ?>

                <div style="margin-bottom: 20px;">
                    <h2 style="margin-bottom: 5px;">Hasil Prediksi: <?= $bulan_text ?></h2>
                    <p style="color: #6c757d;">Data terakhir diperbarui secara otomatis.</p>
                </div>

                <div class="prediction-grid">
                    <div class="prediction-card card-income">
                        <h3>Estimasi Pemasukan</h3>
                        <div class="value">Rp <?= number_format($prediction['estimasi_pemasukan'], 0, ',', '.') ?></div>
                    </div>
                    <div class="prediction-card card-expense">
                        <h3>Estimasi Pengeluaran</h3>
                        <div class="value">Rp <?= number_format($prediction['estimasi_pengeluaran'], 0, ',', '.') ?></div>
                    </div>
                    <div class="prediction-card card-balance">
                        <h3>Prediksi Saldo Akhir</h3>
                        <div class="value">Rp <?= number_format($prediction['prediksi_saldo_akhir'], 0, ',', '.') ?></div>
                    </div>
                </div>

                <div class="insight-box">
                    <div class="insight-icon">🤖</div>
                    <div class="insight-content">
                        <h3>Analisis & Rekomendasi Cerdas</h3>
                        <p>Berdasarkan tren 3 bulan terakhir, status kesehatan keuangan Anda diprediksi: <?= $status_kesehatan ?></p>
                        <p style="margin-top: 10px;"><b>Saran:</b> <?= $advice_text ?></p>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <a href="process.php?action=delete&id=<?= $prediction['id_prediksi'] ?>" class="btn btn-danger" onclick="return confirm('Hapus prediksi ini?')">Hapus Prediksi</a>
                </div>

            <?php else: ?>

                <div style="text-align: center; padding: 50px; background: white; border-radius: 12px; box-shadow: var(--box-shadow);">
                    <img src="https://cdn-icons-png.flaticon.com/512/4712/4712009.png" alt="No Data" style="width: 100px; opacity: 0.5; margin-bottom: 20px;">
                    <h3>Belum ada data prediksi.</h3>
                    <p style="color: #6c757d;">Klik tombol di atas untuk membiarkan AI menganalisis data keuangan Anda.</p>
                </div>

            <?php endif; ?>

        </main>
    </div>
</body>

</html>