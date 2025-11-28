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
     $error_msg = isset($conn->connect_error) ? $conn->connect_error : "Koneksi database gagal.";
     
     $referer = $_SERVER['HTTP_REFERER'] ?? '../../index.php';
     header("Location: " . $referer . "?message=" . urlencode("Error database: " . $error_msg) . "&status=error");
     exit();
}

$action = $_REQUEST['action'] ?? '';
$message = "Aksi atau data tidak valid."; 
$status = "error"; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'create' || $action == 'update')) {

    $id_pengguna_form = $_POST['id_pengguna'] ?? null;
    $id_kategori = $_POST['id_kategori'] ?? null;
    $jumlah_anggaran = $_POST['jumlah_anggaran'] ?? 0;
    $bulan = $_POST['bulan'] ?? null;
    $tahun = $_POST['tahun'] ?? null;
    $id_anggaran = $_POST['id_anggaran'] ?? null;

    
    if ($id_pengguna_form != $current_user_id) {
        $message = "Error: Operasi tidak diizinkan (ID pengguna tidak cocok).";
        $status = "error";
    }
    
    elseif ($action == 'create') {
        
        $stmt = $conn->prepare("INSERT INTO budgets (id_pengguna, id_kategori, jumlah_anggaran, bulan, tahun) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iiddi", $current_user_id, $id_kategori, $jumlah_anggaran, $bulan, $tahun);
            if ($stmt->execute()) {
                $message = "Anggaran berhasil dibuat!"; $status = "success";
            } else {
                
                if ($conn->errno == 1062) { 
                     $message = "Error: Anggaran untuk kategori '$id_kategori' pada periode $bulan/$tahun sudah ada.";
                } else {
                     $message = "Error saat menyimpan: (" . $stmt->errno . ") " . $stmt->error;
                }
                 $status = "error";
            }
            $stmt->close();
        } else {
            $message = "Error saat menyiapkan query: (" . $conn->errno . ") " . $conn->error; $status = "error";
        }
    }
    elseif ($action == 'update' && $id_anggaran) {
        
        $stmt = $conn->prepare("UPDATE budgets SET id_kategori = ?, jumlah_anggaran = ?, bulan = ?, tahun = ? WHERE id_anggaran = ? AND id_pengguna = ?");
        if ($stmt) {
            $stmt->bind_param("iddiii", $id_kategori, $jumlah_anggaran, $bulan, $tahun, $id_anggaran, $current_user_id);
            if ($stmt->execute()) {
                 if ($stmt->affected_rows > 0) {
                     $message = "Anggaran berhasil diperbarui!"; $status = "success";
                 } else {
                      
                      if ($conn->errno == 1062) {
                           $message = "Error: Anggaran untuk kategori '$id_kategori' pada periode $bulan/$tahun sudah ada."; $status = "error";
                      } elseif ($conn->errno == 0) { 
                           $message = "Tidak ada perubahan data yang disimpan."; $status = "warning";
                      } else {
                            $message = "Error saat memperbarui atau data tidak ditemukan: (" . $conn->errno . ") " . $conn->error; $status = "error";
                      }
                 }
            } else {
                $message = "Error saat menjalankan update: (" . $stmt->errno . ") " . $stmt->error; $status = "error";
            }
            $stmt->close();
        } else {
             $message = "Error saat menyiapkan query update: (" . $conn->errno . ") " . $conn->error; $status = "error";
        }
    }
}

elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_anggaran = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM budgets WHERE id_anggaran = ? AND id_pengguna = ?");
     if ($stmt) {
        $stmt->bind_param("ii", $id_anggaran, $current_user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "Anggaran berhasil dihapus!"; $status = "success";
            } else {
                $message = "Anggaran tidak ditemukan atau Anda tidak punya hak akses."; $status = "error";
            }
        } else {
            $message = "Error saat menghapus: (" . $stmt->errno . ") " . $stmt->error; $status = "error";
        }
        $stmt->close();
    } else {
        $message = "Error saat menyiapkan query delete: (" . $conn->errno . ") " . $conn->error; $status = "error";
    }
}



if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
    $conn->close();
}


header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
exit();
?>