<?php
session_start();
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if (isset($_POST['kirim'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kegiatan = mysqli_real_escape_string($koneksi, $_POST['kegiatan']);
    $tanggal  = date('Y-m-d');
    $waktu_sekarang = time();

    // 1. Cek data absen terakhir mahasiswa ini hari ini
    $cek = mysqli_query($koneksi, "SELECT JAM_MASUK FROM absensi_magang WHERE NAMA = '$nama' AND TANGGAL = '$tanggal' ORDER BY ID DESC LIMIT 1");
    
    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        $jam_terakhir = strtotime($tanggal . " " . $data['JAM_MASUK']);
        $selisih = $waktu_sekarang - $jam_terakhir;

        // 2. Jika belum lewat 180 detik (3 menit)
        if ($selisih < 180) {
            $sisa = 180 - $selisih;
            echo "<script>
                    alert('Gagal! Tunggu $sisa detik lagi untuk absen ulang.');
                    window.location.href='mahasiswa_index.php';
                  </script>";
            exit;
        }
    }

    // 3. Simpan data baru jika lolos pengecekan
    $jam_simpan = date('H:i:s');
    $sql = "INSERT INTO absensi_magang (NAMA, TANGGAL, JAM_MASUK, KEGIATAN, JAM_PULANG) 
            VALUES ('$nama', '$tanggal', '$jam_simpan', '$kegiatan', 'BELUM')";
    
    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('Berhasil Absen!'); window.location.href='mahasiswa_index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>