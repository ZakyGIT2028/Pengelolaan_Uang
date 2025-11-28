<?php
session_start();

if (isset($_SESSION['id_pengguna'])) {
    header('Location: index.php');
    exit();
}

include_once 'config/db.php';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $kata_sandi = $_POST['kata_sandi'] ?? '';

    
    $stmt = $conn->prepare("SELECT id_pengguna, nama, kata_sandi, status_akun, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($kata_sandi, $user['kata_sandi'])) {
            if ($user['status_akun'] == 'Aktif') {
                
                $_SESSION['id_pengguna'] = $user['id_pengguna'];
                $_SESSION['nama_pengguna'] = $user['nama'];
                
                
                $_SESSION['role'] = $user['role']; 
                
                $stmt->close(); 
                $conn->close();
                
                header('Location: index.php');
                exit();
            } else {
                $error_message = "Akun Anda tidak aktif. Silakan hubungi administrator.";
            }
        } else {
            $error_message = "Email atau Kata Sandi salah.";
        }
    } else {
        $error_message = "Email atau Kata Sandi salah.";
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pengelolaan Uang</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .login-container { 
            max-width: 400px; margin: 100px auto; padding: 30px; 
            background: var(--white); border-radius: 12px; 
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1); 
        }
        .login-container h1 { 
            text-align: center; border-bottom: none; 
            margin-bottom: 25px; color: var(--primary-color);
        }
        .signup-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login Aplikasi 🔐</h1>

        <?php 
        if (isset($_GET['message'])) {
            $msg = htmlspecialchars($_GET['message']);
            $class = (isset($_GET['status']) && $_GET['status'] == 'success') ? 'success' : 'error';
            echo "<div class='message $class'>" . $msg . "</div>";
        }
        ?>

        <?php if ($error_message): ?>
            <div class="message error"><?= $error_message ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="kata_sandi">Kata Sandi:</label>
                <input type="password" name="kata_sandi" id="kata_sandi" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>

        <div class="signup-link">
            <p>Belum punya akun? <a href="modul/users/create.php">Daftar Sekarang</a></p>
        </div>
    </div>
</body>
</html>
<?php 
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
?>