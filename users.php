<?php
include "koneksi.php";
?>

<h3 class="mb-4"><i class="bi bi-people-fill text-info"></i> USER MANAGEMENT</h3>

<div class="card p-4 bg-dark text-white">

    <form action="proses_admin.php?act=tambah_user" method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="col-md-4">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="col-md-4">
                <button class="btn btn-success w-100">Tambah User</button>
            </div>
        </div>
    </form>

    <table class="table table-dark table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

            <?php
        $no=1;
        $query = mysqli_query($koneksi,"SELECT * FROM users");
        while($row = mysqli_fetch_array($query)){
        ?>

            <tr>
                <td><?= $no++; ?></td>
                <td><?= $row['username']; ?></td>
                <td>
                    <a href="proses_admin.php?act=hapus_user&id=<?= $row['id']; ?>" class="btn btn-danger btn-sm"
                        onclick="return confirm('Hapus user?')">
                        Hapus
                    </a>
                </td>
            </tr>

            <?php } ?>

        </tbody>
    </table>

</div>