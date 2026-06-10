<?php 
include 'includes/header.php'; 

// =========================================================================
// --- KEAMANAN LAPIS DUA: BLOKIR AKSES STAFF ---
// =========================================================================
// Jika tidak ada session role, ATAU rolenya BUKAN 'Admin Utama', tendang ke dashboard!
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin Utama') {
    echo "<script>
            alert('⛔ AKSES DITOLAK! Hanya Admin Utama yang memiliki akses ke halaman Manajemen Pengguna.');
            window.location.href = 'dashboard.php';
          </script>";
    exit; // Wajib ada exit agar sisa kode di bawah tidak dieksekusi oleh browser.
}
// =========================================================================
// --- 1. PROSES LOGIKA PHP ---
// =========================================================================

// A. AKSI TAMBAH PENGGUNA BARU
if(isset($_POST['tambah_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']); // Akan menerima 'Admin Utama' atau 'Staff'
    
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

// B. AKSI EDIT PENGGUNA (GANTI USERNAME, PASSWORD, ATAU ROLE)
if(isset($_POST['edit_user'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    if(!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        mysqli_query($conn, "UPDATE users SET name='$name', password='$password', role='$role' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE users SET name='$name', role='$role' WHERE id=$id");
    }
    header("Location: pengguna.php?status=sukses_edit");
    exit;
}

// C. AKSI HAPUS PENGGUNA
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

// Mengambil data pengguna untuk tabel
$users = mysqli_query($conn, "SELECT id, name, role FROM users");
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Manajemen Pengguna & Hak Akses</h1>
</div>

<?php if(isset($_GET['status'])): ?>
    <?php if($_GET['status'] == 'sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>Berhasil!</strong> Pengguna baru telah ditambahkan.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'sukses_edit'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>Berhasil!</strong> Data pengguna berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong>Terhapus!</strong> Data pengguna telah dihapus dari sistem.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'gagal_ganda'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>Gagal!</strong> Username sudah terdaftar. Gunakan nama lain.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'gagal_hapus_diri_sendiri'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>Akses Ditolak!</strong> Anda tidak dapat menghapus akun Anda sendiri.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary">➕ Tambah Pengguna Baru</h6>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: kasir">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Role Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="Admin Utama">Admin Utama</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    <button type="submit" name="tambah_user" class="btn btn-primary w-100 fw-bold">Daftarkan Pengguna</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary">👥 Daftar Akun Pengguna</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Role / Akses</th>
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
                            <td>
                                <?php if($u['role'] == 'Admin Utama'): ?>
                                    <span class="badge bg-danger px-2 py-1"><?= $u['role']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark px-2 py-1"><?= $u['role'] ?? 'Staff'; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group gap-1">
                                    <button class="btn btn-sm btn-warning fw-semibold rounded" data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id']; ?>">Edit</button>
                                    
                                    <?php if($u['name'] != $_SESSION['admin']): ?>
                                        <a href="pengguna.php?hapus_user=<?= $u['id']; ?>" class="btn btn-sm btn-outline-danger rounded" onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary rounded" disabled>Hapus</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
mysqli_data_seek($users, 0); 
while($u = mysqli_fetch_assoc($users)): 
?>
<div class="modal fade" id="editModal<?= $u['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">📝 Edit Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" value="<?= $u['id']; ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Username</label>
                    <input type="text" name="name" class="form-control" value="<?= $u['name']; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti password">
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih Role Akses</label>
                    <select name="role" class="form-select" required>
                        <option value="Admin Utama" <?= ($u['role'] == 'Admin Utama') ? 'selected' : ''; ?>>Admin Utama</option>
                        <option value="Staff" <?= ($u['role'] == 'Staff') ? 'selected' : ''; ?>>Staff</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="edit_user" class="btn btn-warning fw-bold shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endwhile; ?>

<?php include 'includes/footer.php'; ?>