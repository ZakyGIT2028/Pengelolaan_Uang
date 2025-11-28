<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();


include_once '../../config/db.php';
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../login.php");
    exit();
}
$current_user_id = $_SESSION['id_pengguna'];

$action = $_GET['action'] ?? '';

if ($action == 'generate') {

    
    function getPrediction($conn, $userId, $table)
    {
        
        
        $sql_history = "SELECT AVG(monthly_total) as prediction 
                        FROM (
                            SELECT SUM(jumlah) as monthly_total 
                            FROM $table 
                            WHERE id_pengguna = ? 
                            AND tanggal < DATE_FORMAT(NOW(), '%Y-%m-01') /* Data sebelum bulan ini */
                            GROUP BY YEAR(tanggal), MONTH(tanggal) 
                            ORDER BY YEAR(tanggal) DESC, MONTH(tanggal) DESC LIMIT 3
                        ) as subquery";

        $stmt = $conn->prepare($sql_history);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $val = $stmt->get_result()->fetch_assoc()['prediction'] ?? 0;
        $stmt->close();

        
        
        if (floatval($val) == 0) {
            $sql_current = "SELECT SUM(jumlah) as current_total 
                            FROM $table 
                            WHERE id_pengguna = ? 
                            AND MONTH(tanggal) = MONTH(CURRENT_DATE()) 
                            AND YEAR(tanggal) = YEAR(CURRENT_DATE())";
            $stmt = $conn->prepare($sql_current);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $val = $stmt->get_result()->fetch_assoc()['current_total'] ?? 0;
            $stmt->close();
        }

        return $val;
    }

    
    $pred_income = getPrediction($conn, $current_user_id, 'incomes');
    $pred_expense = getPrediction($conn, $current_user_id, 'expenses');

    
    $pred_balance = $pred_income - $pred_expense;

    
    $next_month = date('n', strtotime('+1 month'));

    
    $stmt_check = $conn->prepare("SELECT id_prediksi FROM financial_predictions WHERE id_pengguna = ? AND bulan_prediksi = ?");
    $stmt_check->bind_param("ii", $current_user_id, $next_month);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        
        $id_prediksi = $result_check->fetch_assoc()['id_prediksi'];
        $stmt_save = $conn->prepare("UPDATE financial_predictions SET estimasi_pemasukan = ?, estimasi_pengeluaran = ?, prediksi_saldo_akhir = ? WHERE id_prediksi = ?");
        $stmt_save->bind_param("dddi", $pred_income, $pred_expense, $pred_balance, $id_prediksi);
        $message = "Prediksi diperbarui! (Data: " . ($pred_income > 0 ? "Historis/Bulan Ini" : "Kosong") . ")";
        $stmt_save->execute(); 
        $stmt_save->close();
    } else {
        
        $stmt_save = $conn->prepare("INSERT INTO financial_predictions (id_pengguna, bulan_prediksi, estimasi_pemasukan, estimasi_pengeluaran, prediksi_saldo_akhir) VALUES (?, ?, ?, ?, ?)");
        $stmt_save->bind_param("iiddd", $current_user_id, $next_month, $pred_income, $pred_expense, $pred_balance);
        $message = "Prediksi berhasil dibuat! (Data: " . ($pred_income > 0 ? "Historis/Bulan Ini" : "Kosong") . ")";
        $stmt_save->execute(); 
        $stmt_save->close();
    }

    $stmt_check->close(); 

    header("Location: read.php?message=" . urlencode($message) . "&status=success");
    exit();
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM financial_predictions WHERE id_prediksi = ? AND id_pengguna = ?");
    $stmt->bind_param("ii", $id, $current_user_id);
    if ($stmt->execute()) {
        header("Location: read.php?message=Data prediksi dihapus&status=success");
    } else {
        header("Location: read.php?message=Gagal menghapus&status=error");
    }
    $stmt->close();
    exit();
} else {
    
    header("Location: read.php");
    exit();
}
