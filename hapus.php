<?php
include "koneksi.php";
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $query = mysqli_query($koneksi, "DELETE FROM absensi_magang WHERE id='$id'");
    if($query){
        header("location:rekap.php?pesan=hapus-berhasil");
    } else {
        header("location:rekap.php?pesan=hapus-gagal");
    }
}
?>