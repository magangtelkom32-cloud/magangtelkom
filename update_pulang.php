<?php
session_start();
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Proteksi Admin
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $jam_sekarang = date('H:i:s');

    // Update Jam Pulang di Database
    $sql = "UPDATE absensi_magang SET JAM_PULANG = '$jam_sekarang' WHERE ID = '$id'";
    $query = mysqli_query($koneksi, $sql);

    if ($query) {
        echo "<script>
                alert('Berhasil! Mahasiswa telah diabsenkan pulang pada jam $jam_sekarang');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "Gagal Update: " . mysqli_error($koneksi);
    }
} else {
    header("location:index.php");
}
?>