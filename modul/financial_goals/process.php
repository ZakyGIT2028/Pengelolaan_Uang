<?php

ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();


include_once '../../config/db.php';
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../../login.php?message=" . urlencode("Sesi berakhir.") . "&status=error");
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
$message = "Aksi atau data tidak valid."; $status = "error";


if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'create' || $action == 'update')) {

    $id_pengguna_form = $_POST['id_pengguna'] ?? null; 
    $nama_tujuan = trim($_POST['nama_tujuan'] ?? '');
    $total_target = filter_var($_POST['total_target'] ?? 0, FILTER_VALIDATE_FLOAT); 
    $tanggal_target = $_POST['tanggal_target'] ?? null;
    $status_from_form = $_POST['status'] ?? 'Belum Tercapai'; 
    $id_tujuan = $_POST['id_tujuan'] ?? null; 

    
    if (empty($nama_tujuan)) {
         $message = "Error: Nama tujuan wajib diisi."; $status = "error";
    } elseif ($total_target === false || $total_target <= 0) {
         $message = "Error: Target dana harus berupa angka positif."; $status = "error";
    } elseif (empty($tanggal_target)) {
         $message = "Error: Tanggal target wajib diisi."; $status = "error";
    }
    
    elseif ($id_pengguna_form != $current_user_id) {
        $message = "Error: Operasi tidak diizinkan (ID pengguna tidak cocok)."; $status = "error";
    }
    
    elseif (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $tanggal_target)) {
         $message = "Error: Format tanggal target tidak valid (gunakan YYYY-MM-DD)."; $status = "error";
    }
    
    elseif (!in_array($status_from_form, ['Belum Tercapai', 'Tercapai'])) {
         $message = "Error: Nilai status tidak valid."; $status = "error";
    }
    else {
         
         if ($action == 'create') {
            
            $stmt = $conn->prepare("INSERT INTO financial_goals (id_pengguna, nama_tujuan, total_target, tanggal_target, status) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $status_create = 'Belum Tercapai'; 
                
                $stmt->bind_param("isdss", $current_user_id, $nama_tujuan, $total_target, $tanggal_target, $status_create);
                if ($stmt->execute()) {
                    $message = "Tujuan keuangan berhasil ditambahkan!"; $status = "success";
                } else {
                    $message = "Error saat menyimpan: (" . $stmt->errno . ") " . $stmt->error; $status = "error";
                }
                $stmt->close();
            } else {
                $message = "Error saat menyiapkan query (create): (" . $conn->errno . ") " . $conn->error; $status = "error";
            }

        } elseif ($action == 'update' && $id_tujuan) {
            
            $stmt = $conn->prepare("UPDATE financial_goals SET nama_tujuan = ?, total_target = ?, tanggal_target = ?, status = ? WHERE id_tujuan = ? AND id_pengguna = ?");
            if ($stmt) {
                 
                $stmt->bind_param("sdssii", $nama_tujuan, $total_target, $tanggal_target, $status_from_form, $id_tujuan, $current_user_id);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = "Tujuan keuangan berhasil diperbarui!"; $status = "success";
                    } else {
                        
                        if ($conn->errno == 0) {
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
                 $message = "Error saat menyiapkan query (update): (" . $conn->errno . ") " . $conn->error; $status = "error";
            }
        }
    } 
}

elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_tujuan = filter_var($_GET['id'], FILTER_VALIDATE_INT); 

    if ($id_tujuan === false) {
         $message = "Error: ID tujuan tidak valid."; $status = "error";
    } else {
        $stmt = $conn->prepare("DELETE FROM financial_goals WHERE id_tujuan = ? AND id_pengguna = ?");
         if ($stmt) {
            $stmt->bind_param("ii", $id_tujuan, $current_user_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                     $message = "Tujuan keuangan berhasil dihapus!"; $status = "success";
                } else {
                     $message = "Data tidak ditemukan atau Anda tidak punya hak akses."; $status = "error";
                }
            } else {
                 $message = "Error saat menghapus: (" . $stmt->errno . ") " . $stmt->error; $status = "error";
            }
            $stmt->close();
        } else {
             $message = "Error saat menyiapkan query (delete): (" . $conn->errno . ") " . $conn->error; $status = "error";
        }
    }
}



if (isset($conn) && $conn instanceof mysqli && $conn->ping()) { $conn->close(); }
header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
exit();
?>