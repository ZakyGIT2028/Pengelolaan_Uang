<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php?message=Silakan login terlebih dahulu&status=error');
    exit();
}


include_once 'config/db.php';
if (!$conn || $conn->connect_error) {
    die("Maaf, terjadi masalah koneksi ke database.");
}
$current_user_id = $_SESSION['id_pengguna'];
$current_user_name = $_SESSION['nama_pengguna'];
$current_user_role = $_SESSION['role'] ?? 'user';


$total_income = 0;
$total_expense = 0;
$current_balance = 0;
$total_goals = 0;
$total_debts = 0;
$total_receivables = 0;
$chart_labels = [];
$chart_data = [];
$wallets_data = [];
$budgets_data = [];
$prediction_data = null;
$result_transactions = null;
$result_goals_list = null;


$stmt = $conn->prepare("SELECT SUM(jumlah) FROM incomes WHERE id_pengguna = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$total_income = $stmt->get_result()->fetch_row()[0] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT SUM(jumlah) FROM expenses WHERE id_pengguna = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$total_expense = $stmt->get_result()->fetch_row()[0] ?? 0;
$stmt->close();

$current_balance = $total_income - $total_expense;

$stmt = $conn->prepare("SELECT SUM(jumlah_utang) FROM debts WHERE id_pengguna = ? AND status_utang != 'Lunas'");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$total_debts = $stmt->get_result()->fetch_row()[0] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT SUM(jumlah_piutang) FROM receivables WHERE id_pengguna = ? AND status_piutang != 'Lunas'");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$total_receivables = $stmt->get_result()->fetch_row()[0] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM financial_goals WHERE id_pengguna = ? AND status = 'Belum Tercapai'");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$total_goals = $stmt->get_result()->fetch_row()[0] ?? 0;
$stmt->close();



$stmt = $conn->prepare("SELECT c.nama_kategori, SUM(e.jumlah) as total FROM expenses e JOIN categories c ON e.id_kategori = c.id_kategori WHERE e.id_pengguna = ? GROUP BY c.nama_kategori ORDER BY total DESC LIMIT 5");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$res_chart = $stmt->get_result();
while ($row = $res_chart->fetch_assoc()) {
    $chart_labels[] = $row['nama_kategori'];
    $chart_data[] = $row['total'];
}
$stmt->close();


$stmt = $conn->prepare("SELECT estimasi_pemasukan, estimasi_pengeluaran, prediksi_saldo_akhir FROM financial_predictions WHERE id_pengguna = ? ORDER BY id_prediksi DESC LIMIT 1");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$prediction_data = $stmt->get_result()->fetch_assoc();
$stmt->close();


$stmt = $conn->prepare("SELECT m.nama_metode, (COALESCE((SELECT SUM(jumlah) FROM incomes WHERE id_metode = m.id_metode AND id_pengguna = ?), 0) - COALESCE((SELECT SUM(jumlah) FROM expenses WHERE id_metode = m.id_metode AND id_pengguna = ?), 0)) as saldo FROM payment_methods m HAVING saldo != 0 ORDER BY saldo DESC");
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$wallets_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


$stmt_trans = $conn->prepare("(SELECT 'Pemasukan' as tipe, sumber as ket, jumlah, tanggal FROM incomes WHERE id_pengguna = ?) UNION ALL (SELECT 'Pengeluaran' as tipe, c.nama_kategori as ket, jumlah, tanggal FROM expenses e LEFT JOIN categories c ON e.id_kategori = c.id_kategori WHERE e.id_pengguna = ?) ORDER BY tanggal DESC LIMIT 5");
$stmt_trans->bind_param("ii", $current_user_id, $current_user_id);
$stmt_trans->execute();
$result_transactions = $stmt_trans->get_result();


$stmt_budget = $conn->prepare("SELECT b.jumlah_anggaran, c.nama_kategori, b.bulan, b.tahun, COALESCE((SELECT SUM(e.jumlah) FROM expenses e WHERE e.id_kategori=b.id_kategori AND e.id_pengguna=b.id_pengguna AND MONTH(e.tanggal)=b.bulan AND YEAR(e.tanggal)=b.tahun), 0) as actual FROM budgets b LEFT JOIN categories c ON b.id_kategori = c.id_kategori WHERE b.id_pengguna = ? ORDER BY b.tahun DESC, b.bulan DESC, c.nama_kategori ASC");
$stmt_budget->bind_param("i", $current_user_id);
$stmt_budget->execute();
$budgets_data = $stmt_budget->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_budget->close();


$stmt_goals = $conn->prepare("SELECT nama_tujuan, total_target, status FROM financial_goals WHERE id_pengguna = ? AND status = 'Belum Tercapai' ORDER BY tanggal_target ASC LIMIT 3");
$stmt_goals->bind_param("i", $current_user_id);
$stmt_goals->execute();
$result_goals_list = $stmt_goals->get_result();


$health_status = "Netral";
$health_color = "secondary";
$health_msg = "Belum cukup data.";
if ($total_income > 0) {
    $ratio = ($total_expense / $total_income) * 100;
    if ($ratio > 100) {
        $health_status = "BAHAYA 🚨";
        $health_color = "danger";
        $health_msg = "Pengeluaran > Pemasukan.";
    } elseif ($ratio > 80) {
        $health_status = "WASPADA ⚠️";
        $health_color = "warning";
        $health_msg = "Sisa uang menipis.";
    } else {
        $health_status = "SEHAT ✅";
        $health_color = "success";
        $health_msg = "Keuangan aman.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - FinSet</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --sidebar-bg: #111827;
            --sidebar-text: #e5e7eb;
            --border-color: #e9ecef;
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --light-bg: #f8f9fc;
            --secondary-color: #5a6268;
        }

        body {
            margin: 0;
            background-color: var(--light-bg);
            font-family: 'Segoe UI', sans-serif;
            color: var(--secondary-color);
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        
        .sidebar {
            width: 240px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding: 20px 15px;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .sidebar-profile {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #374151;
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 8px;
            font-weight: 600;
        }

        .sidebar-nav h2 {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #9ca3af;
            margin: 18px 0 4px 15px;
            font-weight: 600;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            color: var(--sidebar-text);
            padding: 10px 15px;
            border-radius: 6px;
            margin: 2px 0;
            transition: 0.2s;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: #374151;
            color: white;
        }

        .sidebar-nav a.active-dashboard {
            background-color: var(--primary-color);
            color: white;
        }

        .sidebar-footer {
            padding-top: 20px;
            margin-top: auto;
            text-align: center;
            padding-bottom: 10px;
        }

        
        .main-content {
            flex-grow: 1;
            margin-left: 240px;
            padding: 25px;
            box-sizing: border-box;
        }

        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            border-bottom: 4px solid transparent;
        }

        .stat-card .amount {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 4px;
        }

        .stat-card .income {
            color: var(--success-color);
        }

        .stat-card .expense {
            color: var(--danger-color);
        }

        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1.1fr;
            gap: 22px;
        }

        @media (max-width: 1100px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .sidebar {
                display: none;
            }
        }

        
        .widget-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            padding: 22px;
            margin-bottom: 22px;
        }

        .widget-card h2 {
            margin-top: 0;
            margin-bottom: 16px;
            padding-bottom: 12px;
            font-size: 1.1rem;
            color: #333;
            border-bottom: 1px solid var(--border-color);
        }

        
        #expenseChart {
            width: 100% !important;
            height: 120px !important;
            max-width: 150px;
            max-height: 150px;
        }

        
        .ai-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 25px;
        }

        .ai-card h2 {
            color: white;
            border-bottom-color: rgba(255, 255, 255, 0.2);
        }

        .ai-val {
            font-size: 1.6rem;
            font-weight: 800;
            margin: 12px 0;
        }

        
        .transaction-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .transaction-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            align-items: center;
            font-size: 0.95rem;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        
        .progress-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }

        .progress {
            height: 100%;
            background: var(--primary-color);
            border-radius: 3px;
        }

        .progress.over {
            background: var(--danger-color);
        }

        
        .status-badge {
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-Belum-Tercapai {
            background-color: #ffc107;
            color: #333;
        }

        .status-Tercapai {
            background-color: var(--success-color);
            color: white;
        }

        .widget-footer {
            text-align: right;
            margin-top: 12px;
        }

        .budget-widget-list {
            max-height: 260px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .no-data-message {
            text-align: center;
            color: #999;
            font-size: 0.9rem;
            padding: 10px 0;
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
                <a href="index.php" class="active-dashboard">Dashboard</a>
                <a href="modul/incomes/read.php">Pemasukan</a>
                <a href="modul/expenses/read.php">Pengeluaran</a>

                <h2>Perencanaan</h2>
                <a href="modul/budgets/read.php">Anggaran (Budgets)</a>
                <a href="modul/financial_goals/read.php">Tujuan Keuangan (Goals)</a>

                <h2>Aset & Kewajiban</h2>
                <a href="modul/payment_methods/read.php">Metode Bayar (Dompet)</a>
                <a href="modul/debts/read.php">Utang (Debts)</a>
                <a href="modul/receivables/read.php">Piutang (Receivables)</a>

                <h2>Fitur Lain</h2>
                <a href="modul/social_user_relations/read.php">Relasi Sosial</a>
                <a href="modul/financial_predictions/read.php">Prediksi Keuangan</a>

                <h2>Pengaturan Akun</h2>
                <a href="modul/users/update.php?id=<?= $current_user_id ?>">Edit Profil Saya</a>


                <?php if ($current_user_role == 'admin'): ?>
                    <h2 style="color: var(--danger-color);">Area Admin</h2>
                    <a href="modul/users/read.php">Kelola Semua Pengguna</a>
                    <a href="modul/currencies/read.php">Kelola Mata Uang</a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </aside>

        <main class="main-content">

            <div style="margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 2rem; color: #333;">Dashboard Ringkasan</h1>
                <p style="color: #666;">Halo, <?= htmlspecialchars($current_user_name) ?>! Berikut laporan keuangan Anda.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card" style="border-bottom-color: var(--success-color);">
                    <h3>Total Pemasukan</h3>
                    <div class="amount income">Rp <?= number_format($total_income, 0, ',', '.') ?></div>
                </div>
                <div class="stat-card" style="border-bottom-color: var(--danger-color);">
                    <h3>Total Pengeluaran</h3>
                    <div class="amount expense">Rp <?= number_format($total_expense, 0, ',', '.') ?></div>
                </div>
                <div class="stat-card" style="border-bottom-color: var(--primary-color);">
                    <h3>Sisa Saldo</h3>
                    <div class="amount" style="color: var(--primary-color);">Rp <?= number_format($current_balance, 0, ',', '.') ?></div>
                </div>
                <div class="stat-card" style="border-bottom-color: #f6c23e;">
                    <h3>Tujuan Aktif</h3>
                    <div class="amount" style="color: #f6c23e;"><?= $total_goals ?></div>
                </div>
            </div>

            <div class="content-grid">

                <div class="left-col">

                    <div class="widget-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h2 style="margin:0; border:none;">Analisa Pengeluaran</h2>
                            <span class="status-badge bg-<?= $health_color ?>" style="padding: 5px 10px;"><?= $health_status ?></span>
                        </div>
                        <div style="display: flex; gap: 20px; align-items: center;">
                            <div style="flex: 1;">
                                <p style="color: #666;"><?= $health_msg ?></p>
                            </div>
                            <div style="width: 150px; height: 150px;">
                                <?php if (count($chart_data) > 0): ?>
                                    <canvas id="expenseChart"></canvas>
                                <?php else: ?>
                                    <p style="text-align: center; color: #ccc; font-size: 0.8rem; padding-top: 60px;">Data Kosong</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="widget-card">
                        <h2>Transaksi Terakhir</h2>
                        <ul class="transaction-list">
                            <?php if ($result_transactions && $result_transactions->num_rows > 0): ?>
                                <?php while ($row = $result_transactions->fetch_assoc()): ?>
                                    <li class="transaction-item">
                                        <div>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($row['ket'] ?? 'Umum') ?></div>
                                            <div style="font-size: 0.85rem; color: #999;"><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                                        </div>
                                        <div style="font-weight: bold; color: <?= ($row['tipe'] == 'Pemasukan') ? 'var(--success-color)' : 'var(--danger-color)' ?>">
                                            <?= ($row['tipe'] == 'Pemasukan' ? '+' : '-') ?> Rp <?= number_format($row['jumlah'], 0, ',', '.') ?>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="text-align: center; color: #999;">Belum ada transaksi.</p>
                            <?php endif; ?>
                        </ul>
                        <div class="widget-footer">
                            <a href="modul/expenses/read.php" class="btn btn-secondary">Lihat Semua</a>
                        </div>
                    </div>
                </div>

                <div class="right-col">

                    <div class="widget-card ai-card">
                        <div class="widget-header" style="border-bottom-color: rgba(255,255,255,0.2); margin-bottom: 15px; padding-bottom: 10px;">
                            <h2 style="color: white; margin: 0; font-size: 1.2rem;">🔮 Prediksi AI </h2>
                        </div>

                        <?php if ($prediction_data): ?>
                            <div style="text-align: center; margin-bottom: 20px;">
                                <div style="font-size: 0.9rem; opacity: 0.9;">Estimasi Saldo Akhir</div>
                                <div class="ai-val">Rp <?= number_format($prediction_data['prediksi_saldo_akhir'], 0, ',', '.') ?></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; background: rgba(0,0,0,0.15); padding: 10px; border-radius: 8px;">
                                <span>In: Rp <?= number_format($prediction_data['estimasi_pemasukan'] / 1000, 0) ?>k</span>
                                <span>Out: Rp <?= number_format($prediction_data['estimasi_pengeluaran'] / 1000, 0) ?>k</span>
                            </div>
                            <div style="margin-top: 15px; text-align: center;">
                                <a href="modul/financial_predictions/read.php" style="color: white; text-decoration: underline; font-size: 0.9rem;">Lihat Analisis Lengkap &rarr;</a>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px 0;">
                                <p style="margin-bottom: 10px;">Belum ada data prediksi.</p>
                                <a href="modul/financial_predictions/read.php" style="color: white; text-decoration: underline; font-weight: bold;">Buat Analisis Sekarang</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="widget-card">
                        <h2>Anggaran Saya</h2>
                        <div class="budget-widget-list">
                            <?php if (!empty($budgets_data)): ?>
                                <?php
                                $month_names = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
                                foreach ($budgets_data as $b):
                                    $pct = ($b['jumlah_anggaran'] > 0) ? round(($b['actual'] / $b['jumlah_anggaran']) * 100) : 0;
                                    $is_over = $b['actual'] > $b['jumlah_anggaran'];
                                    $period = isset($month_names[$b['bulan']]) ? $month_names[$b['bulan']] . " " . $b['tahun'] : "-";
                                ?>
                                    <div style="margin-bottom: 15px;">
                                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; color: #4b5563;">
                                            <span><?= htmlspecialchars($b['nama_kategori']) ?> <small style="color:#999; font-weight:normal;">(<?= $period ?>)</small></span>
                                            <span style="<?= $is_over ? 'color:var(--danger-color)' : '' ?>"><?= $pct ?>%</span>
                                        </div>
                                        <div class="progress-bar" style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; margin-top: 5px;">
                                            <div class="progress" style="width: <?= min($pct, 100) ?>%; height: 100%; background: <?= $is_over ? 'var(--danger-color)' : 'var(--primary-color)' ?>;"></div>
                                        </div>
                                        <div style="text-align: right; font-size: 0.75rem; color: #999; margin-top: 2px;">
                                            Rp <?= number_format($b['actual'], 0, ',', '.') ?> / <?= number_format($b['jumlah_anggaran'], 0, ',', '.') ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data-message">Belum ada anggaran.</p>
                            <?php endif; ?>
                        </div>
                        <div class="widget-footer">
                            <a href="modul/budgets/read.php" class="btn btn-secondary">Kelola</a>
                        </div>
                    </div>

                    <div class="widget-card">
                        <h2>Tujuan Keuangan</h2>
                        <ul class="transaction-list">
                            <?php if ($result_goals_list && $result_goals_list->num_rows > 0): ?>
                                <?php while ($row = $result_goals_list->fetch_assoc()): ?>
                                    <li class="transaction-item">
                                        <div style="flex-grow: 1;">
                                            <span style="font-weight: 600; display: block;"><?= htmlspecialchars($row['nama_tujuan']) ?></span>
                                            <?php if (isset($row['status'])): ?>
                                                <span class="status-badge status-<?= str_replace(' ', '-', $row['status']) ?>"><?= $row['status'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-weight: 700; color: var(--primary-color);">Rp <?= number_format($row['total_target'], 0, ',', '.') ?></div>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="no-data-message">Belum ada tujuan aktif.</p>
                            <?php endif; ?>
                        </ul>
                        <div class="widget-footer">
                            <a href="modul/financial_goals/read.php" class="btn btn-secondary">Kelola</a>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <script>
        const ctx = document.getElementById('expenseChart');
        if (ctx && <?= count($chart_data) ?> > 0) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        data: <?= json_encode($chart_data) ?>,
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '75%'
                }
            });
        }
    </script>

</body>

</html>
<?php

if (isset($result_goals_list)) $result_goals_list->close();
if (isset($result_transactions)) $result_transactions->close();
if (isset($stmt_trans)) $stmt_trans->close();
if (isset($stmt_goals)) $stmt_goals->close();
if (isset($conn)) $conn->close();
?>