<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include_once '../../config/db.php';

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../login.php?message=" . urlencode("Sesi berakhir.") . "&status=error");
    exit();
}

$action = $_REQUEST['action'] ?? '';
$message = "Aksi tidak valid.";
$status = "error";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_metode = $_POST['nama_metode'] ?? '';
    $id_metode = $_POST['id_metode'] ?? null;

    if ($action == 'create') {
        $stmt = $conn->prepare("INSERT INTO payment_methods (nama_metode) VALUES (?)");
        $stmt->bind_param("s", $nama_metode);
        if ($stmt->execute()) {
            $message = "Metode pembayaran berhasil ditambahkan!";
            $status = "success";
        } else {
            $message = "Error saat menambah: " . $stmt->error;
            $status = "error";
        }
        $stmt->close();
    } elseif ($action == 'update' && $id_metode) {
        $stmt = $conn->prepare("UPDATE payment_methods SET nama_metode = ? WHERE id_metode = ?");
        $stmt->bind_param("si", $nama_metode, $id_metode);
        if ($stmt->execute()) {
            $message = "Metode pembayaran berhasil diperbarui!";
            $status = "success";
        } else {
            $message = "Error saat memperbarui: " . $stmt->error;
            $status = "error";
        }
        $stmt->close();
    }
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_metode = $_GET['id'];
    
    
    $stmt = $conn->prepare("DELETE FROM payment_methods WHERE id_metode = ?");
    $stmt->bind_param("i", $id_metode);

    try {
        if ($stmt->execute()) {
            $message = "Metode pembayaran berhasil dihapus!";
            $status = "success";
        } else {
            
            $message = "Gagal menghapus. Metode ini mungkin sedang digunakan dalam transaksi.";
            $status = "error";
        }
    } catch (mysqli_sql_exception $e) {
        
        if ($e->getCode() == 1451) {
            $message = "Tidak dapat menghapus metode ini karena masih digunakan dalam data Pemasukan atau Pengeluaran.";
        } else {
            $message = "Error database: " . $e->getMessage();
        }
        $status = "error";
    }
    $stmt->close();
}

$conn->close();
header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
exit();
