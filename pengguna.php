<?php 
include 'includes/header.php'; 

// --- LOGIKA PHP: TAMBAH ADMIN ---
if(isset($_POST['tambah_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = 'Admin'; // Role dikunci secara otomatis menjadi 'Admin'
    
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE name='$name'");
    if(mysqli_num_rows($cek) > 0) {
        header("Location: pengguna.php?status=gagal_ganda");
        exit;
    } else {
        mysqli_query($conn, "INSERT INTO users (name, password, role) VALUES ('$name', '$password', '$role')");
        header("Location: pengguna.php?status=sukses");
        exit;
    }
}

// Aksi Hapus Pengguna
if(isset($_GET['hapus_user'])) {
    $id = intval($_GET['hapus_user']);
    $user_hapus = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM users WHERE id=$id"));
    
    if ($user_hapus['name'] == $_SESSION['admin']) {
        header("Location: pengguna.php?status=gagal_hapus_diri_sendiri");
        exit;
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
        header("Location: pengguna.php?status=hapus");
        exit;
    }
}

$users = mysqli_query($conn, "SELECT id, name, role FROM users");
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Manajemen Admin Utama</h1>
</div>

<?php if(isset($_GET['status'])): ?>
    <?php if($_GET['status'] == 'sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>Berhasil!</strong> Admin baru telah ditambahkan.
        </div>
    <?php elseif($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong>Terhapus!</strong> Data admin telah dihapus.
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary">➕ Tambah Admin Baru</h6>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="tambah_user" class="btn btn-primary w-100 fw-bold">Daftarkan Admin</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary">👑 Daftar Admin Terdaftar</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle m-0">
    <thead class="table-light">
        <tr>
            <th>Username</th>
            <th class="text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while($u = mysqli_fetch_assoc($users)): ?>
        <tr>
            <td class="fw-bold text-primary">
                @<?= $u['name']; ?>
                
                <?php if($u['name'] == $_SESSION['admin']): ?>
                    <span class="badge bg-success ms-2" style="font-size: 0.7rem;">Anda (Aktif)</span>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <?php if($u['name'] != $_SESSION['admin']): ?>
                    <a href="pengguna.php?hapus_user=<?= $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus admin ini?')">Hapus</a>
                <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" disabled>Hapus</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>