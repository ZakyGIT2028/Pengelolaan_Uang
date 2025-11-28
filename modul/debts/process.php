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

    $jumlah_utang = $_POST['jumlah_utang'] ?? 0;
    $tanggal_tenggat = $_POST['tanggal_tenggat'] ?? null;
    $status_utang = $_POST['status_utang'] ?? 'Belum Dibayar';
    $id_utang = $_POST['id_utang'] ?? null;
    $id_pengguna_form = $_POST['id_pengguna'] ?? null;

    
    if ($id_pengguna_form != $current_user_id) {
        $message = "Error: ID pengguna tidak cocok.";
        $status = "error";
    } else {
        if ($action == 'create') {
            $stmt = $conn->prepare("INSERT INTO debts (id_pengguna, jumlah_utang, tanggal_tenggat, status_utang) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $current_user_id, $jumlah_utang, $tanggal_tenggat, $status_utang);

            if ($stmt->execute()) {
                $message = "Utang berhasil dicatat!";
                $status = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $status = "error";
            }
            $stmt->close();
        } elseif ($action == 'update' && $id_utang) {
            
            $stmt = $conn->prepare("UPDATE debts SET jumlah_utang = ?, tanggal_tenggat = ?, status_utang = ? WHERE id_utang = ? AND id_pengguna = ?");
            $stmt->bind_param("dssii", $jumlah_utang, $tanggal_tenggat, $status_utang, $id_utang, $current_user_id);

            if ($stmt->execute()) {
                $message = "Data utang berhasil diperbarui!";
                $status = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $status = "error";
            }
            $stmt->close();
        }
    }
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_utang = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM debts WHERE id_utang = ? AND id_pengguna = ?");
    $stmt->bind_param("ii", $id_utang, $current_user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "Utang berhasil dihapus!";
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
