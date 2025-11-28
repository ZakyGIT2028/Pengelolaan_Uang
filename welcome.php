<?php
session_start();

if (isset($_SESSION['id_pengguna'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - FinSet</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .welcome-card { 
            max-width: 500px; 
            margin: 100px auto; 
            padding: 40px; 
            text-align: center; 
            background: var(--white); 
            border-radius: 12px; 
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1); 
        }
        .welcome-card h1 { color: var(--primary-color); border-bottom: none; }
        .welcome-actions { margin-top: 30px; }
        .welcome-actions a { margin: 0 10px; }
    </style>
</head>
<body>
    <div class="welcome-card">
        <h1>Selamat Datang di FinSet 💡</h1>
        <p>Aplikasi Pengelolaan Keuangan Pribadi Anda yang Inovatif.</p>
        
        <div class="welcome-actions">
            <a href="login.php" class="btn btn-primary btn-lg" style="padding: 12px 30px;">
                Login
            </a>
            
            <a href="modul/users/create.php" class="btn btn-success btn-lg" style="padding: 12px 30px;">
                Sign Up
            </a>
        </div>

        <p style="margin-top: 30px; font-size: 0.9em; color: #777;">
            Kelola Transaksi, Anggaran, dan Tujuan Keuangan Anda.
        </p>
    </div>
</body>
</html>