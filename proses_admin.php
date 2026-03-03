<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['status'])) { header("location:login.php"); exit; }

$act = $_GET['act'] ?? '';

if ($act == "tambah") {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $kegiatan = mysqli_real_escape_string($koneksi, $_POST['kegiatan'] ?? '');
    $jam      = date('H:i:s');
    $tgl      = date('Y-m-d');
    mysqli_query($koneksi, "INSERT INTO absensi_magang (NAMA, TANGGAL, JAM_MASUK, KEGIATAN, JAM_PULANG) VALUES ('$nama','$tgl','$jam','$kegiatan','BELUM')");
    header("location:index.php?msg=ok"); exit;

} elseif ($act == "edit") {
    $id       = (int)($_POST['id'] ?? 0);
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $kegiatan = mysqli_real_escape_string($koneksi, $_POST['kegiatan'] ?? '');
    $jam_m    = $_POST['jam_masuk'] ?? '';
    $jam_p    = $_POST['jam_pulang'] ?? '';
    if (empty($jam_p)) $jam_p = 'BELUM';
    mysqli_query($koneksi, "UPDATE absensi_magang SET NAMA='$nama', KEGIATAN='$kegiatan', JAM_MASUK='$jam_m', JAM_PULANG='$jam_p' WHERE id='$id'");
    header("location:index.php?msg=ok"); exit;

} elseif ($act == "pulang") {
    $id   = (int)($_GET['id'] ?? 0);
    $jam  = date('H:i:s');
    mysqli_query($koneksi, "UPDATE absensi_magang SET JAM_PULANG='$jam' WHERE id='$id'");
    header("location:index.php?msg=ok"); exit;

} elseif ($act == "pulang_semua") {
    $jam = date('H:i:s');
    mysqli_query($koneksi, "UPDATE absensi_magang SET JAM_PULANG='$jam' WHERE JAM_PULANG='BELUM' OR JAM_PULANG='00:00:00' OR JAM_PULANG IS NULL OR JAM_PULANG=''");
    header("location:index.php?msg=ok"); exit;

} elseif ($act == "hapus") {
    $id = (int)($_GET['id'] ?? 0);
    mysqli_query($koneksi, "DELETE FROM absensi_magang WHERE id='$id'");
    header("location:index.php?msg=ok"); exit;

} else {
    header("location:index.php"); exit;
}
?>