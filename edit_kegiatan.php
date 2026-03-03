<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php");
    exit;
}

$id = $_GET['id'] ?? '';
$query = mysqli_query($koneksi, "SELECT * FROM absensi_magang WHERE id = '$id' OR ID = '$id'");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data Kosong!'); window.location='index.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kegiatan = mysqli_real_escape_string($koneksi, $_POST['kegiatan']);
    $jin      = mysqli_real_escape_string($koneksi, $_POST['jin']);
    $jout     = mysqli_real_escape_string($koneksi, $_POST['jout']);
    
    $update = mysqli_query($koneksi, "UPDATE absensi_magang SET NAMA='$nama', KEGIATAN='$kegiatan', `JAM MASUK`='$jin', `JAM KELUAR`='$jout' WHERE id='$id' OR ID='$id'");
    
    if($update) {
        echo "<script>alert('Data Berhasil Diupdate!'); window.location='index.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data - Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap"
        rel="stylesheet">
    <style>
    body {
        background-color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .card-edit {
        border: none;
        border-radius: 25px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        margin: auto;
        overflow: hidden;
    }

    .head-edit {
        background: #1e293b;
        padding: 30px;
        color: white;
        text-align: center;
    }

    .input-group-text {
        background: #fff;
        border-right: none;
        color: #3b82f6;
    }

    .form-control {
        border-left: none;
        padding: 12px;
        font-weight: 600;
    }

    .form-label {
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        letter-spacing: 1px;
    }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="card card-edit">
            <div class="head-edit">
                <i class="bi bi-pencil-square fs-1 mb-2 d-block text-warning"></i>
                <h4 class="fw-bold mb-0">EDIT DATA MAHASISWA</h4>
                <small class="opacity-50 text-uppercase">ID Transaksi: #<?= $id; ?></small>
            </div>
            <div class="card-body p-4 bg-white">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">NAMA MAHASISWA</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="nama" class="form-control text-uppercase"
                                value="<?= $data['NAMA']; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">LAPORAN KEGIATAN</label>
                        <div class="input-group">
                            <span class="input-group-text align-items-start pt-2"><i class="bi bi-card-text"></i></span>
                            <textarea name="kegiatan" class="form-control" rows="3"
                                required><?= $data['KEGIATAN']; ?></textarea>
                        </div>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label text-success">JAM MASUK</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                <input type="text" name="jin" class="form-control" value="<?= $data['JAM MASUK']; ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-danger">JAM KELUAR</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-alarm"></i></span>
                                <input type="text" name="jout" class="form-control" value="<?= $data['JAM KELUAR']; ?>">
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow">
                        <i class="bi bi-check-circle me-2"></i> UPDATE DATA SEKARANG
                    </button>
                    <a href="index.php"
                        class="btn btn-link w-100 mt-2 text-decoration-none text-muted fw-bold">BATAL</a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>