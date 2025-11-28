<?php
include_once '../../config/db.php';

$action = $_REQUEST['action'] ?? ''; 


if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'create' || $action == 'update')) {
    
    
    $nama_kategori = $_POST['nama_kategori'] ?? '';
    $tipe_kategori = $_POST['tipe_kategori'] ?? '';
    $id_kategori = $_POST['id_kategori'] ?? null;
    
    
    
    
    if ($action == 'create') {
        $stmt = $conn->prepare("INSERT INTO categories (nama_kategori, tipe_kategori) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama_kategori, $tipe_kategori);

        if ($stmt->execute()) {
            $message = "Kategori berhasil ditambahkan!";
            $status = "success";
        } else {
            $message = "Error saat menambah kategori: " . $stmt->error;
            $status = "error";
        }
        $stmt->close();
    } 
    
    
    
    
    elseif ($action == 'update' && $id_kategori) {
        $stmt = $conn->prepare("UPDATE categories SET nama_kategori = ?, tipe_kategori = ? WHERE id_kategori = ?");
        $stmt->bind_param("ssi", $nama_kategori, $tipe_kategori, $id_kategori);

        if ($stmt->execute()) {
            $message = "Kategori berhasil diperbarui!";
            $status = "success";
        } else {
            $message = "Error saat memperbarui kategori: " . $stmt->error;
            $status = "error";
        }
        $stmt->close();
    }

} 



elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_kategori = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE id_kategori = ?");
    $stmt->bind_param("i", $id_kategori);

    if ($stmt->execute()) {
        $message = "Kategori berhasil dihapus!";
        $status = "success";
    } else {
        
        $message = "Error saat menghapus kategori. Pastikan kategori tidak digunakan pada data pengeluaran manapun: " . $stmt->error;
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