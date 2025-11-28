<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include_once '../../config/db.php';

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../login.php?message=" . urlencode("Sesi berakhir.") . "&status=error");
    exit();
}
$current_user_id = $_SESSION['id_pengguna'];

$action = $_REQUEST['action'] ?? '';
$message = "Aksi tidak valid.";
$status = "error";

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'create' || $action == 'update')) {

    $jumlah_piutang = $_POST['jumlah_piutang'] ?? 0;
    $tanggal_tenggat = $_POST['tanggal_tenggat'] ?? null;
    $status_piutang = $_POST['status_piutang'] ?? 'Belum Diterima';
    $id_piutang = $_POST['id_piutang'] ?? null;
    $id_pengguna_form = $_POST['id_pengguna'] ?? null;

    
    if ($id_pengguna_form != $current_user_id) {
        $message = "Error: ID pengguna tidak cocok.";
        $status = "error";
    } else {
        if ($action == 'create') {
            $stmt = $conn->prepare("INSERT INTO receivables (id_pengguna, jumlah_piutang, tanggal_tenggat, status_piutang) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $current_user_id, $jumlah_piutang, $tanggal_tenggat, $status_piutang);

            if ($stmt->execute()) {
                $message = "Piutang berhasil dicatat!";
                $status = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $status = "error";
            }
            $stmt->close();
        } elseif ($action == 'update' && $id_piutang) {
            
            $stmt = $conn->prepare("UPDATE receivables SET jumlah_piutang = ?, tanggal_tenggat = ?, status_piutang = ? WHERE id_piutang = ? AND id_pengguna = ?");
            $stmt->bind_param("dssii", $jumlah_piutang, $tanggal_tenggat, $status_piutang, $id_piutang, $current_user_id);

            if ($stmt->execute()) {
                $message = "Data piutang berhasil diperbarui!";
                $status = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $status = "error";
            }
            $stmt->close();
        }
    }
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_piutang = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM receivables WHERE id_piutang = ? AND id_pengguna = ?");
    $stmt->bind_param("ii", $id_piutang, $current_user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "Piutang berhasil dihapus!";
            $status = "success";
        } else {
            $message = "Data tidak ditemukan atau akses ditolak.";
            $status = "error";
        }
    } else {
        $message = "Error: " . $stmt->error;
        $status = "error";
    }
    $stmt->close();
}

$conn->close();
header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
exit();
