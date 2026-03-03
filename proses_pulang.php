<?php
include "koneksi.php"; 
date_default_timezone_set('Asia/Jakarta'); // Mengatur waktu Indonesia

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $jam_keluar_otomatis = date("H:i:s"); // Mengambil jam otomatis saat ini

    // Update database menggunakan variabel $koneksi yang benar
    // Gunakan backtick (`) karena nama kolom Anda memiliki spasi (JAM KELUAR)
    $sql = "UPDATE absensi_magang SET `JAM KELUAR` = '$jam_keluar_otomatis' WHERE ID = '$id'";
    $update = mysqli_query($koneksi, $sql);

    if ($update) {
        // Jika berhasil, otomatis kembali ke dashboard dan jam keluar akan langsung muncul
        header("location:mahasiswa_index.php");
        exit();
    } else {
        echo "Gagal memperbarui jam keluar: " . mysqli_error($koneksi);
    }
} else {
    header("location:mahasiswa_index.php");
    exit();
}
?>