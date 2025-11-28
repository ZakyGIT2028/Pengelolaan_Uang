<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include_once '../../config/db.php';


if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../login.php?message=" . urlencode("Sesi berakhir, silakan login kembali.") . "&status=error");
    exit();
}
$current_user_id = $_SESSION['id_pengguna'];


if (!$conn || $conn->connect_error) {
     die("Koneksi Gagal: " . ($conn->connect_error ?? 'Tidak bisa terhubung'));
}

$action = $_REQUEST['action'] ?? '';
$message = "Aksi atau data tidak valid."; 
$status = "error"; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'create' || $action == 'update')) {

    $id_pengguna_form = $_POST['id_pengguna'] ?? null; 
    $id_metode = $_POST['id_metode'] ?? null;
    $id_mata_uang = $_POST['id_mata_uang'] ?? null;
    $jumlah = $_POST['jumlah'] ?? 0;
    $sumber = $_POST['sumber'] ?? null;
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $id_pemasukan = $_POST['id_pemasukan'] ?? null; 

    
    if ($id_pengguna_form != $current_user_id) {
        $message = "Error: Operasi tidak diizinkan (ID pengguna tidak cocok).";
        $status = "error";
    }
    
    elseif ($action == 'create') {
        
        $stmt = $conn->prepare("INSERT INTO incomes (id_pengguna, id_metode, id_mata_uang, jumlah, sumber, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            
            $stmt->bind_param("iiidss", $current_user_id, $id_metode, $id_mata_uang, $jumlah, $sumber, $tanggal);
            if ($stmt->execute()) {
                $message = "Pemasukan berhasil ditambahkan!"; $status = "success";
            } else {
                $message = "Error saat menyimpan: " . $stmt->error; $status = "error";
            }
            $stmt->close();
        } else {
            $message = "Error saat menyiapkan query: " . $conn->error; $status = "error";
        }
    }
    elseif ($action == 'update' && $id_pemasukan) {
        
        $stmt = $conn->prepare("UPDATE incomes SET id_metode = ?, id_mata_uang = ?, jumlah = ?, sumber = ?, tanggal = ? WHERE id_pemasukan = ? AND id_pengguna = ?");
        if ($stmt) {
            
            $stmt->bind_param("iidssii", $id_metode, $id_mata_uang, $jumlah, $sumber, $tanggal, $id_pemasukan, $current_user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                     $message = "Pemasukan berhasil diperbarui!"; $status = "success";
                 } else {
                     $message = "Tidak ada perubahan atau data tidak ditemukan."; $status = "warning";
                 }
            } else {
                $message = "Error saat memperbarui: " . $stmt->error; $status = "error";
            }
            $stmt->close();
        } else {
             $message = "Error saat menyiapkan query update: " . $conn->error; $status = "error";
        }
    }
}

elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_pemasukan = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM incomes WHERE id_pemasukan = ? AND id_pengguna = ?");
     if ($stmt) {
        $stmt->bind_param("ii", $id_pemasukan, $current_user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "Pemasukan berhasil dihapus!"; $status = "success";
            } else {
                $message = "Data tidak ditemukan atau Anda tidak punya hak akses."; $status = "error";
            }
        } else {
            $message = "Error saat menghapus: " . $stmt->error; $status = "error";
        }
        $stmt->close();
    } else {
        $message = "Error saat menyiapkan query delete: " . $conn->error; $status = "error";
    }
}



if (isset($conn) && $conn->ping()) {
    $conn->close();
}


header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
exit();
?>