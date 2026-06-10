<?php 
include 'includes/header.php'; 

// --- LOGIKA HAPUS DATA BARANG ---
if(isset($_GET['hapus_barang'])) {
    $id = intval($_GET['hapus_barang']);
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: master_data.php?status=hapus_berhasil");
    exit;
}

// --- [BARU] LOGIKA EDIT DATA BARANG (HANYA NAMA DAN KATEGORI) ---
if(isset($_POST['edit_barang'])) {
    $id = intval($_POST['id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    
    // Query hanya memperbarui nama produk dan category_id
    mysqli_query($conn, "UPDATE products SET product_name='$product_name', category_id=$category_id WHERE id=$id");
    header("Location: master_data.php?status=edit_berhasil");
    exit;
}

// Mengambil Data Produk (Tanpa $users)
$products = mysqli_query($conn, "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");

// [BARU] Mengambil semua daftar kategori untuk pilihan dropdown di Form Edit
$categories_query = mysqli_query($conn, "SELECT * FROM categories");
$categories_list = [];
while($cat = mysqli_fetch_assoc($categories_query)) {
    $categories_list[] = $cat;
}
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Pengelolaan Master Data Barang</h1>
</div>

<?php if(isset($_GET['status'])): ?>
    <?php if($_GET['status'] == 'hapus_berhasil'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong>Terhapus!</strong> Data komoditas berhasil dieliminasi dari sistem.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($_GET['status'] == 'edit_berhasil'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>Berhasil!</strong> Nama barang dan kategori telah diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary">Menu Daftar Barang</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($products) > 0): ?>
                                <?php while($p = mysqli_fetch_assoc($products)): ?>
                                <tr>
                                    <td><code class="text-danger fw-bold"><?= $p['product_code']; ?></code></td>
                                    <td class="fw-semibold text-dark"><?= $p['product_name']; ?></td>
                                    <td><?= $p['category_name'] ?? '<span class="text-muted">Umum</span>'; ?></td>
                                    <td><span class="badge bg-secondary"><?= $p['stock']; ?> <?= $p['unit']; ?></span></td>
                                    <td class="text-center">
                                        <div class="btn-group gap-1">
                                            <button class="btn btn-sm btn-info text-white rounded" data-bs-toggle="modal" data-bs-target="#detailModal<?= $p['id']; ?>">Detail</button>
                                            
                                            <button class="btn btn-sm btn-warning text-dark fw-bold rounded" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id']; ?>">Edit</button>
                                            
                                            <a href="master_data.php?hapus_barang=<?= $p['id']; ?>" class="btn btn-sm btn-danger rounded" onclick="return confirm('Hapus item ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada barang kelontong yang terdata.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Loop kedua untuk merender komponen Modal Pop-up
mysqli_data_seek($products, 0);
while($p = mysqli_fetch_assoc($products)):
?>
<div class="modal fade" id="detailModal<?= $p['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">🔍 Detail Spesifikasi Produk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped table-bordered m-0">
                    <tr><th class="bg-light" width="40%">Kode Produk</th><td><code><?= $p['product_code']; ?></code></td></tr>
                    <tr><th class="bg-light">Nama Sembako</th><td class="fw-bold text-dark"><?= $p['product_name']; ?></td></tr>
                    <tr><th class="bg-light">Kategori Kelompok</th><td><?= $p['category_name'] ?? 'Umum'; ?></td></tr>
                    <tr><th class="bg-light">Jumlah Stok</th><td><?= $p['stock']; ?></td></tr>
                    <tr><th class="bg-light">Satuan</th><td><?= $p['unit']; ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal<?= $p['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">📝 Edit Informasi Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" value="<?= $p['id']; ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kode Barang</label>
                    <input type="text" class="form-control bg-light text-muted" value="<?= $p['product_code']; ?>" readonly disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Nama Barang / Sembako</label>
                    <input type="text" name="product_name" class="form-control" value="<?= $p['product_name']; ?>" required placeholder="Masukkan nama barang">
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Kategori Kelompok</label>
                    <select name="category_id" class="form-select" required>
                        <option value="0">-- Pilih Kategori --</option>
                        <?php foreach($categories_list as $cat): ?>
                            <option value="<?= $cat['id']; ?>" <?= ($p['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?= $cat['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary shadow-sm btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="edit_barang" class="btn btn-warning fw-bold shadow-sm btn-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endwhile; ?>

<?php include 'includes/footer.php'; ?>