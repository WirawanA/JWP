<?php 
// 1. Memanggil header (berisi koneksi database, pengecekan login admin, dan CSS Bootstrap)
include 'includes/header.php'; 

// 2. MEMASTIKAN SESSION AKTIF UNTUK FITUR "FLASH MESSAGE"
// Flash message adalah pesan sementara (seperti notifikasi sukses/gagal) yang disimpan di session.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =========================================================================
// --- [A] LOGIKA PHP: PROSES MUTASI STOK MASUK/KELUAR ---
// =========================================================================
if (isset($_POST['proses_mutasi'])) {
    // Menangkap data dari form sebelah kiri dan mengubah tipe datanya menjadi integer khusus untuk angka
    $product_id = intval($_POST['product_id']);
    $type = $_POST['type']; // Berisi string 'masuk' atau 'keluar'
    $quantity = intval($_POST['quantity']);
    $date = date('Y-m-d'); // Mengambil tanggal hari ini

    // Mengecek sisa stok barang saat ini di database sebelum memproses transaksi
    $p_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock, product_name, unit FROM products WHERE id=$product_id"));
    
    // VALIDASI LOGIS: Jika admin memilih 'keluar', tetapi jumlah yang dikeluarkan melebihi sisa stok di gudang
    if ($type == 'keluar' && $p_check['stock'] < $quantity) {
        
        // Simpan status dan pesan error ke dalam Session, bukan ke URL
        $_SESSION['pop_status'] = 'gagal';
        $_SESSION['pop_pesan'] = "Stok '" . $p_check['product_name'] . "' tidak mencukupi untuk transaksi keluar! Sisa stok: " . $p_check['stock'] . " " . $p_check['unit'] . ".";
    
    } else {
        // Jika validasi aman, masukkan data aktivitas ke tabel riwayat mutasi (stock_mutations)
        mysqli_query($conn, "INSERT INTO stock_mutations (product_id, type, quantity, date) VALUES ($product_id, '$type', $quantity, '$date')");
        
        // Menggunakan logika percabangan untuk meng-update stok utama di tabel 'products'
        if ($type == 'masuk') {
            // Jika masuk, tambahkan stok lama dengan kuantitas baru (stock = stock + quantity)
            mysqli_query($conn, "UPDATE products SET stock = stock + $quantity WHERE id=$product_id");
        } else {
            // Jika keluar, kurangi stok lama dengan kuantitas baru (stock = stock - quantity)
            mysqli_query($conn, "UPDATE products SET stock = stock - $quantity WHERE id=$product_id");
        }
        
        // Simpan pesan sukses ke dalam Session
        $_SESSION['pop_status'] = 'sukses';
        $_SESSION['pop_pesan'] = "Transaksi barang " . $type . " sebanyak " . $quantity . " " . $p_check['unit'] . " untuk \"" . $p_check['product_name'] . "\" berhasil disimpan!";
    }
    
    // PRG (Post/Redirect/Get) PATTERN: Mengarahkan kembali ke halaman ini sendiri.
    // Tujuannya agar jika admin menekan tombol "Refresh/F5" di browser, form tidak tersubmit dua kali secara tidak sengaja.
    header("Location: persediaan.php");
    exit;
}

// =========================================================================
// --- [B] LOGIKA PHP: TAMBAH BARANG BARU ---
// =========================================================================
if(isset($_POST['tambah_barang'])) {
    $code = mysqli_real_escape_string($conn, trim($_POST['product_code']));
    $name = mysqli_real_escape_string($conn, trim($_POST['product_name']));
    $cat_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $date = date('Y-m-d');

    // VALIDASI GANDA: Mengecek apakah Kode atau Nama barang sudah pernah terdaftar di database
    $cek_ganda = mysqli_query($conn, "SELECT product_code, product_name FROM products WHERE product_code = '$code' OR product_name = '$name'");
    
    if(mysqli_num_rows($cek_ganda) > 0) {
        $row = mysqli_fetch_assoc($cek_ganda);
        $_SESSION['pop_status'] = 'gagal';
        
        // Mendeteksi bagian mana yang kembar untuk memberikan pesan error yang spesifik
        if($row['product_code'] == $code) {
            $_SESSION['pop_pesan'] = "Gagal menambah barang! Kode '" . $code . "' sudah terdaftar.";
        } else {
            $_SESSION['pop_pesan'] = "Gagal menambah barang! Nama \"" . $name . "\" sudah digunakan.";
        }
    } else {
        // Jika kode dan nama unik, simpan barang baru ke tabel products
        $insert_product = mysqli_query($conn, "INSERT INTO products (product_code, product_name, category_id, stock, unit) VALUES ('$code', '$name', '$cat_id', '$stock', '$unit')");
        
        // AUTO-MUTASI: Jika barang baru langsung memiliki stok awal > 0, otomatis catat ke tabel laporan mutasi sebagai barang 'masuk'
        if($insert_product && $stock > 0) {
            // Mengambil ID barang yang baru saja berhasil dibuat oleh MySQL (Auto Increment ID)
            $product_id = mysqli_insert_id($conn);
            mysqli_query($conn, "INSERT INTO stock_mutations (product_id, type, quantity, date) VALUES ($product_id, 'masuk', $stock, '$date')");
        }
        
        $_SESSION['pop_status'] = 'sukses';
        $_SESSION['pop_pesan'] = "Barang baru \"" . $name . "\" berhasil didaftarkan ke dalam sistem!";
    }
    header("Location: persediaan.php");
    exit;
}

// 3. MENGAMBIL DATA URL DARI DASHBOARD
// Jika admin datang ke halaman ini dengan menekan tombol 'Restock' dari Dashboard, tangkap ID produknya.
$restock_id = isset($_GET['restock_id']) ? intval($_GET['restock_id']) : 0;

// Mengambil seluruh data barang untuk ditampilkan di tabel dan dropdown
$all_products = mysqli_query($conn, "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
$categories = mysqli_query($conn, "SELECT * FROM categories");
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Persediaan Barang Kelontong</h1>
    <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">+ Tambah Barang Baru</button>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-dark text-white">
            <div class="card-body p-4">
                <h5 class="card-title text-warning fw-bold mb-3">🔄 Catat Keluar Masuk</h5>
                <form action="" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Produk</label>
                        <select name="product_id" id="productSelect" class="form-select" required onchange="updateSatuan()">
                            <?php 
                            mysqli_data_seek($all_products, 0); 
                            while($p = mysqli_fetch_assoc($all_products)): 
                            ?>
                                <option value="<?= $p['id']; ?>" data-satuan="<?= $p['unit']; ?>" <?= ($p['id'] == $restock_id) ? 'selected' : ''; ?>>
                                    <?= $p['product_code']; ?> - <?= $p['product_name']; ?> (Sisa: <?= $p['stock']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jenis Aktivitas</label>
                        <div class="d-flex gap-3 bg-secondary bg-opacity-25 p-2 rounded">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="m1" value="masuk" checked>
                                <label class="form-check-label text-success fw-bold" for="m1">Barang Masuk</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="m2" value="keluar">
                                <label class="form-check-label text-danger fw-bold" for="m2">Barang Keluar</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jumlah / Kuantitas</label>
                        <div class="input-group">
                            <input type="number" name="quantity" class="form-control" min="0" value="0" required>
                            <span class="input-group-text bg-secondary text-white border-0 fw-bold" id="labelSatuan">Unit</span>
                        </div>
                    </div>
                    <button type="submit" name="proses_mutasi" class="btn btn-warning w-100 fw-bold py-2">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary">Status Ketersediaan Saat Ini (Terakhir)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Produk</th>
                                <th>Stok Akhir</th>
                                <th>Status </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($all_products, 0); 
                            while($p = mysqli_fetch_assoc($all_products)): 
                            ?>
                            <tr class="<?= ($p['id'] == $restock_id) ? 'table-warning fw-bold' : ''; ?>">
                                <td class="fw-semibold text-secondary"><?= $p['product_name']; ?></td>
                                <td class="fw-bold text-dark"><?= $p['stock']; ?> <?= $p['unit']; ?></td>
                                <td>
                                    <?php if($p['stock'] > 0): ?>
                                        <span class="badge bg-success px-3 py-2">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-3 py-2">Tidak Tersedia / Habis</span>
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
</div>

<div class="modal fade" id="popNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            
            <?php if (isset($_SESSION['pop_status']) && $_SESSION['pop_status'] == 'sukses'): ?>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">🎉 Transaksi Berhasil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="text-success display-4 mb-2">✓</div>
                    <p class="fs-6 text-secondary m-0"><?= $_SESSION['pop_pesan']; ?></p>
                </div>
                
            <?php elseif (isset($_SESSION['pop_status']) && $_SESSION['pop_status'] == 'gagal'): ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">⚠️ Transaksi Gagal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="text-danger display-4 mb-2">✕</div>
                    <p class="fs-6 text-secondary m-0"><?= $_SESSION['pop_pesan']; ?></p>
                </div>
            <?php endif; ?>
            
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Oke</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahBarang" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">📦 Form Registrasi Barang Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-dark">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Kode Unik Barang</label>
                    <input type="text" name="product_code" class="form-control" placeholder="Misal: BRG-102" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Barang / Sembako</label>
                    <input type="text" name="product_name" class="form-control" placeholder="Misal: Minyak Goreng 2L" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Kategori Komoditas</label>
                    <select name="category_id" class="form-select" required>
                        <?php mysqli_data_seek($categories, 0); while($c = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $c['id']; ?>"><?= $c['category_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold">Stok Awal</label>
                        <input type="number" name="stock" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold">Satuan Ukur</label>
                        <input type="text" name="unit" class="form-control" placeholder="Pcs / Dus / Karung" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" name="tambah_barang" class="btn btn-primary btn-sm px-3">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<script>
// 1. Fungsi Dinamis Pengubah Label Satuan
function updateSatuan() {
    // Mencari elemen dropdown produk berdasarkan ID 'productSelect'
    var select = document.getElementById("productSelect");
    
    // Mengecek apakah dropdown memiliki isi/opsi
    if(select.options.length > 0) {
        // Mengambil opsi mana yang saat ini sedang disorot/dipilih
        var selectedOption = select.options[select.selectedIndex];
        
        // Membaca isi atribut 'data-satuan' dari opsi tersebut (contoh: "Bungkus")
        var satuan = selectedOption.getAttribute("data-satuan");
        
        // Memasukkan teks satuan tersebut ke dalam label di samping input kuantitas
        document.getElementById("labelSatuan").innerText = satuan;
    }
}

// 2. Fungsi Eksekusi Saat Halaman Selesai Dimuat (Onload)
window.onload = function() {
    // Memanggil fungsi updateSatuan sekali di awal agar saat halaman baru dibuka, satuan produk pertama langsung muncul
    updateSatuan();
    
    // PENYUNTIKAN PHP KE JAVASCRIPT: Memicu Modal Pop-up Flash Message
    // PHP akan mengecek apakah ada variabel $_SESSION['pop_status']. Jika ya, PHP akan "menuliskan" script untuk membuka modal.
    <?php if (isset($_SESSION['pop_status'])): ?>
        
        // Membuat objek Modal baru dari library Bootstrap dan memerintahkannya untuk tampil (.show)
        var myModal = new bootstrap.Modal(document.getElementById('popNotificationModal'));
        myModal.show();
        
        <?php 
        // Sangat Penting: Setelah script pemanggilan modal berhasil dicetak ke browser,
        // Hapus variabel session di server. Jika tidak dihapus, pop-up akan terus muncul setiap kali admin me-refresh halaman.
        unset($_SESSION['pop_status']); 
        unset($_SESSION['pop_pesan']); 
        ?>
        
    <?php endif; ?>
};
</script>

<?php include 'includes/footer.php'; ?>