<?php
session_start();
session_destroy(); // Menghapus semua data login
header("location:login.php"); // Mengarahkan kembali ke halaman login
exit();
?>