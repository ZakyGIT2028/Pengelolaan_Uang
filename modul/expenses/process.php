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


function getOrCreateCategoryId($conn, $categoryName) {
    $categoryName = trim($categoryName);
    if (empty($categoryName)) {
        return null;
    }
    
    $stmt_check = $conn->prepare("SELECT id_kategori FROM categories WHERE LOWER(nama_kategori) = LOWER(?) LIMIT 1");
    if(!$stmt_check) { error_log("Prepare Check Error: ".$conn->error); return null; }
    $stmt_check->bind_param("s", $categoryName);
    if(!$stmt_check->execute()) { error_log("Execute Check Error: ".$stmt_check->error); return null; }
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $existing_category = $result_check->fetch_assoc();
        $stmt_check->close();
        return $existing_category['id_kategori'];
    } else {
        
        $stmt_check->close();
        $stmt_insert = $conn->prepare("INSERT INTO categories (nama_kategori, tipe_kategori) VALUES (?, 'Dinamis')");
         if(!$stmt_insert) { error_log("Prepare Insert Error: ".$conn->error); return null; }
        $stmt_insert->bind_param("s", $categoryName);
        if ($stmt_insert->execute()) {
            $new_category_id = $conn->insert_id;
            $stmt_insert->close();
            return $new_category_id;
        } else {
            
             error_log("Execute Insert Error: ".$stmt_insert->error);
            $stmt_insert->close();
            
            return getOrCreateCategoryId($conn, $categoryName);
        }
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'create' || $action == 'update')) {

    $id_pengguna_form = $_POST['id_pengguna'] ?? null;
    $kategori_input = $_POST['kategori_input'] ?? null; 
    $id_metode = $_POST['id_metode'] ?? null;
    $id_mata_uang = $_POST['id_mata_uang'] ?? null;
    $jumlah = $_POST['jumlah'] ?? 0;
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $id_pengeluaran = $_POST['id_pengeluaran'] ?? null;

    
    if ($id_pengguna_form != $current_user_id) {
        $message = "Error: Operasi tidak diizinkan."; $status = "error";
    } else {
        
        $id_kategori = getOrCreateCategoryId($conn, $kategori_input);

        if ($id_kategori === null || $id_kategori === false) { 
            $message = "Error: Nama kategori '$kategori_input' tidak valid atau gagal diproses."; $status = "error";
        } else {
             
             if ($action == 'create') {
                
                $stmt = $conn->prepare("INSERT INTO expenses (id_pengguna, id_kategori, id_metode, id_mata_uang, jumlah, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("iiiids", $current_user_id, $id_kategori, $id_metode, $id_mata_uang, $jumlah, $tanggal);
                    if ($stmt->execute()) { $message = "Pengeluaran berhasil ditambahkan!"; $status = "success"; }
                    else { $message = "Error saat menyimpan: (" . $stmt->errno . ") " . $stmt->error; $status = "error"; }
                    $stmt->close();
                } else { $message = "Error prepare create: (" . $conn->errno . ") " . $conn->error; $status = "error"; }

            } elseif ($action == 'update' && $id_pengeluaran) {
                
                $stmt = $conn->prepare("UPDATE expenses SET id_kategori = ?, id_metode = ?, id_mata_uang = ?, jumlah = ?, tanggal = ? WHERE id_pengeluaran = ? AND id_pengguna = ?");
                 if ($stmt) {
                    $stmt->bind_param("iiidsii", $id_kategori, $id_metode, $id_mata_uang, $jumlah, $tanggal, $id_pengeluaran, $current_user_id);
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) { $message = "Pengeluaran berhasil diperbarui!"; $status = "success"; }
                        else {
                           if ($conn->errno == 0) { $message = "Tidak ada perubahan data."; $status = "warning";}
                           else { $message = "Error saat memperbarui atau data tidak ditemukan: (" . $conn->errno . ") " . $conn->error; $status = "error"; }
                        }
                    } else { $message = "Error saat menjalankan update: (" . $stmt->errno . ") " . $stmt->error; $status = "error"; }
                    $stmt->close();
                 } else { $message = "Error prepare update: (" . $conn->errno . ") " . $conn->error; $status = "error"; }
            }
        }
    }
}

elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_pengeluaran = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id_pengeluaran = ? AND id_pengguna = ?");
     if ($stmt) {
        $stmt->bind_param("ii", $id_pengeluaran, $current_user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) { $message = "Pengeluaran berhasil dihapus!"; $status = "success"; }
            else { $message = "Data tidak ditemukan atau Anda tidak punya hak akses."; $status = "error"; }
        } else { $message = "Error saat menghapus: (" . $stmt->errno . ") " . $stmt->error; $status = "error"; }
        $stmt->close();
    } else { $message = "Error prepare delete: (" . $conn->errno . ") " . $conn->error; $status = "error"; }
}



if (isset($conn) && $conn instanceof mysqli && $conn->ping()) { $conn->close(); }
header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
exit();
?>