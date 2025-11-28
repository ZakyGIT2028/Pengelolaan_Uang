<?php
include_once '../../config/db.php';

$action = $_REQUEST['action'] ?? '';


if ($_SERVER["REQUEST_METHOD"] == "POST" && ($action == 'create' || $action == 'update')) {
    
    
    $id_pengguna1 = $_POST['id_pengguna1'] ?? null;
    $id_pengguna2 = $_POST['id_pengguna2'] ?? null;
    $status_relasi = $_POST['status_relasi'] ?? 'Pending';
    $id_relasi = $_POST['id_relasi'] ?? null;

    
    if ($id_pengguna1 == $id_pengguna2) {
        $message = "Error: Pengguna 1 dan Pengguna 2 tidak boleh sama.";
        $status = "error";
        header("Location: read.php?message=" . urlencode($message) . "&status=" . $status);
        exit();
    }

    
    
    
    if ($action == 'create') {
        $stmt = $conn->prepare("INSERT INTO social_user_relations (id_pengguna1, id_pengguna2, status_relasi) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_pengguna1, $id_pengguna2, $status_relasi);

        if ($stmt->execute()) {
            $message = "Relasi berhasil dibuat!";
            $status = "success";
        } else {
            
            $message = "Error saat membuat relasi: Relasi mungkin sudah ada. " . $stmt->error;
            $status = "error";
        }
        $stmt->close();
    } 
    
    
    
    
    elseif ($action == 'update' && $id_relasi) {
        $stmt = $conn->prepare("UPDATE social_user_relations SET id_pengguna1 = ?, id_pengguna2 = ?, status_relasi = ? WHERE id_relasi = ?");
        $stmt->bind_param("iisi", $id_pengguna1, $id_pengguna2, $status_relasi, $id_relasi);

        if ($stmt->execute()) {
            $message = "Relasi berhasil diperbarui!";
            $status = "success";
        } else {
            $message = "Error saat memperbarui relasi: Relasi mungkin sudah ada. " . $stmt->error;
            $status = "error";
        }
        $stmt->close();
    }

} 



elseif ($action == 'delete' && isset($_GET['id'])) {
    $id_relasi = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM social_user_relations WHERE id_relasi = ?");
    $stmt->bind_param("i", $id_relasi);

    if ($stmt->execute()) {
        $message = "Relasi berhasil dihapus!";
        $status = "success";
    } else {
        $message = "Error saat menghapus relasi: " . $stmt->error;
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