<?php 
// 1. Memanggil header yang memuat konfigurasi database, proteksi login, dan CSS Bootstrap
include 'includes/header.php'; 

// =========================================================================
// --- 1. PROSES LOGIKA PHP (CRUD KATEGORI) ---
// =========================================================================

// A. AKSI TAMBAH KATEGORI (CREATE)
// Memeriksa apakah tombol dengan atribut name="tambah_kategori" ditekan (metode POST)
if(isset($_POST['tambah_kategori'])) {
    // Mengamankan inputan nama kategori dari SQL Injection (mencegah karakter aneh/berbahaya)
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    // Validasi: Pastikan inputan tidak kosong
    if(!empty($category_name)) {
        // Menjalankan kueri SQL untuk memasukkan data baru ke tabel 'categories'
        mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$category_name')");
        // Mengarahkan ulang (redirect) halaman dan mengirimkan parameter status='sukses' ke URL
        header("Location: kategori.php?status=sukses");
        exit;
    }
}

// B. AKSI EDIT / UPDATE KATEGORI (UPDATE)
// Memeriksa apakah form di dalam Modal Edit dikirim (tombol name="edit_kategori" ditekan)
if(isset($_POST['edit_kategori'])) {
    // Mengambil ID kategori yang dikirim secara tersembunyi (hidden input) dan memastikannya berupa angka (intval)
    $id = intval($_POST['id']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    if(!empty($category_name)) {
        // Menjalankan kueri SQL untuk menimpa/memperbarui nama kategori yang memiliki ID spesifik tersebut
        mysqli_query($conn, "UPDATE categories SET category_name='$category_name' WHERE id=$id");
        header("Location: kategori.php?status=update_sukses");
        exit;
    }
}

// C. AKSI HAPUS KATEGORI (DELETE)
// Memeriksa apakah URL memiliki parameter '?hapus_kategori=...' (metode GET)
if(isset($_GET['hapus_kategori'])) {
    // Menangkap ID dari URL dan merubahnya menjadi integer (angka bulat) demi keamanan
    $id = intval($_GET['hapus_kategori']);
    // Menghapus data dari tabel 'categories' berdasarkan ID
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    header("Location: kategori.php?status=hapus");
    exit;
}

// D. AKSI TAMPIL DATA (READ)
// Mengambil semua data kategori dari database untuk ditampilkan ke dalam tabel
$categories = mysqli_query($conn, "SELECT * FROM categories");
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Manajemen Kategori Barang</h1>
</div>

<?php if(isset($_GET['status'])): ?>
    <?php if($_GET['status'] == 'sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>Berhasil!</strong> Kategori baru telah ditambahkan ke sistem.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'update_sukses'): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <strong>Berhasil Diperbarui!</strong> Nama kategori telah sukses diubah.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'hapus'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong>Terhapus!</strong> Data kategori telah dihapus dari sistem.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4">
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary">Tambah Kategori Baru</h6>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Kategori</label>
                        <input type="text" name="category_name" class="form-control" placeholder="Misal: Bahan Pokok, Sabun" required>
                    </div>
                    <button type="submit" name="tambah_kategori" class="btn btn-success w-100 fw-bold">Simpan Kategori</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary">Daftar Kategori Terdaftar</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">ID Kategori</th>
                                <th>Nama Kategori Komoditas</th>
                                <th width="25%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($c = mysqli_fetch_assoc($categories)): ?>
                            <tr>
                                <td><span class="badge bg-secondary px-3 py-2">ID: <?= $c['id']; ?></span></td>
                                <td class="fw-bold text-dark"><?= $c['category_name']; ?></td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <button class="btn btn-sm btn-warning text-dark fw-bold rounded" data-bs-toggle="modal" data-bs-target="#editModal<?= $c['id']; ?>">Edit</button>
                                        
                                        <a href="kategori.php?hapus_kategori=<?= $c['id']; ?>" class="btn btn-sm btn-outline-danger rounded" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
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
</div>

<?php 
// PENTING: mysqli_data_seek mengembalikan posisi "pointer" data kembali ke urutan 0 (awal).
// Ini wajib dilakukan karena variabel $categories sudah habis dilooping di tabel atas.
// Jika tidak direset, looping untuk modal di bawah ini tidak akan berjalan (kosong).
mysqli_data_seek($categories, 0); 

// Looping kembali untuk mencetak kotak Modal satu per satu sesuai jumlah kategori
while($c = mysqli_fetch_assoc($categories)): 
?>
<div class="modal fade" id="editModal<?= $c['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">✏️ Ubah Nama Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" value="<?= $c['id']; ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Kategori Baru</label>
                    <input type="text" name="category_name" class="form-control" value="<?= $c['category_name']; ?>" required>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="edit_kategori" class="btn btn-warning btn-sm px-3 fw-bold">Perbarui</button>
            </div>
        </form>
    </div>
</div>
<?php endwhile; ?>

<?php 
// Memanggil footer
include 'includes/footer.php'; 
?>