<?php
session_start();
include "koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username' AND password='$password'");
$cek = mysqli_num_rows($query);

if($cek > 0){
    $data = mysqli_fetch_assoc($query);
    
    // Simpan data ke session
    $_SESSION['nama']   = $data['nama']; // Harus sesuai kolom di tabel user
    $_SESSION['status'] = "login";
    $_SESSION['level']  = $data['level']; // Pastikan ada kolom level (admin/mahasiswa)

    if($data['level'] == "admin"){
        echo "<script>alert('Login Admin Berhasil'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Login Mahasiswa Berhasil'); window.location.href='mahasiswa_index.php';</script>";
    }
} else {
    echo "<script>alert('Username atau Password Salah!'); window.location.href='login.php';</script>";
}
?>