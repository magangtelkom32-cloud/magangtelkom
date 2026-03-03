<?php
session_start();
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

$nama = $_POST['nama'];
$status = $_POST['status_hadir'];
$kegiatan = $_POST['kegiatan'];
$tgl = date("Y-m-d");
$jam = date("H:i:s");

// Gunakan backticks (`) untuk kolom yang pakai SPASI agar tidak error
$query = "INSERT INTO absensi_magang 
          (`nama`, `status_hadir`, `KEGIATAN`, `TANGGAL`, `JAM MASUK`, `JAM KELUAR`) 
          VALUES 
          ('$nama', '$status', '$kegiatan', '$tgl', '$jam', '00:00:00')";

$simpan = mysqli_query($koneksi, $query);

if($simpan) {
    echo "<script>alert('Berhasil Absen!'); window.location='mahasiswa_index.php';</script>";
} else {
    echo "Error: " . mysqli_error($koneksi);
}
?>