<?php



define('DB_SERVER', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', '');     
define('DB_NAME', 'db_penglolaan_uang'); 


$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);


if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}


$conn->set_charset("utf8mb4");
?>