<?php
include_once '../../config/db.php';
session_start(); 

$action = $_REQUEST['action'] ?? '';


if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'create' || $action == 'update')) {
    
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $tanggal_daftar = $_POST['tanggal_daftar'] ?? date('Y-m-d');
    $status_akun = $_POST['status_akun'] ?? 'Aktif';
    $id_pengguna = $_POST['id_pengguna'] ?? null;
    
    
    
    $role = $_POST['role'] ?? 'user';

    
    if ($role == 'admin' && (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin')) {
        $role = 'user'; 
    }
    
    
    
    
    if ($action == 'create') {
        $kata_sandi = $_POST['kata_sandi'] ?? '';
        $hashed_password = password_hash($kata_sandi, PASSWORD_DEFAULT);

        
        $stmt = $conn->prepare("INSERT INTO users (nama, email, kata_sandi, tanggal_daftar, status_akun, role) VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssss", $nama, $email, $hashed_password, $tanggal_daftar, $status_akun, $role);

        if ($stmt->execute()) {
            $message = "Pendaftaran berhasil! Silakan Login.";
            $status = "success";
            $stmt->close();
            $conn->close();
            
            
            header("Location: ../../login.php?message=" . urlencode($message) . "&status=" . $status);
            exit(); 
            
        } else {
            $message = "Error saat mendaftar pengguna: " . $stmt->error;
            $status = "error";
        }
        $stmt->close();
    } 
    
    
    
    
    elseif ($action == 'update' && $id_pengguna) {
        
        
        if (!isset($_SESSION['id_pengguna']) || ($_SESSION['role'] != 'admin' && $_SESSION['id_pengguna'] != $id_pengguna)) {
            $message = "Error: Anda tidak punya hak akses untuk operasi ini.";
            $status = "error";
            header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
            exit();
        }
        
        $new_kata_sandi = $_POST['new_kata_sandi'] ?? '';
        
        
        $sql = "UPDATE users SET nama = ?, email = ?, tanggal_daftar = ?, status_akun = ?, role = ?";
        $params = [$nama, $email, $tanggal_daftar, $status_akun, $role];
        $types = "sssss"; 
        
        if (!empty($new_kata_sandi)) {
            $hashed_password = password_hash($new_kata_sandi, PASSWORD_DEFAULT);
            $sql .= ", kata_sandi = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }
        
        $sql .= " WHERE id_pengguna = ?";
        $params[] = $id_pengguna;
        $types .= "i"; 
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $message = "Pengguna berhasil diperbarui!";
            $status = "success";
        } else {
            $message = "Error saat memperbarui pengguna: " . $stmt->error;
            $status = "error";
        }
        $stmt->close();
    }
} 



elseif ($action == 'delete' && isset($_GET['id'])) {
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
         $message = "Error: Anda tidak punya hak akses untuk menghapus.";
         $status = "error";
         header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
         exit();
    }
    
    $id_pengguna = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id_pengguna = ?");
    $stmt->bind_param("i", $id_pengguna);

    if ($stmt->execute()) {
        $message = "Pengguna berhasil dihapus!";
        $status = "success";
    } else {
        $message = "Error saat menghapus pengguna: " . $stmt->error;
        $status = "error";
    }
    $stmt->close();
} else {
    $message = "Aksi atau data tidak valid.";
    $status = "error";
}

$conn->close();


header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
exit();
?>