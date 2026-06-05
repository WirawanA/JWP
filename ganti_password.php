<?php 
include 'includes/header.php'; 

$pesan = '';

if(isset($_POST['ganti_pass'])) {
    $admin_user = $_SESSION['admin'];
    $pass_lama = $_POST['pass_lama'];
    $pass_baru = $_POST['pass_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    // Cek apakah password lama benar
    $cek = mysqli_query($conn, "SELECT password FROM users WHERE name='$admin_user' AND password='$pass_lama'");
    
    if(mysqli_num_rows($cek) == 0) {
        $pesan = "<div class='alert alert-danger'>Password lama salah!</div>";
    } elseif($pass_baru !== $konfirmasi) {
        $pesan = "<div class='alert alert-danger'>Password baru tidak cocok!</div>";
    } else {
        mysqli_query($conn, "UPDATE users SET password='$pass_baru' WHERE name='$admin_user'");
        $pesan = "<div class='alert alert-success'>Password berhasil diperbarui!</div>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Ganti Password</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?= $pesan; ?>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password Lama</label>
                        <input type="password" name="pass_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password Baru</label>
                        <input type="password" name="pass_baru" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi" class="form-control" required>
                    </div>
                    <button type="submit" name="ganti_pass" class="btn btn-primary w-100 fw-bold">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>