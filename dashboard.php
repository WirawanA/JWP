<?php 
include 'includes/header.php'; 

$total_barang = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));

$hari_ini = date('Y-m-d');

$masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as total FROM stock_mutations WHERE type='masuk' AND date='$hari_ini'"));

$keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as total FROM stock_mutations WHERE type='keluar' AND date='$hari_ini'"));

$stok_tertinggi_query = mysqli_query($conn, "SELECT id, product_name, stock, unit FROM products WHERE stock > 0 ORDER BY stock DESC LIMIT 3");

$stok_rendah_query = mysqli_query($conn, "SELECT id, product_name, stock, unit FROM products WHERE stock < 10");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Dashboard Analisis Persediaan</h1>
    <span class="badge bg-dark p-2 fs-6"><?= date('d F Y'); ?></span>
</div>

<div class="row g-3 mb-4">
    
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <div class="small text-white-50 text-uppercase fw-bold">Varian Produk</div>
                <div class="fs-3 fw-bold my-1"><?= $total_barang; ?> Item</div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body">
                <div class="small text-white-50 text-uppercase fw-bold">Stok Masuk Hari Ini</div>
                <div class="fs-3 fw-bold my-1"><?= $masuk['total'] ?? 0; ?> Unit</div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body">
                <div class="small text-dark-50 text-uppercase fw-bold">Stok Keluar Hari Ini</div>
                <div class="fs-3 fw-bold my-1"><?= $keluar['total'] ?? 0; ?> Unit</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="m-0 fw-bold text-danger">🚨 Peringatan Batas Minimum Stok (&lt; 10)</h5>
            </div>
            <div class="card-body p-0">
                <?php if(mysqli_num_rows($stok_rendah_query) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Barang Kelontong</th>
                                    <th>Sisa Kuantitas</th>
                                    <th class="text-center" width="25%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($stok_rendah_query)): ?>
                                    <tr>
                                        <td class="fw-semibold text-secondary"><?= $row['product_name']; ?></td>
                                        <td><span class="badge bg-danger px-3 py-2 fs-6"><?= $row['stock']; ?> <?= $row['unit']; ?></span></td>
                                        <td class="text-center">
                                            <a href="persediaan.php?restock_id=<?= $row['id']; ?>" class="btn btn-sm btn-warning fw-bold text-dark shadow-sm">
                                                🛒 Restock
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-success fw-semibold">
                        ✨ Selamat! Semua stok logistik warung Anda dalam kondisi aman (Di atas 10 unit).
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="m-0 fw-bold text-info">📈 Informasi Stok Tertinggi</h5>
            </div>
            <div class="card-body p-0">
                <?php if(mysqli_num_rows($stok_tertinggi_query) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle m-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Barang Kelontong</th>
                                    <th>Jumlah Kuantitas</th>
                                    <th class="text-center" width="25%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                while($row = mysqli_fetch_assoc($stok_tertinggi_query)): 
                                ?>
                                    <tr>
                                        <td class="fw-semibold text-secondary">
                                            <span class="text-muted me-1">#<?= $rank++; ?></span> <?= $row['product_name']; ?>
                                        </td>
                                        <td><span class="badge bg-info px-3 py-2 fs-6 text-white"><?= $row['stock']; ?> <?= $row['unit']; ?></span></td>
                                        <td class="text-center">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-bold">Tersedia</span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        Belum ada data produk di gudang.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
include 'includes/footer.php'; 
?>
