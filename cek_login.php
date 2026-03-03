<?php 
session_start();
include 'koneksi.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$login = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");
$cek   = mysqli_num_rows($login);

if ($cek > 0) {
    $data = mysqli_fetch_assoc($login);

    $_SESSION['username'] = $username;
    $_SESSION['nama']     = $data['nama'];   // ✅ Ambil dari kolom 'nama' di tabel users
    $_SESSION['level']    = $data['level'];
    $_SESSION['status']   = "login";

    if ($data['level'] == "admin") {
        header("location:index.php");
        exit;
    } elseif ($data['level'] == "siswa") {   // ✅ Diganti dari 'mahasiswa' ke 'siswa'
        header("location:mahasiswa_index.php");
        exit;
    }
} else {
    header("location:login.php?pesan=gagal");
    exit;
}
?>