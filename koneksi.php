<?php
$koneksi = mysqli_connect("localhost", "root", "", "absensi");

if (mysqli_connect_errno()){
    echo "Koneksi database gagal : " . mysqli_connect_error();
}
// Set zona waktu agar jamnya pas
date_default_timezone_set('Asia/Jakarta');
?>